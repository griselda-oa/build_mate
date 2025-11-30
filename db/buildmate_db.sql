-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 26, 2025 at 10:25 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buildmate_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `ua` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Cement & Mortar', 'cement-mortar', 'Portland cement, ready-mix concrete, and mortar products', '2025-11-20 18:48:46'),
(2, 'Steel & Rebar', 'steel-rebar', 'Reinforcement bars, steel rods, and structural steel', '2025-11-20 18:48:46'),
(3, 'Blocks & Bricks', 'blocks-bricks', 'Concrete blocks, clay bricks, and building blocks', '2025-11-20 18:48:46'),
(4, 'Roofing Materials', 'roofing-materials', 'Roofing sheets, tiles, and roofing accessories', '2025-11-20 18:48:46'),
(5, 'Electrical Supplies', 'electrical-supplies', 'Wires, cables, switches, and electrical components', '2025-11-20 18:48:46'),
(6, 'Plumbing Materials', 'plumbing-materials', 'Pipes, fittings, fixtures, and plumbing accessories', '2025-11-20 18:48:46'),
(7, 'Paint & Finishes', 'paint-finishes', 'Paints, varnishes, and finishing materials', '2025-11-20 18:48:46'),
(8, 'Tiles & Flooring', 'tiles-flooring', 'Ceramic tiles, floor tiles, and flooring materials', '2025-11-20 18:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `logistics_user_id` int(10) UNSIGNED DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','assigned','picked_up','in_transit','delivered','failed') DEFAULT 'pending',
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_documents`
--

CREATE TABLE `kyc_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `document_type` enum('business_registration','tax_certificate','id_card','bank_statement','other') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `buyer_id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `total_cents` int(10) UNSIGNED NOT NULL,
  `currency` char(3) DEFAULT 'GHS',
  `status` enum('pending','paid','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `price_cents` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_cents` int(10) UNSIGNED NOT NULL,
  `currency` char(3) DEFAULT 'GHS',
  `stock` int(10) UNSIGNED DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `category_id`, `name`, `slug`, `description`, `price_cents`, `currency`, `stock`, `verified`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Dangote Cement 50kg', 'dangote-cement-50kg', 'Premium quality Portland cement, 50kg bag. Suitable for all construction purposes.', 6500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(2, 1, 1, 'GHACEM Cement 50kg', 'ghacem-cement-50kg', 'High-grade cement for construction projects. 50kg bag.', 6800, 'GHS', 450, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(3, 2, 1, 'Ready-Mix Concrete M20', 'ready-mix-concrete-m20', 'Ready-to-use concrete mix, M20 grade. 1 cubic meter.', 45000, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(4, 2, 1, 'Cement Mortar Mix', 'cement-mortar-mix', 'Pre-mixed cement mortar for bricklaying and plastering. 25kg bag.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(5, 3, 1, 'White Cement 25kg', 'white-cement-25kg', 'Premium white cement for decorative applications. 25kg bag.', 8500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(6, 1, 1, 'Rapid Hardening Cement', 'rapid-hardening-cement', 'Fast-setting cement for quick construction. 50kg bag.', 7200, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(7, 2, 1, 'Sulfate Resistant Cement', 'sulfate-resistant-cement', 'Special cement resistant to sulfate attack. 50kg bag.', 7500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(8, 3, 1, 'Portland Pozzolana Cement', 'portland-pozzolana-cement', 'PPC cement with pozzolanic properties. 50kg bag.', 6900, 'GHS', 250, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(9, 1, 2, 'Y12 Rebar (12mm)', 'y12-rebar-12mm', 'High-tensile reinforcement bar, 12mm diameter. 12 meters length.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(10, 1, 2, 'Y10 Rebar (10mm)', 'y10-rebar-10mm', 'Reinforcement bar, 10mm diameter. 12 meters length.', 3200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(11, 2, 2, 'Y16 Rebar (16mm)', 'y16-rebar-16mm', 'Heavy-duty reinforcement bar, 16mm diameter. 12 meters length.', 6800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(12, 2, 2, 'Y20 Rebar (20mm)', 'y20-rebar-20mm', 'Extra heavy reinforcement bar, 20mm diameter. 12 meters length.', 10500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(13, 3, 2, 'Steel Mesh A142', 'steel-mesh-a142', 'Welded steel mesh for concrete reinforcement. 2.4m x 4.8m sheet.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(14, 1, 2, 'Binding Wire 16 Gauge', 'binding-wire-16-gauge', 'Steel binding wire for tying rebar. 50kg coil.', 2800, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(15, 2, 2, 'Steel Plate 6mm', 'steel-plate-6mm', 'Mild steel plate, 6mm thickness. 1m x 2m sheet.', 12500, 'GHS', 80, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(16, 3, 2, 'Angle Iron 50x50x5mm', 'angle-iron-50x50x5mm', 'Structural angle iron. 6 meters length.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(17, 1, 2, 'I-Beam 150mm', 'i-beam-150mm', 'Structural I-beam, 150mm depth. 6 meters length.', 18000, 'GHS', 60, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(18, 2, 2, 'Steel Channel 100mm', 'steel-channel-100mm', 'C-channel steel section. 6 meters length.', 9500, 'GHS', 90, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(19, 1, 3, 'Hollow Block 6 Inch', 'hollow-block-6-inch', 'Standard hollow concrete block, 6 inches. High quality.', 350, 'GHS', 5000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(20, 1, 3, 'Hollow Block 4 Inch', 'hollow-block-4-inch', 'Hollow concrete block, 4 inches. For partition walls.', 280, 'GHS', 6000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(21, 2, 3, 'Solid Block 6 Inch', 'solid-block-6-inch', 'Solid concrete block, 6 inches. For load-bearing walls.', 420, 'GHS', 4000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(22, 2, 3, 'Clay Brick Standard', 'clay-brick-standard', 'Fired clay brick, standard size. 1000 pieces per pallet.', 850, 'GHS', 3000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(23, 3, 3, 'Interlocking Block', 'interlocking-block', 'Interlocking concrete block for walls. No mortar needed.', 450, 'GHS', 2500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(24, 1, 3, 'Decorative Block', 'decorative-block', 'Decorative concrete block for facades. Various patterns.', 550, 'GHS', 1800, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(25, 2, 3, 'Paving Block 60mm', 'paving-block-60mm', 'Concrete paving block, 60mm thick. For driveways and walkways.', 380, 'GHS', 3500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(26, 3, 3, 'Kerbs Stone', 'kerbs-stone', 'Concrete kerb stone for road edges. 1 meter length.', 1200, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(27, 1, 3, 'Aerated Block', 'aerated-block', 'Lightweight aerated concrete block. Excellent insulation.', 680, 'GHS', 1200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(28, 2, 3, 'Brick Veneer', 'brick-veneer', 'Thin brick veneer for cladding. Easy installation.', 1200, 'GHS', 1500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(29, 1, 4, 'Long Span Roofing Sheet', 'long-span-roofing-sheet', 'Galvanized long span roofing sheet. 0.55mm thickness.', 8500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(30, 1, 4, 'Corrugated Roofing Sheet', 'corrugated-roofing-sheet', 'Standard corrugated roofing sheet. 0.45mm thickness.', 7200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(31, 2, 4, 'Aluminum Roofing Sheet', 'aluminum-roofing-sheet', 'Lightweight aluminum roofing sheet. Corrosion resistant.', 12500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(32, 2, 4, 'Roofing Ridge Cap', 'roofing-ridge-cap', 'Ridge cap for roof peak. 3 meters length.', 1800, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(33, 3, 4, 'Roofing Nails 4 Inch', 'roofing-nails-4-inch', 'Galvanized roofing nails. 1kg pack.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(34, 1, 4, 'Roofing Felt Underlay', 'roofing-felt-underlay', 'Waterproof roofing felt underlay. 10 meters roll.', 3500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(35, 2, 4, 'Gutter System PVC', 'gutter-system-pvc', 'Complete PVC gutter system. 3 meters length.', 2800, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(36, 3, 4, 'Roof Ventilator', 'roof-ventilator', 'Roof ventilator for air circulation. Weatherproof.', 4500, 'GHS', 120, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(37, 1, 4, 'Roofing Sealant', 'roofing-sealant', 'Silicone roofing sealant. 310ml tube.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(38, 2, 4, 'Roofing Insulation', 'roofing-insulation', 'Thermal insulation for roofs. 50mm thickness, 1m x 2m sheet.', 4200, 'GHS', 180, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(39, 1, 5, 'Copper Wire 2.5mm²', 'copper-wire-2.5mm', 'Single core copper wire, 2.5mm². 100 meters roll.', 8500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(40, 1, 5, 'PVC Conduit Pipe 20mm', 'pvc-conduit-pipe-20mm', 'PVC electrical conduit pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(41, 2, 5, 'Electrical Switch 1-Gang', 'electrical-switch-1-gang', 'Single gang electrical switch. White color.', 280, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(42, 2, 5, 'Socket Outlet 13A', 'socket-outlet-13a', 'UK standard 13A socket outlet. White.', 450, 'GHS', 900, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(43, 3, 5, 'MCB Circuit Breaker 20A', 'mcb-circuit-breaker-20a', 'Miniature circuit breaker, 20A rating. Single pole.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(44, 1, 5, 'LED Bulb 12W', 'led-bulb-12w', 'Energy efficient LED bulb, 12W equivalent to 60W. Warm white.', 850, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(45, 2, 5, 'Electrical Panel Box', 'electrical-panel-box', 'Main electrical distribution panel box. 12-way.', 12500, 'GHS', 80, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(46, 3, 5, 'Cable Ties 200mm', 'cable-ties-200mm', 'Nylon cable ties for wire management. Pack of 100.', 280, 'GHS', 1500, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(47, 1, 5, 'Earth Rod 2.5m', 'earth-rod-2.5m', 'Copper-clad earth rod for grounding. 2.5 meters length.', 3500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(48, 2, 5, 'Wire Stripper Tool', 'wire-stripper-tool', 'Professional wire stripping tool. Multi-size.', 1200, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(49, 1, 6, 'PVC Pipe 20mm', 'pvc-pipe-20mm', 'PVC water pipe, 20mm diameter. 3 meters length.', 450, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(50, 1, 6, 'PVC Elbow Fitting', 'pvc-elbow-fitting', 'PVC elbow fitting, 20mm. 90-degree angle.', 180, 'GHS', 2000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(51, 2, 6, 'Galvanized Pipe 1/2 Inch', 'galvanized-pipe-half-inch', 'Galvanized steel water pipe, 1/2 inch. 6 meters length.', 2800, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(52, 2, 6, 'Tap Connector', 'tap-connector', 'Flexible tap connector. 1/2 inch BSP.', 850, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(53, 3, 6, 'Ball Valve 1/2 Inch', 'ball-valve-half-inch', 'Brass ball valve, 1/2 inch. Full port.', 1800, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(54, 1, 6, 'Toilet Flush Tank', 'toilet-flush-tank', 'Complete toilet flush tank with mechanism. White.', 4500, 'GHS', 150, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(55, 2, 6, 'Shower Mixer Tap', 'shower-mixer-tap', 'Wall-mounted shower mixer tap. Chrome finish.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(56, 3, 6, 'Pipe Wrench 12 Inch', 'pipe-wrench-12-inch', 'Adjustable pipe wrench. 12 inch capacity.', 2800, 'GHS', 200, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(57, 1, 6, 'PTFE Tape', 'ptfe-tape', 'Thread seal tape for pipe fittings. 12mm width, 10 meters.', 450, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(58, 2, 6, 'Pipe Insulation 22mm', 'pipe-insulation-22mm', 'Foam pipe insulation, 22mm diameter. 2 meters length.', 850, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(59, 1, 7, 'Emulsion Paint 20L', 'emulsion-paint-20l', 'Premium interior emulsion paint. White, 20 liters.', 8500, 'GHS', 100, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(60, 1, 7, 'Gloss Paint 5L', 'gloss-paint-5l', 'High-gloss paint for wood and metal. White, 5 liters.', 4500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(61, 2, 7, 'Primer Paint 5L', 'primer-paint-5l', 'Multi-surface primer paint. 5 liters.', 3500, 'GHS', 250, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(62, 2, 7, 'Paint Brush Set', 'paint-brush-set', 'Professional paint brush set. 5 pieces various sizes.', 850, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(63, 3, 7, 'Paint Roller Kit', 'paint-roller-kit', 'Complete paint roller kit with tray. Professional quality.', 1200, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(64, 1, 7, 'Varnish Clear 5L', 'varnish-clear-5l', 'Clear wood varnish. 5 liters.', 5500, 'GHS', 150, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(65, 2, 7, 'Paint Thinner 5L', 'paint-thinner-5l', 'Paint thinner and cleaner. 5 liters.', 2800, 'GHS', 180, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(66, 3, 7, 'Wall Putty 20kg', 'wall-putty-20kg', 'Wall putty for smooth finish. 20kg bag.', 3500, 'GHS', 200, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(67, 1, 8, 'Ceramic Floor Tile 60x60cm', 'ceramic-floor-tile-60x60cm', 'Premium ceramic floor tile, 60x60cm. Various colors available.', 8500, 'GHS', 500, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(68, 1, 8, 'Wall Tile 30x60cm', 'wall-tile-30x60cm', 'Ceramic wall tile, 30x60cm. Glossy finish.', 4500, 'GHS', 800, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(69, 2, 8, 'Tile Adhesive 20kg', 'tile-adhesive-20kg', 'Premium tile adhesive. 20kg bag.', 2800, 'GHS', 600, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(70, 2, 8, 'Tile Grout 5kg', 'tile-grout-5kg', 'Tile grout for joints. White, 5kg bag.', 1200, 'GHS', 1000, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(71, 3, 8, 'Vinyl Flooring Roll', 'vinyl-flooring-roll', 'Self-adhesive vinyl flooring. 2 meters width, per meter.', 3500, 'GHS', 300, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(72, 1, 8, 'Laminate Flooring', 'laminate-flooring', 'Click-lock laminate flooring. Per square meter.', 4500, 'GHS', 400, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(73, 2, 8, 'Tile Spacers 2mm', 'tile-spacers-2mm', 'Plastic tile spacers. Pack of 100 pieces.', 280, 'GHS', 2000, 0, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(74, 3, 8, 'Tile Cutter Manual', 'tile-cutter-manual', 'Manual tile cutter for straight cuts. Up to 60cm width.', 8500, 'GHS', 50, 1, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=500', '2025-11-20 18:48:46', '2025-11-20 18:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_registration` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `kyc_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `verified_badge` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `user_id`, `business_name`, `business_registration`, `phone`, `address`, `kyc_status`, `verified_badge`, `created_at`, `updated_at`) VALUES
(1, 3, 'Asante Building Materials', NULL, NULL, NULL, 'approved', 1, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(2, 4, 'Premium Materials Ltd', NULL, NULL, NULL, 'approved', 1, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(3, 5, 'Ghana Build Supplies', NULL, NULL, NULL, 'approved', 1, '2025-11-20 18:48:46', '2025-11-20 18:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('buyer','supplier','logistics','admin') NOT NULL DEFAULT 'buyer',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@buildmate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(2, 'John Mensah', 'buyer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(3, 'Ama Osei', 'buyer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(4, 'Kwame Asante', 'supplier@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(5, 'Premium Materials Ltd', 'supplier2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(6, 'Ghana Build Supplies', 'supplier3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(7, 'Fast Logistics', 'logistics@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'logistics', 0, '2025-11-20 18:48:46', '2025-11-20 18:48:46'),
(8, 'GRISELDA', 'griselda.owusu@ashesi.edu.gh', '$2y$12$ywtKPQMaETUHAE778FY21uhCyLDewJPDFF4ACbywkaYmabBYu.roq', 'buyer', 0, '2025-11-24 22:40:25', '2025-11-24 22:40:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `logistics_user_id` (`logistics_user_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_tracking_number` (`tracking_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_verified` (`verified`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_kyc_status` (`kyc_status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`logistics_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD CONSTRAINT `kyc_documents_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
