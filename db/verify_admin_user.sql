-- Verify and fix admin user
-- Run this in phpMyAdmin or via command line

USE ecommerce_2025A_griselda_owusu;

-- Check if admin user exists
SELECT id, name, email, role FROM users WHERE email = 'admin@buildmate.com' AND role = 'admin';

-- If admin doesn't exist or password is wrong, insert/update it
-- Password: Admin123
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('Admin User', 'admin@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE 
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    role = 'admin',
    name = 'Admin User';

-- Verify the admin user
SELECT id, name, email, role, 
       CASE 
           WHEN password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
           THEN 'Password hash matches' 
           ELSE 'Password hash does NOT match' 
       END as password_status
FROM users 
WHERE email = 'admin@buildmate.com' AND role = 'admin';

