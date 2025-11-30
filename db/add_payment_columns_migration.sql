-- Add payment_reference and payment_method columns to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) NULL AFTER payment_method,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL AFTER status;



