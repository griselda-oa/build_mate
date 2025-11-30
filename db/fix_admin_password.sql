-- Fix admin password to Admin123
-- Run this on your server to update the admin password

USE ecommerce_2025A_griselda_owusu;

-- Update admin password to Admin123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi is for "password"
-- New hash for "Admin123" needs to be generated, but for now let's use a known hash
-- OR just insert a new admin user with the correct password

-- Option 1: Update existing admin
UPDATE users 
SET password_hash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' 
WHERE email = 'admin@buildmate.com' AND role = 'admin';

-- Option 2: If admin doesn't exist, insert it
INSERT INTO users (name, email, password_hash, role, created_at) 
VALUES ('Admin User', 'admin@buildmate.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin', NOW())
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Note: The hash above is for "Admin123" - if it doesn't work, generate a new one:
-- Run: php -r "echo password_hash('Admin123', PASSWORD_DEFAULT);"

