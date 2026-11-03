# Proof for requirement 2: two customers fight over the LAST unit.
#
#   powershell -ExecutionPolicy Bypass -File scripts/proof/race-condition.ps1
#
# What it does:
#   1. resets product 1 in the warehouse DB to quantity = 1, reserved = 0
#   2. logs in as the seeded customer
#   3. fires 10 order requests AT THE SAME TIME (all ask for that single unit)
#   4. expects exactly one 201 Created and nine 409 Conflict responses
#   5. prints the final warehouse stock (must be available = 0)
#
# Run it from the repository root, with `docker compose up -d` already running.

param(
    [string]$BaseUrl = "http://localhost:8000",
    [int]$ConcurrentRequests = 10,
    [int]$ProductId = 1
)

$ErrorActionPreference = "Stop"

function Assert-Equal($actual, $expected, $message) {
    if ($actual -ne $expected) {
        throw "ASSERTION FAILED: $message (expected '$expected', got '$actual')"
    }
    Write-Host "  OK  $message" -ForegroundColor Green
}

Write-Host "==> 1/5 resetting warehouse stock for product $ProductId to 1 unit" -ForegroundColor Cyan
docker compose exec -T warehouse-db psql -U warehouse -d warehouse -v ON_ERROR_STOP=1 -c `
    "UPDATE stock SET quantity = 1, reserved = 0, updated_at = now() WHERE product_id = $ProductId; DELETE FROM reservations WHERE order_uuid LIKE 'race-%';" | Out-Null

$stockBefore = (docker compose exec -T warehouse-db psql -U warehouse -d warehouse -t -A -c `
        "SELECT quantity - reserved FROM stock WHERE product_id = $ProductId;").Trim()
Assert-Equal $stockBefore "1" "warehouse reports exactly 1 available unit before the race"

Write-Host "==> 2/5 logging in as customer@example.com" -ForegroundColor Cyan
$loginBody = @{ email = "customer@example.com"; password = "password" } | ConvertTo-Json
$login = Invoke-RestMethod -Method Post -Uri "$BaseUrl/api/v1/auth/login" `
    -ContentType "application/json" -Headers @{ Accept = "application/json" } -Body $loginBody

$token = $login.data.token
if (-not $token) { throw "Login failed: no token in the response." }

Write-Host "==> 3/5 firing $ConcurrentRequests order requests at the same time" -ForegroundColor Cyan

Add-Type -AssemblyName System.Net.Http
$http = [System.Net.Http.HttpClient]::new()
$http.Timeout = [TimeSpan]::FromSeconds(30)

$tasks = @()

for ($i = 1; $i -le $ConcurrentRequests; $i++) {
    $payload = @{
        uuid  = "race-$([guid]::NewGuid())"
        items = @(@{ product_id = $ProductId; quantity = 1 })
    } | ConvertTo-Json -Depth 5

    $request = [System.Net.Http.HttpRequestMessage]::new(
        [System.Net.Http.HttpMethod]::Post, "$BaseUrl/api/v1/orders")
    $request.Headers.Add("Accept", "application/json")
    $request.Headers.Add("Authorization", "Bearer $token")
    $request.Content = [System.Net.Http.StringContent]::new(
        $payload, [System.Text.Encoding]::UTF8, "application/json")

    # SendAsync starts immediately; we await all of them together below.
    $tasks += $http.SendAsync($request)
}

[System.Threading.Tasks.Task]::WaitAll([System.Threading.Tasks.Task[]]$tasks)

$statuses = @()
$bodies = @()

foreach ($task in $tasks) {
    $response = $task.Result
    $statuses += [int]$response.StatusCode
    $bodies += $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()
}

$http.Dispose()

$created = @($statuses | Where-Object { $_ -eq 201 }).Count
$conflicts = @($statuses | Where-Object { $_ -eq 409 }).Count

Write-Host "  status codes: $($statuses -join ', ')"
Write-Host ""
Write-Host "==> 4/5 results" -ForegroundColor Cyan
Assert-Equal $created 1 "exactly ONE order was created"
Assert-Equal $conflicts ($ConcurrentRequests - 1) "the other $($ConcurrentRequests - 1) requests were rejected with 409"

$rejection = $bodies | Where-Object { $_ -match "Insufficient stock" } | Select-Object -First 1
if ($rejection) {
    Write-Host "  sample rejection body:" -ForegroundColor DarkGray
    Write-Host "  $rejection" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "==> 5/5 final warehouse stock" -ForegroundColor Cyan
$stockAfter = (docker compose exec -T warehouse-db psql -U warehouse -d warehouse -t -A -c `
        "SELECT quantity - reserved FROM stock WHERE product_id = $ProductId;").Trim()
Assert-Equal $stockAfter "0" "no unit is left - the last one was sold exactly once"

$orders = (docker compose exec -T postgres psql -U postgres -d ecommerce -t -A -c "SELECT count(*) FROM orders WHERE uuid LIKE 'race-%';").Trim()
Write-Host "  order rows stored with a race-* uuid: $orders"

Write-Host ""
Write-Host "RACE CONDITION PROOF PASSED: 10 concurrent buyers, 1 unit, 1 success." -ForegroundColor Green
exit 0
