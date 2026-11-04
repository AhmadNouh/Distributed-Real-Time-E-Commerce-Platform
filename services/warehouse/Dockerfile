# syntax=docker/dockerfile:1

# ---- build stage -----------------------------------------------------------
FROM golang:1.24-bookworm AS build

WORKDIR /src

COPY go.mod go.sum ./
RUN go mod download

COPY . .

RUN CGO_ENABLED=0 GOOS=linux go build \
    -trimpath \
    -ldflags="-s -w" \
    -o /out/warehouse \
    ./cmd/server

# ---- runtime stage ---------------------------------------------------------
FROM alpine:3.20

RUN apk add --no-cache ca-certificates tzdata \
 && adduser -D -u 10001 app

COPY --from=build /out/warehouse /usr/local/bin/warehouse

USER app
EXPOSE 50051

ENTRYPOINT ["warehouse"]
