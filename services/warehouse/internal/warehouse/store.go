package warehouse

import (
	"context"
	_ "embed"
	"encoding/json"
	"errors"
	"fmt"
	"sort"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

//go:embed schema.sql
var schemaSQL string

// Item is one requested line of an order.
type Item struct {
	ProductID int64
	Quantity  int64
}

// Shortage explains why a single line could not be reserved.
type Shortage struct {
	ProductID int64
	Requested int64
	Available int64
}

// ReserveResult is the outcome of an all-or-nothing reservation.
type ReserveResult struct {
	Approved      bool
	Reason        string
	Shortages     []Shortage
	ReservationID string
}

// Reason values returned to the caller.
const (
	ReasonOK                = "OK"
	ReasonInsufficientStock = "INSUFFICIENT_STOCK"
	ReasonProductNotFound   = "PRODUCT_NOT_FOUND"
	ReasonInvalidRequest    = "INVALID_REQUEST"
	ReasonAlreadyReleased   = "ALREADY_RELEASED"
)

type reservationRow struct {
	ProductID int64 `json:"product_id"`
	Quantity  int64 `json:"quantity"`
}

// Store is the only component that talks to the warehouse database.
type Store struct {
	pool *pgxpool.Pool
}

func NewStore(ctx context.Context, dsn string) (*Store, error) {
	pool, err := pgxpool.New(ctx, dsn)
	if err != nil {
		return nil, fmt.Errorf("connect warehouse db: %w", err)
	}

	pingCtx, cancel := context.WithTimeout(ctx, 10*time.Second)
	defer cancel()

	if err := pool.Ping(pingCtx); err != nil {
		pool.Close()
		return nil, fmt.Errorf("ping warehouse db: %w", err)
	}

	store := &Store{pool: pool}

	if err := store.migrate(ctx); err != nil {
		pool.Close()
		return nil, err
	}

	return store, nil
}

func (s *Store) Close() { s.pool.Close() }

// migrate applies the (idempotent) schema. Running it on every boot keeps the
// container self-contained: drop the volume and it comes back seeded.
func (s *Store) migrate(ctx context.Context) error {
	if _, err := s.pool.Exec(ctx, schemaSQL); err != nil {
		return fmt.Errorf("apply warehouse schema: %w", err)
	}
	return nil
}

// SetStock

func (s *Store) SetStock(ctx context.Context, productId int64, quantity int64) error {
    query := `INSERT INTO stock (product_id, quantity, reserved, updated_at) 
              VALUES ($1, $2, 0, NOW()) 
              ON CONFLICT (product_id) 
              DO UPDATE SET quantity = $2, updated_at = NOW()`
              
    _, err := s.pool.Exec(ctx, query, productId, quantity)
    return err
}

// GetStock returns the sellable quantity (quantity - reserved).
func (s *Store) GetStock(ctx context.Context, productID int64) (available int64, found bool, err error) {
	var quantity, reserved int64

	err = s.pool.QueryRow(ctx,
		`SELECT quantity, reserved FROM stock WHERE product_id = $1`,
		productID,
	).Scan(&quantity, &reserved)

	if errors.Is(err, pgx.ErrNoRows) {
		return 0, false, nil
	}
	if err != nil {
		return 0, false, fmt.Errorf("get stock: %w", err)
	}

	return quantity - reserved, true, nil
}

// Reserve locks the requested rows, validates every line and only then applies
// the reservation. Concurrent requests for the same "last unit" serialize on
// the row lock, so exactly one of them can observe enough stock.
func (s *Store) Reserve(ctx context.Context, orderUUID string, items []Item) (ReserveResult, error) {
	merged, err := mergeItems(items)
	if err != nil {
		// A malformed request is a business answer, not a server failure: the
		// caller gets approved = false with a reason instead of a gRPC error.
		return ReserveResult{Approved: false, Reason: ReasonInvalidRequest}, nil
	}

	tx, err := s.pool.BeginTx(ctx, pgx.TxOptions{IsoLevel: pgx.ReadCommitted})
	if err != nil {
		return ReserveResult{}, fmt.Errorf("begin reserve tx: %w", err)
	}
	defer func() { _ = tx.Rollback(ctx) }()

	// Serialize retries of the same order (idempotency) before touching rows.
	if _, err := tx.Exec(ctx, `SELECT pg_advisory_xact_lock(hashtext($1))`, orderUUID); err != nil {
		return ReserveResult{}, fmt.Errorf("lock order uuid: %w", err)
	}

	var existingStatus string
	err = tx.QueryRow(ctx,
		`SELECT status FROM reservations WHERE order_uuid = $1`,
		orderUUID,
	).Scan(&existingStatus)

	switch {
	case err == nil && existingStatus == "reserved":
		return ReserveResult{Approved: true, Reason: ReasonOK, ReservationID: orderUUID}, nil
	case err == nil && existingStatus == "released":
		return ReserveResult{Approved: false, Reason: ReasonAlreadyReleased}, nil
	case err != nil && !errors.Is(err, pgx.ErrNoRows):
		return ReserveResult{}, fmt.Errorf("read reservation: %w", err)
	}

	ids := make([]int64, 0, len(merged))
	for _, item := range merged {
		ids = append(ids, item.ProductID)
	}

	// ORDER BY + FOR UPDATE takes the locks in a deterministic order, which
	// prevents deadlocks between two multi-item orders.
	rows, err := tx.Query(ctx,
		`SELECT product_id, quantity, reserved
		   FROM stock
		  WHERE product_id = ANY($1)
		  ORDER BY product_id
		    FOR UPDATE`,
		ids,
	)
	if err != nil {
		return ReserveResult{}, fmt.Errorf("lock stock rows: %w", err)
	}

	type stockRow struct{ quantity, reserved int64 }
	locked := make(map[int64]stockRow, len(merged))

	for rows.Next() {
		var productID int64
		var row stockRow
		if err := rows.Scan(&productID, &row.quantity, &row.reserved); err != nil {
			rows.Close()
			return ReserveResult{}, fmt.Errorf("scan stock row: %w", err)
		}
		locked[productID] = row
	}
	if err := rows.Err(); err != nil {
		rows.Close()
		return ReserveResult{}, fmt.Errorf("iterate stock rows: %w", err)
	}
	rows.Close()

	var shortages []Shortage
	missing := false

	for _, item := range merged {
		row, ok := locked[item.ProductID]
		if !ok {
			missing = true
			shortages = append(shortages, Shortage{
				ProductID: item.ProductID,
				Requested: item.Quantity,
				Available: 0,
			})
			continue
		}

		available := row.quantity - row.reserved
		if available < item.Quantity {
			shortages = append(shortages, Shortage{
				ProductID: item.ProductID,
				Requested: item.Quantity,
				Available: available,
			})
		}
	}

	if len(shortages) > 0 {
		reason := ReasonInsufficientStock
		if missing {
			reason = ReasonProductNotFound
		}
		return ReserveResult{Approved: false, Reason: reason, Shortages: shortages}, nil
	}

	for _, item := range merged {
		if _, err := tx.Exec(ctx,
			`UPDATE stock
			    SET reserved = reserved + $1, updated_at = now()
			  WHERE product_id = $2`,
			item.Quantity, item.ProductID,
		); err != nil {
			return ReserveResult{}, fmt.Errorf("reserve stock: %w", err)
		}
	}

	payload, err := json.Marshal(toReservationRows(merged))
	if err != nil {
		return ReserveResult{}, fmt.Errorf("encode reservation: %w", err)
	}

	if _, err := tx.Exec(ctx,
		`INSERT INTO reservations (order_uuid, items, status) VALUES ($1, $2, 'reserved')`,
		orderUUID, payload,
	); err != nil {
		return ReserveResult{}, fmt.Errorf("store reservation: %w", err)
	}

	if err := tx.Commit(ctx); err != nil {
		return ReserveResult{}, fmt.Errorf("commit reserve tx: %w", err)
	}

	return ReserveResult{Approved: true, Reason: ReasonOK, ReservationID: orderUUID}, nil
}

// Release returns the reserved quantities of an order to the pool. It is safe
// to call twice: the second call reports released = true with 0 items.
func (s *Store) Release(ctx context.Context, orderUUID string) (released bool, releasedItems int64, err error) {
	tx, err := s.pool.BeginTx(ctx, pgx.TxOptions{IsoLevel: pgx.ReadCommitted})
	if err != nil {
		return false, 0, fmt.Errorf("begin release tx: %w", err)
	}
	defer func() { _ = tx.Rollback(ctx) }()

	if _, err := tx.Exec(ctx, `SELECT pg_advisory_xact_lock(hashtext($1))`, orderUUID); err != nil {
		return false, 0, fmt.Errorf("lock order uuid: %w", err)
	}

	var payload []byte
	var status string

	err = tx.QueryRow(ctx,
		`SELECT items, status FROM reservations WHERE order_uuid = $1 FOR UPDATE`,
		orderUUID,
	).Scan(&payload, &status)

	if errors.Is(err, pgx.ErrNoRows) {
		return false, 0, nil
	}
	if err != nil {
		return false, 0, fmt.Errorf("read reservation: %w", err)
	}
	if status == "released" {
		return true, 0, nil
	}

	var items []reservationRow
	if err := json.Unmarshal(payload, &items); err != nil {
		return false, 0, fmt.Errorf("decode reservation: %w", err)
	}

	for _, item := range items {
		if _, err := tx.Exec(ctx,
			`UPDATE stock
			    SET reserved = GREATEST(reserved - $1, 0), updated_at = now()
			  WHERE product_id = $2`,
			item.Quantity, item.ProductID,
		); err != nil {
			return false, 0, fmt.Errorf("release stock: %w", err)
		}
		releasedItems += item.Quantity
	}

	if _, err := tx.Exec(ctx,
		`UPDATE reservations SET status = 'released', updated_at = now() WHERE order_uuid = $1`,
		orderUUID,
	); err != nil {
		return false, 0, fmt.Errorf("mark reservation released: %w", err)
	}

	if err := tx.Commit(ctx); err != nil {
		return false, 0, fmt.Errorf("commit release tx: %w", err)
	}

	return true, releasedItems, nil
}

// toReservationRows converts the merged items to the JSON shape that Release()
// reads back. reservationRow carries `json:"product_id"` tags, so the stored
// payload survives a round-trip through the database.
func toReservationRows(items []Item) []reservationRow {
	rows := make([]reservationRow, 0, len(items))
	for _, item := range items {
		rows = append(rows, reservationRow{ProductID: item.ProductID, Quantity: item.Quantity})
	}
	return rows
}

// mergeItems combines duplicated product lines so a request like
// [{1,1},{1,1}] is validated as [{1,2}] and cannot bypass the stock check.
func mergeItems(items []Item) ([]Item, error) {
	if len(items) == 0 {
		return nil, errors.New("empty items")
	}

	byProduct := make(map[int64]int64, len(items))

	for _, item := range items {
		if item.ProductID <= 0 {
			return nil, fmt.Errorf("invalid product id %d", item.ProductID)
		}
		if item.Quantity <= 0 {
			return nil, fmt.Errorf("invalid quantity %d for product %d", item.Quantity, item.ProductID)
		}
		byProduct[item.ProductID] += item.Quantity
	}

	merged := make([]Item, 0, len(byProduct))
	for productID, quantity := range byProduct {
		merged = append(merged, Item{ProductID: productID, Quantity: quantity})
	}

	sort.Slice(merged, func(i, j int) bool {
		return merged[i].ProductID < merged[j].ProductID
	})

	return merged, nil
}
