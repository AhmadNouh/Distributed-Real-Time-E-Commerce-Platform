package main

import (
	"context"
	"errors"
	"log/slog"
	"net"
	"os"
	"os/signal"
	"syscall"
	"time"

	"google.golang.org/grpc"
	"google.golang.org/grpc/health"
	healthpb "google.golang.org/grpc/health/grpc_health_v1"
	"google.golang.org/grpc/reflection"

	pb "ecommerce/warehouse/gen/warehouse/v1"
	"ecommerce/warehouse/internal/warehouse"
)

func env(key, fallback string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return fallback
}

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))

	dsn := env("DB_DSN", "postgres://warehouse:warehouse@warehouse-db:5432/warehouse?sslmode=disable")
	addr := ":" + env("GRPC_PORT", "50051")

	startupCtx, cancelStartup := context.WithTimeout(context.Background(), 60*time.Second)
	defer cancelStartup()

	store, err := warehouse.NewStore(startupCtx, dsn)
	if err != nil {
		logger.Error("warehouse db unavailable", "error", err)
		os.Exit(1)
	}
	defer store.Close()

	listener, err := net.Listen("tcp", addr)
	if err != nil {
		logger.Error("listen failed", "addr", addr, "error", err)
		os.Exit(1)
	}

	server := grpc.NewServer(
		grpc.MaxRecvMsgSize(4 << 20),
		grpc.ConnectionTimeout(5*time.Second),
	)

	pb.RegisterWarehouseServiceServer(server, warehouse.NewServer(store, logger))

	healthServer := health.NewServer()
	healthServer.SetServingStatus("warehouse.v1.WarehouseService", healthpb.HealthCheckResponse_SERVING)
	healthpb.RegisterHealthServer(server, healthServer)

	// Lets grpcurl discover the service without the .proto file.
	reflection.Register(server)

	go func() {
		logger.Info("warehouse gRPC server listening", "addr", addr)
		if err := server.Serve(listener); err != nil {
			logger.Error("grpc server stopped", "error", err)
		}
	}()

	stop := make(chan os.Signal, 1)
	signal.Notify(stop, os.Interrupt, syscall.SIGTERM)
	<-stop

	logger.Info("shutting down warehouse service")

	healthServer.SetServingStatus("warehouse.v1.WarehouseService", healthpb.HealthCheckResponse_NOT_SERVING)

	done := make(chan struct{})
	go func() {
		server.GracefulStop()
		close(done)
	}()

	select {
	case <-done:
	case <-time.After(10 * time.Second):
		logger.Warn("graceful stop timed out, forcing")
		server.Stop()
	}

	if errors.Is(err, context.DeadlineExceeded) {
		logger.Warn("startup deadline exceeded")
	}
}
