-- Add escrow_held column to orders table if it doesn't exist
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS escrow_held TINYINT(1) DEFAULT 0 AFTER payment_method;

-- Also add paystack_secure_held if it doesn't exist (for new payment system)
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS paystack_secure_held TINYINT(1) DEFAULT 0 AFTER escrow_held;



