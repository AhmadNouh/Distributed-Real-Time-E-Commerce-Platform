#!/usr/bin/env bash
# Compiles the warehouse service into a single static binary.
#
# Run inside a container that has the Go toolchain (the application container
# works: `docker compose exec -T -u root -w /var/www/html app
# sh /var/www/html/services/warehouse/scripts/build.sh`), or from
# scripts/build-warehouse.ps1 on Windows which does everything for you.
set -euo pipefail

cd "$(dirname "$0")/.."

CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build \
  -trimpath \
  -ldflags="-s -w" \
  -o /tmp/warehouse \
  ./cmd/server

echo "==> built /tmp/warehouse ($(du -h /tmp/warehouse | cut -f1))"
