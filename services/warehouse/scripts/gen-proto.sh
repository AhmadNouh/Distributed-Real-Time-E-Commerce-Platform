#!/usr/bin/env bash
# Regenerates the Go stubs for the warehouse contract.
#
# Two supported ways to run it from the repository root:
#
#   1) Inside the running application container (no image pull required):
#        docker compose exec -T -u root app \
#          sh /var/www/html/services/warehouse/scripts/gen-proto.sh
#
#   2) In a throw-away Go image:
#        docker run --rm -v "${PWD}:/w" -w /w golang:1.23-bookworm \
#          bash /w/services/warehouse/scripts/gen-proto.sh
#
# The protoc plugins are installed under ${GOBIN:-/tmp/protoc-bin}, so the
# script never touches the system Go installation.
set -euo pipefail

PROTOC_VERSION_PLUGIN_GO="v1.35.2"
PROTOC_VERSION_PLUGIN_GO_GRPC="v1.5.1"

if ! command -v protoc >/dev/null 2>&1; then
  echo "==> installing protoc"
  apt-get update -qq
  apt-get install -y -qq protobuf-compiler >/dev/null
fi

if ! command -v go >/dev/null 2>&1; then
  echo "error: the go toolchain is required to build the protoc plugins" >&2
  exit 1
fi

GOBIN="${GOBIN:-/tmp/protoc-bin}"
mkdir -p "${GOBIN}"

echo "==> installing Go protoc plugins into ${GOBIN}"
GOBIN="${GOBIN}" go install "google.golang.org/protobuf/cmd/protoc-gen-go@${PROTOC_VERSION_PLUGIN_GO}"
GOBIN="${GOBIN}" go install "google.golang.org/grpc/cmd/protoc-gen-go-grpc@${PROTOC_VERSION_PLUGIN_GO_GRPC}"

OUT="services/warehouse/gen"
mkdir -p "${OUT}"

echo "==> generating Go code into ${OUT}"
PATH="${GOBIN}:${PATH}" protoc \
  -I proto \
  --go_out="${OUT}" \
  --go_opt=paths=source_relative \
  --go-grpc_out="${OUT}" \
  --go-grpc_opt=paths=source_relative \
  warehouse/v1/warehouse.proto

echo "==> generated files"
find "${OUT}" -type f -name '*.go' -print
