-- Warehouse service owns this schema. The Laravel application has no access to it.
CREATE TABLE IF NOT EXISTS stock (
    product_id BIGINT PRIMARY KEY,
    -- physical units present in the warehouse
    quantity   BIGINT NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    -- units already promised to orders that are currently being placed
    reserved   BIGINT NOT NULL DEFAULT 0 CHECK (reserved >= 0),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT stock_not_oversold CHECK (reserved <= quantity)
);

CREATE TABLE IF NOT EXISTS reservations (
    order_uuid TEXT PRIMARY KEY,
    items      JSONB NOT NULL,
    status     TEXT NOT NULL DEFAULT 'reserved' CHECK (status IN ('reserved', 'released')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS reservations_status_idx ON reservations (status);

-- Mirrors the demo catalogue (products 1..8). Product 1 is seeded with a single
-- unit on purpose so the race-condition proof has a "last item" to fight over.
INSERT INTO stock (product_id, quantity, reserved) VALUES
    (1, 1,   0),
    (2, 100, 0),
    (3, 100, 0),
    (4, 100, 0),
    (5, 100, 0),
    (6, 100, 0),
    (7, 100, 0),
    (8, 100, 0)
ON CONFLICT (product_id) DO NOTHING;
