-- Build Mate Delivery System Migration
-- Adds regional delivery restrictions, product size categories, and delivery tracking

-- 1. Add size_category to products table
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS size_category ENUM('small', 'large') DEFAULT 'small' AFTER stock;

-- 2. Rename shipping_address to delivery_address in orders table
ALTER TABLE orders 
CHANGE COLUMN shipping_address delivery_address TEXT NOT NULL;

-- 3. Add delivery location fields to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS delivery_lat DECIMAL(10, 8) NULL AFTER delivery_address,
ADD COLUMN IF NOT EXISTS delivery_lng DECIMAL(11, 8) NULL AFTER delivery_lat,
ADD COLUMN IF NOT EXISTS delivery_region ENUM('Greater Accra', 'Ashanti Region') NULL AFTER delivery_lng,
ADD COLUMN IF NOT EXISTS delivery_phone VARCHAR(20) NULL AFTER delivery_region;

-- 4. Update orders status enum to include paid_escrow (if not exists)
-- Note: This may fail if enum already has these values, that's okay
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'paid', 'paid_escrow', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending';

-- 5. Add escrow_held column if it doesn't exist
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS escrow_held TINYINT(1) DEFAULT 0 AFTER payment_method;

-- 6. Create deliveries table for tracking
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    delivery_lat DECIMAL(10, 8) NOT NULL,
    delivery_lng DECIMAL(11, 8) NOT NULL,
    street TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    region ENUM('Greater Accra', 'Ashanti Region') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    vehicle_type ENUM('motorbike', 'truck') NOT NULL DEFAULT 'motorbike',
    status ENUM('pending_pickup', 'ready_for_pickup', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending_pickup',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_region (region),
    INDEX idx_vehicle_type (vehicle_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create delivery_status_history table for tracking status changes
CREATE TABLE IF NOT EXISTS delivery_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT UNSIGNED NOT NULL,
    status ENUM('pending_pickup', 'ready_for_pickup', 'picked_up', 'in_transit', 'delivered', 'failed') NOT NULL,
    changed_by INT UNSIGNED NULL,
    changed_by_role ENUM('supplier', 'admin', 'logistics', 'system') DEFAULT 'system',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_delivery_id (delivery_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



