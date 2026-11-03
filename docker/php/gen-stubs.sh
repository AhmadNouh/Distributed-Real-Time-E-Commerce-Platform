#!/bin/sh
# Generates the PHP classes + gRPC client for proto/warehouse/v1/warehouse.proto.
#
# The generated code is committed under app/Shared/Grpc/Generated so the
# application does not need protoc at runtime. Re-run this script whenever the
# .proto file changes:
#
#   docker compose exec -T -u root app sh /var/www/html/docker/php/gen-stubs.sh
#
# PSR-4 mapping (composer.json):
#   "Warehouse\\": "app/Shared/Grpc/Generated/Warehouse/"
# so `option php_namespace = "Warehouse\V1"` results in
#   app/Shared/Grpc/Generated/Warehouse/V1/*.php
set -eu

cd /var/www/html

echo "==> installing protoc + grpc php plugin"
if ! command -v protoc >/dev/null 2>&1 || [ ! -x /usr/bin/grpc_php_plugin ]; then
    apt-get update -qq
    apt-get install -y -qq protobuf-compiler protobuf-compiler-grpc >/dev/null
fi

OUT="app/Shared/Grpc/Generated"

rm -rf "${OUT}/Warehouse"
mkdir -p "${OUT}"

echo "==> generating PHP message classes + service client into ${OUT}"
protoc \
    -I proto \
    --php_out="${OUT}" \
    --grpc_out="${OUT}" \
    --plugin=protoc-gen-grpc=/usr/bin/grpc_php_plugin \
    proto/warehouse/v1/warehouse.proto

echo "==> generated files"
find "${OUT}" -type f -name '*.php' | sort
