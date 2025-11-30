-- Fix status ENUM to include 'out_for_delivery' or convert to VARCHAR
-- This fixes the "string did not match expected pattern" error

-- Option 1: Convert to VARCHAR (more flexible)
ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'placed';

-- Option 2: If you prefer ENUM, add 'out_for_delivery' (uncomment if needed)
-- ALTER TABLE `orders` 
-- MODIFY COLUMN `status` ENUM('pending', 'placed', 'paid', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'placed';

