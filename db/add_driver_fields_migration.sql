-- Add driver/rider fields to deliveries table
ALTER TABLE deliveries 
ADD COLUMN IF NOT EXISTS driver_name VARCHAR(100) NULL AFTER status,
ADD COLUMN IF NOT EXISTS driver_phone VARCHAR(20) NULL AFTER driver_name,
ADD COLUMN IF NOT EXISTS driver_vehicle_number VARCHAR(50) NULL AFTER driver_phone,
ADD COLUMN IF NOT EXISTS logistics_user_id INT UNSIGNED NULL AFTER driver_vehicle_number,
ADD COLUMN IF NOT EXISTS estimated_delivery_time DATETIME NULL AFTER logistics_user_id,
ADD COLUMN IF NOT EXISTS actual_delivery_time DATETIME NULL AFTER estimated_delivery_time,
ADD INDEX idx_logistics_user_id (logistics_user_id),
ADD FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL;



