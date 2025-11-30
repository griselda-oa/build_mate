-- Premium Supplier System Migration
-- Run this to add premium features to Build Mate

-- 1. Add premium fields to suppliers table
ALTER TABLE suppliers 
ADD COLUMN IF NOT EXISTS plan_type ENUM('freemium','premium') DEFAULT 'freemium' AFTER verified_badge,
ADD COLUMN IF NOT EXISTS premium_expires_at DATETIME NULL AFTER plan_type,
ADD COLUMN IF NOT EXISTS sentiment_score FLOAT DEFAULT 1.0 AFTER premium_expires_at,
ADD COLUMN IF NOT EXISTS performance_warnings INT DEFAULT 0 AFTER sentiment_score;

-- 2. Create advertisements table for supplier-paid ads
CREATE TABLE IF NOT EXISTS advertisements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500),
    title VARCHAR(255),
    description TEXT,
    status ENUM('active','expired','pending','rejected') DEFAULT 'pending',
    start_date DATETIME,
    end_date DATETIME,
    clicks INT UNSIGNED DEFAULT 0,
    impressions INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add indexes for faster ranking
CREATE INDEX IF NOT EXISTS idx_plan ON suppliers(plan_type);
CREATE INDEX IF NOT EXISTS idx_sentiment ON suppliers(sentiment_score);
CREATE INDEX IF NOT EXISTS idx_premium_expires ON suppliers(premium_expires_at);

-- 4. Create premium_subscriptions table to track payment history
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    payment_reference VARCHAR(100) UNIQUE,
    amount_cents INT UNSIGNED NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    plan_duration_days INT UNSIGNED DEFAULT 30,
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    started_at DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_payment_ref (payment_reference),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Update existing suppliers to have default values
UPDATE suppliers 
SET plan_type = 'freemium',
    sentiment_score = 1.0,
    performance_warnings = 0
WHERE plan_type IS NULL;
