-- Fix status column size to accommodate longer status values
ALTER TABLE orders 
MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending';



