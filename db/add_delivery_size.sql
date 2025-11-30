-- Add delivery_size column to products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS delivery_size ENUM('small', 'large') DEFAULT 'small' AFTER stock;

-- Add vehicle_type column to orders table
ALTER TABLE orders ADD COLUMN IF NOT EXISTS vehicle_type ENUM('motorbike', 'truck') DEFAULT 'motorbike' AFTER payment_status;

-- Add vehicle_type column to deliveries table
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS vehicle_type ENUM('motorbike', 'truck') DEFAULT 'motorbike' AFTER status;

-- Update existing products to have default delivery_size
UPDATE products SET delivery_size = 'small' WHERE delivery_size IS NULL;



