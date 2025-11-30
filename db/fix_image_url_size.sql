-- Increase image_url column size to accommodate longer URLs
-- First try VARCHAR(1000), if that fails, use TEXT
ALTER TABLE products MODIFY COLUMN image_url VARCHAR(1000) DEFAULT NULL;

-- If VARCHAR(1000) is still not enough, use TEXT instead (uncomment the line below and comment the one above)
-- ALTER TABLE products MODIFY COLUMN image_url TEXT DEFAULT NULL;

