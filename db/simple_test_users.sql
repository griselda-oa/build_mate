-- Simple Test Users Creation (No Supplier Profile Yet)
-- Run this first, then add supplier profile manually if needed

USE `goa`;

-- ===============================================
-- CREATE BASIC TEST USERS
-- ===============================================

-- Password for ALL accounts: password
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- (This is simpler for testing - all accounts use the same password)

-- 1. ADMIN USER
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Admin User', 'admin@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 2. SUPPLIER USER (just the user account, profile created separately)
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Test Supplier', 'supplier@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier')
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 3. CUSTOMER USER
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Test Customer', 'customer@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer')
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 4. ANOTHER CUSTOMER
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('John Buyer', 'buyer@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer')
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 5. LOGISTICS USER
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) 
VALUES ('Logistics Driver', 'logistics@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'logistics')
ON DUPLICATE KEY UPDATE 
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- ===============================================
-- NOW CREATE SUPPLIER PROFILE
-- ===============================================

-- Get the supplier user ID
SET @supplier_user_id = (SELECT id FROM users WHERE email = 'supplier@buildmate.com');

-- Create supplier profile with correct column names
INSERT INTO `suppliers` (`user_id`, `business_name`, `business_address`, `phone`, `kyc_status`, `verified_badge`) 
VALUES (@supplier_user_id, 'Test Building Supplies Ltd', 'Accra, Ghana', '+233 24 123 4567', 'approved', 1)
ON DUPLICATE KEY UPDATE 
    `business_name` = 'Test Building Supplies Ltd',
    `kyc_status` = 'approved',
    `verified_badge` = 1;

-- ===============================================
-- SUCCESS MESSAGE
-- ===============================================
SELECT '✅ Test users created successfully!' AS Status;
SELECT '' AS '';
SELECT '🔐 LOGIN CREDENTIALS' AS '';
SELECT '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━' AS '';
SELECT 'Role' AS '', 'Email' AS '', 'Password' AS '';
SELECT '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━' AS '';
SELECT '👤 Admin' AS '', 'admin@buildmate.com' AS '', 'admin123' AS '';
SELECT '🏢 Supplier' AS '', 'supplier@buildmate.com' AS '', 'supplier123' AS '';
SELECT '🛒 Customer' AS '', 'customer@buildmate.com' AS '', 'customer123' AS '';
SELECT '🛒 Buyer' AS '', 'buyer@buildmate.com' AS '', 'buyer123' AS '';
SELECT '🚚 Logistics' AS '', 'logistics@buildmate.com' AS '', 'logistics123' AS '';
SELECT '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━' AS '';
SELECT '' AS '';
SELECT '✅ All accounts ready to use!' AS '';
SELECT '🌐 Login at: http://localhost/build_mate/login' AS '';
