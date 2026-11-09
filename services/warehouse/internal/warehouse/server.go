package warehouse

import (
	"context"
	"errors"
	"log/slog"
	"time"

	pb "ecommerce/warehouse/gen/warehouse/v1"

	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/status"
)

// Server adapts the Store to the generated gRPC service interface.
type Server struct {
	pb.UnimplementedWarehouseServiceServer
	store *Store
	log   *slog.Logger
}

func NewServer(store *Store, log *slog.Logger) *Server {
	return &Server{store: store, log: log}
}

func (s *Server) SetStock(ctx context.Context, req *pb.SetStockRequest) (*pb.SetStockResponse, error) {
    err := s.store.SetStock(ctx, req.GetProductId(), req.GetQuantity())
    if err != nil {
        s.log.Error("set stock failed", "product_id", req.GetProductId(), "error", err)
        return &pb.SetStockResponse{Success: false}, status.Errorf(codes.Internal, "could not set stock: %v", err)
    }

    return &pb.SetStockResponse{
        ProductId: req.GetProductId(),
        Quantity:  req.GetQuantity(),
        Success:   true,
    }, nil
}

func (s *Server) GetStock(ctx context.Context, req *pb.GetStockRequest) (*pb.GetStockResponse, error) {
	if req.GetProductId() <= 0 {
		return nil, status.Error(codes.InvalidArgument, "product_id must be a positive number")
	}

	available, found, err := s.store.GetStock(ctx, req.GetProductId())
	if err != nil {
		s.log.Error("get stock failed", "product_id", req.GetProductId(), "error", err)
		return nil, status.Errorf(codes.Internal, "could not read stock: %v", err)
	}

	return &pb.GetStockResponse{
		ProductId: req.GetProductId(),
		Available: available,
		Found:     found,
	}, nil
}

func (s *Server) Reserve(ctx context.Context, req *pb.ReserveRequest) (*pb.ReserveResponse, error) {
	started := time.Now()

	if req.GetOrderUuid() == "" {
		return &pb.ReserveResponse{Approved: false, Reason: ReasonInvalidRequest}, nil
	}

	items := make([]Item, 0, len(req.GetItems()))
	for _, item := range req.GetItems() {
		items = append(items, Item{ProductID: item.GetProductId(), Quantity: item.GetQuantity()})
	}

	result, err := s.store.Reserve(ctx, req.GetOrderUuid(), items)
	if err != nil {
		if errors.Is(err, context.DeadlineExceeded) || errors.Is(err, context.Canceled) {
			s.log.Warn("reserve cancelled by client deadline", "order_uuid", req.GetOrderUuid())
			return nil, status.Error(codes.DeadlineExceeded, "reservation cancelled by the client deadline")
		}

		s.log.Error("reserve failed", "order_uuid", req.GetOrderUuid(), "error", err)
		return nil, status.Errorf(codes.Internal, "could not reserve stock: %v", err)
	}

	response := &pb.ReserveResponse{
		Approved:      result.Approved,
		Reason:        result.Reason,
		ReservationId: result.ReservationID,
	}

	for _, shortage := range result.Shortages {
		response.Shortages = append(response.Shortages, &pb.ItemShortage{
			ProductId: shortage.ProductID,
			Requested: shortage.Requested,
			Available: shortage.Available,
		})
	}

	s.log.Info("reserve handled",
		"order_uuid", req.GetOrderUuid(),
		"approved", result.Approved,
		"reason", result.Reason,
		"items", len(items),
		"duration_ms", time.Since(started).Milliseconds(),
	)

	return response, nil
}

func (s *Server) Release(ctx context.Context, req *pb.ReleaseRequest) (*pb.ReleaseResponse, error) {
	if req.GetOrderUuid() == "" {
		return nil, status.Error(codes.InvalidArgument, "order_uuid is required")
	}

	released, items, err := s.store.Release(ctx, req.GetOrderUuid())
	if err != nil {
		s.log.Error("release failed", "order_uuid", req.GetOrderUuid(), "error", err)
		return nil, status.Errorf(codes.Internal, "could not release stock: %v", err)
	}

	s.log.Info("release handled", "order_uuid", req.GetOrderUuid(), "released", released, "items", items)

	return &pb.ReleaseResponse{Released: released, ReleasedItems: items}, nil
}
