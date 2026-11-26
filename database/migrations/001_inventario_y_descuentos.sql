BEGIN;

ALTER TABLE sales
ADD COLUMN IF NOT EXISTS discount_percent NUMERIC(5,2) NOT NULL DEFAULT 0
CHECK (discount_percent BETWEEN 0 AND 100);

CREATE TABLE IF NOT EXISTS inventory_movements (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id),
    user_id BIGINT NOT NULL REFERENCES users(id),
    movement_type VARCHAR(10) NOT NULL
        CHECK (movement_type IN ('entry', 'exit')),
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    previous_stock INTEGER NOT NULL CHECK (previous_stock >= 0),
    new_stock INTEGER NOT NULL CHECK (new_stock >= 0),
    reason VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_inventory_movements_product_date
ON inventory_movements(product_id, created_at DESC);

COMMIT;
