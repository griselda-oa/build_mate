-- Add payment_reference column to advertisements table
-- This allows tracking which payment was used for each advertisement

ALTER TABLE advertisements
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) NULL AFTER status,
ADD INDEX IF NOT EXISTS idx_payment_ref (payment_reference);

