-- Insert Default Users for Build Mate
-- Run this in phpMyAdmin or via command line if users are missing

USE ecommerce_2025A_griselda_owusu;

-- Admin user (password: Admin123)
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('Admin User', 'admin@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE name=name;

-- Buyer users (password: password)
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('John Mensah', 'buyer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW()),
('Ama Osei', 'buyer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW())
ON DUPLICATE KEY UPDATE name=name;

-- Supplier users (password: password)
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('Kwame Asante', 'supplier@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', NOW()),
('Premium Materials Ltd', 'supplier2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', NOW()),
('Ghana Build Supplies', 'supplier3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', NOW())
ON DUPLICATE KEY UPDATE name=name;

-- Logistics users (password: password)
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('Fast Logistics', 'logistics@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'logistics', NOW())
ON DUPLICATE KEY UPDATE name=name;

