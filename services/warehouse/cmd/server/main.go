package main

import (
	"context"
	"flag"
	"log/slog"
	"net"
	"os"
	"os/signal"
	"syscall"
	"time"

	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"
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
	healthcheck := flag.Bool("healthcheck", false, "probe the local gRPC health service and exit")
	flag.Parse()

	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))

	addr := ":" + env("GRPC_PORT", "50051")

	if *healthcheck {
		os.Exit(runHealthcheck())
	}

	dsn := env("DB_DSN", "postgres://warehouse:warehouse@warehouse-db:5432/warehouse?sslmode=disable")

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
}

// runHealthcheck probes the local health service. The runtime image is built
// `FROM scratch`, so there is no shell, curl or nc available for a compose
// healthcheck; the binary probes itself instead.
func runHealthcheck() int {
	target := "127.0.0.1:" + env("GRPC_PORT", "50051")

	conn, err := grpc.NewClient(target, grpc.WithTransportCredentials(insecure.NewCredentials()))
	if err != nil {
		return 1
	}
	defer conn.Close()

	ctx, cancel := context.WithTimeout(context.Background(), 3*time.Second)
	defer cancel()

	response, err := healthpb.NewHealthClient(conn).Check(ctx, &healthpb.HealthCheckRequest{
		Service: "warehouse.v1.WarehouseService",
	})
	if err != nil || response.GetStatus() != healthpb.HealthCheckResponse_SERVING {
		return 1
	}

	return 0
}
