-- Order Tracking System Migration
-- Run this to add tracking fields to orders table

-- Add tracking fields to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) NULL AFTER id,
ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(10, 2) DEFAULT 0.00 AFTER total_cents,
ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) NULL AFTER buyer_id,
ADD COLUMN IF NOT EXISTS special_instructions TEXT NULL AFTER delivery_address,
ADD COLUMN IF NOT EXISTS current_status ENUM('pending', 'payment_confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending' AFTER status,
ADD COLUMN IF NOT EXISTS estimated_delivery_date DATETIME NULL AFTER current_status,
ADD COLUMN IF NOT EXISTS order_placed_at DATETIME NULL AFTER estimated_delivery_date,
ADD COLUMN IF NOT EXISTS payment_confirmed_at DATETIME NULL AFTER order_placed_at,
ADD COLUMN IF NOT EXISTS processing_started_at DATETIME NULL AFTER payment_confirmed_at,
ADD COLUMN IF NOT EXISTS shipped_at DATETIME NULL AFTER processing_started_at,
ADD COLUMN IF NOT EXISTS out_for_delivery_at DATETIME NULL AFTER shipped_at,
ADD COLUMN IF NOT EXISTS delivered_at DATETIME NULL AFTER out_for_delivery_at;

-- Update existing orders to set order_placed_at
UPDATE orders SET order_placed_at = created_at WHERE order_placed_at IS NULL;

-- Create order_status_history table
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `notes` TEXT NULL,
  `changed_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Generate order numbers for existing orders
UPDATE orders SET order_number = CONCAT('BM-', LPAD(id, 6, '0')) WHERE order_number IS NULL;



