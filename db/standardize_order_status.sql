-- Standardize Order Status Values
-- This migration updates all order statuses to use the simplified values:
-- placed, paid, processing, out_for_delivery, delivered

-- First, ensure the status column is large enough
ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'placed';

-- Update existing status values to standardized ones
UPDATE `orders` 
SET `status` = 'placed' 
WHERE `status` IN ('pending', 'order_placed', 'new');

UPDATE `orders` 
SET `status` = 'paid' 
WHERE `status` IN ('paid_escrow', 'paid_paystack_secure', 'payment_confirmed', 'paid', 'success');

UPDATE `orders` 
SET `status` = 'processing' 
WHERE `status` IN ('processing', 'processing_order', 'preparing', 'ready_for_pickup', 'pending_pickup');

UPDATE `orders` 
SET `status` = 'out_for_delivery' 
WHERE `status` IN ('out_for_delivery', 'in_transit', 'shipped', 'picked_up', 'on_the_way');

UPDATE `orders` 
SET `status` = 'delivered' 
WHERE `status` IN ('delivered', 'completed', 'delivery_complete');

-- Set default for new orders
ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'placed';



