-- Create Test User Accounts for Build Mate
-- Run this in phpMyAdmin or MySQL command line

USE `goa`;

-- ===============================================
-- CREATE TEST USERS
-- ===============================================

-- 1. ADMIN USER (already exists, but let's ensure it's there)
-- Email: admin@buildmate.com
-- Password: admin123
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Admin User', 'admin@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE 
    `name` = 'Admin User',
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 2. SUPPLIER USER
-- Email: supplier@buildmate.com
-- Password: supplier123
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Test Supplier', 'supplier@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier')
ON DUPLICATE KEY UPDATE 
    `name` = 'Test Supplier',
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Get the supplier user ID for the supplier profile
SET @supplier_user_id = (SELECT id FROM users WHERE email = 'supplier@buildmate.com');

-- Create supplier profile (approved and verified)
INSERT INTO `suppliers` (`user_id`, `business_name`, `business_address`, `phone`, `kyc_status`, `verified_badge`, `plan_type`) 
VALUES (@supplier_user_id, 'Test Building Supplies Ltd', 'Accra, Ghana', '+233 24 123 4567', 'approved', 1, 'freemium')
ON DUPLICATE KEY UPDATE 
    `business_name` = 'Test Building Supplies Ltd',
    `business_address` = 'Accra, Ghana',
    `kyc_status` = 'approved',
    `verified_badge` = 1;

-- 3. CUSTOMER/BUYER USER
-- Email: customer@buildmate.com
-- Password: customer123
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Test Customer', 'customer@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer')
ON DUPLICATE KEY UPDATE 
    `name` = 'Test Customer',
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 4. ANOTHER CUSTOMER USER
-- Email: buyer@buildmate.com
-- Password: buyer123
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('John Buyer', 'buyer@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer')
ON DUPLICATE KEY UPDATE 
    `name` = 'John Buyer',
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 5. LOGISTICS USER
-- Email: logistics@buildmate.com
-- Password: logistics123
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Logistics Driver', 'logistics@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'logistics')
ON DUPLICATE KEY UPDATE 
    `name` = 'Logistics Driver',
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- ===============================================
-- ADD SAMPLE CATEGORIES
-- ===============================================
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Cement & Concrete', 'cement-concrete', 'Cement bags, concrete mix, and related materials'),
('Steel & Iron Rods', 'steel-iron-rods', 'Construction steel bars, iron rods, and metal materials'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and accessories'),
('Tiles & Flooring', 'tiles-flooring', 'Floor tiles, wall tiles, and flooring materials'),
('Paints & Coatings', 'paints-coatings', 'Interior and exterior paints, primers, and coatings'),
('Plumbing Supplies', 'plumbing-supplies', 'Pipes, fittings, and plumbing accessories'),
('Electrical Materials', 'electrical-materials', 'Wires, switches, sockets, and electrical supplies'),
('Tools & Equipment', 'tools-equipment', 'Construction tools and equipment')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- ===============================================
-- ADD SAMPLE PRODUCTS (for the test supplier)
-- ===============================================

-- Get supplier ID
SET @supplier_id = (SELECT id FROM suppliers WHERE user_id = @supplier_user_id LIMIT 1);

-- Get category IDs (use first available category if specific ones don't exist)
SET @category_cement = (SELECT id FROM categories WHERE slug = 'cement-concrete' LIMIT 1);
SET @category_steel = (SELECT id FROM categories WHERE slug = 'steel-iron-rods' LIMIT 1);
SET @category_roofing = (SELECT id FROM categories WHERE slug = 'roofing-materials' LIMIT 1);

-- Fallback to first category if specific categories don't exist
SET @category_cement = IFNULL(@category_cement, (SELECT id FROM categories LIMIT 1));
SET @category_steel = IFNULL(@category_steel, (SELECT id FROM categories LIMIT 1));
SET @category_roofing = IFNULL(@category_roofing, (SELECT id FROM categories LIMIT 1));

-- Only insert products if we have a supplier and categories
INSERT INTO `products` (`supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`) 
SELECT @supplier_id, @category_cement, 'Dangote Cement 50kg Bag', 'dangote-cement-50kg', 'High quality Portland cement, 50kg bag. Perfect for all construction needs.', 6500, 'GHS', 500, 1
WHERE @supplier_id IS NOT NULL AND @category_cement IS NOT NULL
ON DUPLICATE KEY UPDATE `stock` = VALUES(`stock`);

INSERT INTO `products` (`supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`) 
SELECT @supplier_id, @category_steel, 'Steel Rods 12mm (6 meters)', 'steel-rods-12mm', 'High tensile steel reinforcement bars, 12mm diameter, 6 meters length.', 4500, 'GHS', 200, 1
WHERE @supplier_id IS NOT NULL AND @category_steel IS NOT NULL
ON DUPLICATE KEY UPDATE `stock` = VALUES(`stock`);

INSERT INTO `products` (`supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`) 
SELECT @supplier_id, @category_roofing, 'Aluminum Roofing Sheets', 'aluminum-roofing-sheets', 'Durable aluminum roofing sheets, 0.5mm thickness, various colors available.', 8500, 'GHS', 150, 1
WHERE @supplier_id IS NOT NULL AND @category_roofing IS NOT NULL
ON DUPLICATE KEY UPDATE `stock` = VALUES(`stock`);

INSERT INTO `products` (`supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`) 
SELECT @supplier_id, @category_cement, 'Ghacem Cement 50kg', 'ghacem-cement-50kg', 'Premium quality Ghacem Portland cement, ideal for strong foundations.', 6200, 'GHS', 300, 1
WHERE @supplier_id IS NOT NULL AND @category_cement IS NOT NULL
ON DUPLICATE KEY UPDATE `stock` = VALUES(`stock`);

INSERT INTO `products` (`supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`) 
SELECT @supplier_id, @category_steel, 'Steel Rods 16mm (6 meters)', 'steel-rods-16mm', 'Heavy duty steel reinforcement bars, 16mm diameter, 6 meters length.', 7500, 'GHS', 180, 1
WHERE @supplier_id IS NOT NULL AND @category_steel IS NOT NULL
ON DUPLICATE KEY UPDATE `stock` = VALUES(`stock`);

-- ===============================================
-- SUCCESS MESSAGE
-- ===============================================
SELECT '✅ Test accounts created successfully!' AS Status;
SELECT '' AS '';
SELECT '🔐 LOGIN CREDENTIALS' AS Info;
SELECT '' AS '';
SELECT '👤 ADMIN' AS Role, 'admin@buildmate.com' AS Email, 'admin123' AS Password
UNION ALL
SELECT '🏢 SUPPLIER', 'supplier@buildmate.com', 'supplier123'
UNION ALL
SELECT '🛒 CUSTOMER', 'customer@buildmate.com', 'customer123'
UNION ALL
SELECT '🛒 BUYER', 'buyer@buildmate.com', 'buyer123'
UNION ALL
SELECT '🚚 LOGISTICS', 'logistics@buildmate.com', 'logistics123';

SELECT '' AS '';
SELECT '📦 Sample products added for test supplier' AS Note;
SELECT '✅ All accounts are active and ready to use' AS Status;
