-- Delivery Workflow Migration
-- Adds delivery verification code, photo proof, and payment release tracking

-- Update deliveries table
ALTER TABLE deliveries 
ADD COLUMN IF NOT EXISTS delivery_code VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS delivery_photo VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS delivered_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS confirmed_by_buyer TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL,
ADD COLUMN IF NOT EXISTS buyer_notes TEXT NULL,
ADD COLUMN IF NOT EXISTS updated_by INT NULL,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update orders table for payment release tracking
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_released TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS payment_released_at TIMESTAMP NULL;

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS idx_delivery_code ON deliveries(delivery_code);
CREATE INDEX IF NOT EXISTS idx_delivery_status ON deliveries(status);
CREATE INDEX IF NOT EXISTS idx_order_payment_released ON orders(payment_released);



