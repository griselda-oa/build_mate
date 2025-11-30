-- Fix status column to be large enough for all status values
-- This migration ensures the status column can hold 'payment_confirmed' and other statuses

ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending';

-- Also fix current_status if it exists
ALTER TABLE `orders` 
MODIFY COLUMN `current_status` VARCHAR(50) DEFAULT 'pending';



