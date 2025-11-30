-- Build Mate Ghana Ltd - Complete Database Setup
-- Import this file into phpMyAdmin to create the entire database
-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;


-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;


-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;


-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;


-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;


-- This includes: tables, structure, and realistic seed data
-- NOTE: Database must already exist - this file does NOT create it
-- IMPORTANT: Select your database in phpMyAdmin before importing this file

-- ============================================
-- TABLES STRUCTURE
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'supplier', 'logistics', 'admin') NOT NULL DEFAULT 'buyer',
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_registration VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_badge TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_kyc_status (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    stock INT UNSIGNED DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_category_id (category_id),
    INDEX idx_slug (slug),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) DEFAULT 'GHS',
    status ENUM('pending', 'paid', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries table
CREATE TABLE IF NOT EXISTS deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    logistics_user_id INT UNSIGNED,
    tracking_number VARCHAR(100) UNIQUE,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    estimated_delivery_date DATE,
    actual_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (logistics_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS kyc_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NOT NULL,
    document_type ENUM('business_registration', 'tax_certificate', 'id_card', 'bank_statement', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (for security logging)
-- Must be created after users table due to foreign key
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    ua TEXT DEFAULT NULL,
    meta LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

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

-- Suppliers
INSERT INTO suppliers (user_id, business_name, kyc_status, verified_badge, created_at) VALUES
(3, 'Asante Building Materials', 'approved', 1, NOW()),
(4, 'Premium Materials Ltd', 'approved', 1, NOW()),
(5, 'Ghana Build Supplies', 'approved', 1, NOW())
ON DUPLICATE KEY UPDATE business_name=business_name;

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products'),
('Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel'),
('Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks'),
('Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories'),
('Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components'),
('Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories'),
('Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials'),
('Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials')
ON DUPLICATE KEY UPDATE name=name;

-- Products (74 realistic products with images)
INSERT INTO products (supplier_id, category_id, name, slug, description, price_cents, currency, stock, verified, image_url) VALUES
-- Cement & Mortar (8 products)
(1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Steel & Rebar (10 products)
(1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Blocks & Bricks (10 products)
(1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Roofing Materials (10 products)
(1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Electrical Supplies (10 products)
(1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Plumbing Materials (10 products)
(1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Paint & Finishes (8 products)
(1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),

-- Tiles & Flooring (8 products)
(1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500'),
(3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500')
ON DUPLICATE KEY UPDATE name=name;

