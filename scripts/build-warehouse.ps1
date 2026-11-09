# Builds the warehouse gRPC service and its runtime image without pulling the
# large `golang` base image.
#
#   1. compiles a static binary inside the running `app` container (which has
#      the Go toolchain installed by docker/php/install-extensions.sh),
#   2. copies it back to services/warehouse/bin/warehouse,
#   3. builds the `FROM scratch` runtime image from Dockerfile.prebuilt.
#
# Usage (from the repository root):
#   powershell -ExecutionPolicy Bypass -File scripts/build-warehouse.ps1

$ErrorActionPreference = 'Stop'

$repo = Split-Path -Parent $PSScriptRoot

Push-Location $repo
try {
    Write-Host '==> compiling the Go service inside the app container'
    docker compose exec -T -u root -w /var/www/html app `
        sh /var/www/html/services/warehouse/scripts/build.sh
    if ($LASTEXITCODE -ne 0) { throw 'go build failed' }

    Write-Host '==> copying the binary to services/warehouse/bin'
    New-Item -ItemType Directory -Force -Path 'services/warehouse/bin' | Out-Null
    docker compose cp app:/tmp/warehouse services/warehouse/bin/warehouse
    if ($LASTEXITCODE -ne 0) { throw 'docker compose cp failed' }

    Write-Host '==> building the runtime image (Dockerfile.prebuilt)'
    docker compose build warehouse
    if ($LASTEXITCODE -ne 0) { throw 'docker compose build failed' }

    Write-Host '==> done. Start it with: docker compose up -d warehouse'
}
finally {
    Pop-Location
}
