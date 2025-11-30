-- URGENT: Fix status column size to prevent truncation errors
-- This ensures the status column can hold values like 'placed', 'paid', 'processing', 'out_for_delivery', 'delivered', 'cancelled'

-- First, check current column type
-- SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'status';

-- Modify status column to VARCHAR(50) to accommodate all status values
ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'placed';

-- Verify the change
-- DESCRIBE orders;



