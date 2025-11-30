-- Add paystack_secure_held column to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS paystack_secure_held TINYINT(1) DEFAULT 0 AFTER payment_method;



