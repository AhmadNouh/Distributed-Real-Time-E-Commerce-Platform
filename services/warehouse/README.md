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
Dockerfile.prebuilt             `FROM scratch` runtime image (default in compose)
Dockerfile                      canonical multi-stage build (needs golang image)
```

## Build

The static binary is compiled ahead of time, so the runtime image needs no Go
toolchain and pulls nothing from the network:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/build-warehouse.ps1
```

That script compiles `services/warehouse/scripts/build.sh` inside the running
`app` container (which has Go installed), copies the result to
`services/warehouse/bin/warehouse`, and builds the `FROM scratch` image from
`Dockerfile.prebuilt` (compose default).

Alternatively, with the Go toolchain on the host:

```bash
CGO_ENABLED=0 go build -trimpath -ldflags="-s -w" -o bin/warehouse ./cmd/server
docker compose build warehouse
```

`Dockerfile` (multi-stage, `golang:1.23-bookworm` + `alpine`) is kept as the
canonical reference build; use it with
`docker compose build --build-arg` / `docker build -f Dockerfile .` when a
normal registry pull is available.

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
docker run --rm -v "$($PWD.Path):/w" -w /w golang:1.23-bookworm \
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
