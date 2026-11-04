# syntax=docker/dockerfile:1

# ---- build stage -----------------------------------------------------------
# The Go dependencies are vendored (services/warehouse/vendor is committed), so
# this stage never reaches the network: no `go mod download`, no proxy.golang.org.
# That keeps `docker compose up -d --build` working on a fresh clone and on
# machines where the Go module proxy is blocked or flaky.
FROM golang:1.24-bookworm AS build

WORKDIR /src

COPY . .

RUN CGO_ENABLED=0 GOOS=linux go build \
    -mod=vendor \
    -trimpath \
    -ldflags="-s -w" \
    -o /out/warehouse \
    ./cmd/server

# ---- runtime stage ---------------------------------------------------------
FROM alpine:3.20

RUN apk add --no-cache ca-certificates tzdata \
 && adduser -D -u 10001 app

# Keep the binary at /warehouse so the compose healthcheck
# (`/warehouse -healthcheck`) works for both this image and Dockerfile.prebuilt.
COPY --from=build /out/warehouse /warehouse

USER app
EXPOSE 50051

ENTRYPOINT ["/warehouse"]
