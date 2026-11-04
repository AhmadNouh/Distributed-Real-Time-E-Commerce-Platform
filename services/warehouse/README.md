# Warehouse service (Go + gRPC)

Standalone inventory service for requirement 2. It owns its own PostgreSQL
database and exposes exactly three RPCs defined in
[`proto/warehouse/v1/warehouse.proto`](../../proto/warehouse/v1/warehouse.proto):

| RPC | Purpose |
|---|---|
| `GetStock` | sellable quantity (`quantity - reserved`) |
| `Reserve` | all-or-nothing reservation of a whole order (idempotent per `order_uuid`) |
| `Release` | returns a reservation to the pool (idempotent) |

## Layout

```
cmd/server/main.go              gRPC bootstrap, health, reflection, graceful stop
internal/warehouse/store.go     SQL + row locks + advisory locks + compensation
internal/warehouse/server.go    proto <-> store translation
internal/warehouse/schema.sql   embedded schema + seed (product 1 = 1 unit)
gen/warehouse/v1/               generated protobuf + gRPC stubs (committed)
scripts/gen-proto.sh            regenerates gen/warehouse/v1/*.go
scripts/build.sh                compiles the static binary into /tmp/warehouse
Dockerfile.prebuilt             `FROM scratch` image for fast local rebuilds
Dockerfile                      canonical multi-stage build (compose default)
```

## Build

**Fresh clone: nothing to do.** `docker compose up -d --build` compiles the
service from source inside the multi-stage `Dockerfile` (`golang:1.24-bookworm`
builder + `alpine` runtime). The binary is not committed: `bin/` is ignored on
purpose, so the image is always built from the Go source in the repository.

```bash
docker compose up -d --build warehouse warehouse-db
```

Dependencies are **vendored** in `services/warehouse/vendor/` (committed). The
builder runs `go build -mod=vendor`, so it never contacts `proxy.golang.org`.
Only the `golang:1.24-bookworm` base image is pulled, once.

### Faster local rebuilds

Compiling inside the `golang` builder pulls a ~900 MB base image. If the `app`
container is already running (it ships a Go toolchain), this script compiles
the binary there and builds the `FROM scratch` image instead:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/build-warehouse.ps1
```

It runs `services/warehouse/scripts/build.sh` inside the `app` container, copies
the result to `services/warehouse/bin/warehouse`, and builds
`Dockerfile.prebuilt` by setting `WAREHOUSE_DOCKERFILE=Dockerfile.prebuilt` for
that one compose invocation.

Alternatively, with the Go toolchain on the host:

```bash
CGO_ENABLED=0 go build -trimpath -ldflags="-s -w" -o bin/warehouse ./cmd/server
WAREHOUSE_DOCKERFILE=Dockerfile.prebuilt docker compose build warehouse
```

## Run

```bash
docker compose up -d warehouse warehouse-db
docker compose logs -f warehouse
```

* gRPC: `localhost:50051`
* PostgreSQL: `localhost:5433` (`warehouse` / `warehouse` / `warehouse`)

## Environment

| Variable | Default |
|---|---|
| `GRPC_PORT` | `50051` |
| `DB_DSN` | `postgres://warehouse:warehouse@warehouse-db:5432/warehouse?sslmode=disable` |

## Regenerate the Go stubs

```bash
# inside the running app container (has go + protoc installed)
docker compose exec -T -u root app \
  sh /var/www/html/services/warehouse/scripts/gen-proto.sh
```

```bash
# or in a throw-away Go image
docker run --rm -v "$($PWD.Path):/w" -w /w golang:1.24-bookworm \
  bash /w/services/warehouse/scripts/gen-proto.sh
```

## Concurrency notes

`Reserve` runs in one transaction:

1. `pg_advisory_xact_lock(hashtext(order_uuid))` -> idempotency per order.
2. `SELECT ... WHERE product_id = ANY($1) ORDER BY product_id FOR UPDATE`
   -> deterministic lock order (no deadlocks), and PostgreSQL re-reads the
   latest committed row after the lock wait.
3. Validate every line, then `UPDATE stock SET reserved = reserved + $n`.
4. Insert the reservation row and commit. A `CHECK (reserved <= quantity)`
   constraint is the last line of defence.

Because two orders asking for the last unit serialize on the same row lock,
exactly one of them observes `available >= requested`.
