# Proof for requirement 2: what the customer sees when the warehouse is down.
#
#   powershell -ExecutionPolicy Bypass -File scripts/proof/warehouse-down.ps1
#
# What it does:
#   1. stops ONLY the warehouse container (the Laravel app keeps running)
#   2. opens the product page  -> HTTP 200, available_quantity = null,
#      stock_status = "unavailable" (the page still renders)
#   3. tries to place an order -> HTTP 503 with a clear message, in ~2s,
#      never an endless wait
#   4. starts the warehouse again and checks the page is healthy again

param(
    [string]$BaseUrl = "http://localhost:8000",
    [int]$ProductId = 1
)

$ErrorActionPreference = "Stop"

function Assert-Equal($actual, $expected, $message) {
    if ($actual -ne $expected) {
        throw "ASSERTION FAILED: $message (expected '$expected', got '$actual')"
    }
    Write-Host "  OK  $message" -ForegroundColor Green
}

Write-Host "==> logging in as customer@example.com" -ForegroundColor Cyan
$loginBody = @{ email = "customer@example.com"; password = "password" } | ConvertTo-Json
$login = Invoke-RestMethod -Method Post -Uri "$BaseUrl/api/v1/auth/login" `
    -ContentType "application/json" -Headers @{ Accept = "application/json" } -Body $loginBody
$token = $login.data.token
if (-not $token) { throw "Login failed: no token in the response." }

$auth = @{ Accept = "application/json"; Authorization = "Bearer $token" }

try {
    Write-Host "==> stopping the warehouse container" -ForegroundColor Cyan
    docker compose stop warehouse | Out-Null

    # Give the container a moment to actually disappear from the network.
    Start-Sleep -Seconds 2

    Write-Host ""
    Write-Host "==> product page while the warehouse is down" -ForegroundColor Cyan
    $watch = [System.Diagnostics.Stopwatch]::StartNew()
    $product = Invoke-RestMethod -Method Get -Uri "$BaseUrl/api/v1/products/$ProductId" -Headers $auth
    $watch.Stop()

    Write-Host "  GET /api/v1/products/$ProductId -> 200 in $($watch.ElapsedMilliseconds) ms"
    Write-Host "  data.available_quantity = $($product.data.available_quantity)"
    Write-Host "  data.stock_status       = $($product.data.stock_status)"

    Assert-Equal $product.data.stock_status "unavailable" "stock_status is 'unavailable'"
    if ($null -ne $product.data.available_quantity) {
        throw "ASSERTION FAILED: available_quantity must be null while the warehouse is down."
    }
    Write-Host "  OK  available_quantity is null" -ForegroundColor Green

    Write-Host ""
    Write-Host "==> placing an order while the warehouse is down" -ForegroundColor Cyan
    $orderBody = @{ items = @(@{ product_id = $ProductId; quantity = 1 }) } | ConvertTo-Json -Depth 5

    $watch = [System.Diagnostics.Stopwatch]::StartNew()
    $status = 0
    $message = ""
    try {
        Invoke-RestMethod -Method Post -Uri "$BaseUrl/api/v1/orders" `
            -ContentType "application/json" -Headers $auth -Body $orderBody | Out-Null
        $status = 201
    } catch {
        $status = [int]$_.Exception.Response.StatusCode
        $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
        $raw = $reader.ReadToEnd()
        $reader.Dispose()
        $message = ($raw | ConvertFrom-Json).message
    }
    $watch.Stop()

    Write-Host "  POST /api/v1/orders -> $status in $($watch.ElapsedMilliseconds) ms"
    Write-Host "  message: $message"

    Assert-Equal $status 503 "the API answers 503 Service Unavailable"
    if ($message -notmatch "warehouse") {
        throw "ASSERTION FAILED: the message should mention the warehouse, got: $message"
    }
    Write-Host "  OK  the message explains that the warehouse is unavailable" -ForegroundColor Green

    if ($watch.Elapsed.TotalSeconds -gt 5) {
        throw "ASSERTION FAILED: the request waited $($watch.Elapsed.TotalSeconds)s; it must fail fast."
    }
    Write-Host "  OK  the request failed fast ($([math]::Round($watch.Elapsed.TotalSeconds, 2))s), no infinite wait" -ForegroundColor Green
}
finally {
    Write-Host ""
    Write-Host "==> starting the warehouse again" -ForegroundColor Cyan
    docker compose start warehouse | Out-Null

    for ($i = 0; $i -lt 40; $i++) {
        $health = (docker inspect --format "{{.State.Health.Status}}" (docker compose ps -q warehouse)).Trim()
        if ($health -eq "healthy") { break }
        Start-Sleep -Milliseconds 500
    }

    Write-Host "  warehouse health: $health"
}

Write-Host ""
Write-Host "==> product page after the warehouse is back" -ForegroundColor Cyan
$product = Invoke-RestMethod -Method Get -Uri "$BaseUrl/api/v1/products/$ProductId" -Headers $auth
Write-Host "  data.available_quantity = $($product.data.available_quantity)"
Write-Host "  data.stock_status       = $($product.data.stock_status)"

Assert-Equal $product.data.stock_status "available" "the product page is healthy again"
Write-Host ""
Write-Host "WAREHOUSE-DOWN PROOF PASSED: the API degrades gracefully and recovers." -ForegroundColor Green
exit 0
