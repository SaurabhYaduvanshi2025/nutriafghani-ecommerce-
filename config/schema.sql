-- =====================================================
-- NUTRIAFGHAN E-COMMERCE DATABASE SCHEMA
-- =====================================================

-- =====================================================
-- 1. CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT NULL,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `image` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_menu_id` (`menu_id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. PRODUCTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `category_slug` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `short_description` VARCHAR(500),
    `original_price` DECIMAL(10, 2),
    `discount_price` DECIMAL(10, 2),
    `discount_percentage` INT DEFAULT 0,
    `main_image` VARCHAR(255),
    `gallery_image_1` VARCHAR(255),
    `gallery_image_2` VARCHAR(255),
    `gallery_image_3` VARCHAR(255),
    `sku` VARCHAR(100) UNIQUE,
    `stock_quantity` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    INDEX `idx_category_slug` (`category_slug`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PRODUCT VARIANTS TABLE (Weight/Price Options)
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `weight_label` VARCHAR(50) NOT NULL,
    `weight_value` VARCHAR(50) NOT NULL,
    `weight_grams` INT,
    `variant_price` DECIMAL(10, 2) NOT NULL,
    `variant_discount_price` DECIMAL(10, 2),
    `variant_sku` VARCHAR(100) UNIQUE,
    `stock_quantity` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_weight_label` (`weight_label`),
    UNIQUE KEY `unique_variant` (`product_id`, `weight_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. HOMEPAGE MENU ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT NULL,
    `category_id` INT NULL,
    `label` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) DEFAULT '#',
    `icon_class` VARCHAR(100) DEFAULT NULL,
    `menu_type` VARCHAR(50) DEFAULT 'homepage',
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_menu_type` (`menu_type`),
    INDEX `idx_sort_order` (`sort_order`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. CUSTOMERS TABLE (User Accounts)
-- =====================================================
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20),
    `password_hash` VARCHAR(255) NOT NULL,
    `address` TEXT,
    `city` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_email` (`email`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. CART ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `variant_id` INT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_variant_id` (`variant_id`),
    UNIQUE KEY `unique_cart_item` (`customer_id`, `product_id`, `variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ORDERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `shipping_address` TEXT,
    `payment_method` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `variant_id` INT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. SAMPLE DATA - CATEGORIES
-- =====================================================
INSERT INTO `categories` (`name`, `slug`, `description`, `is_active`) VALUES
('Spices', 'spices', 'Premium Afghan spices and seasonings', 1),
('Dry Fruits', 'dry-fruits', 'High-quality dried fruits and nuts', 1),
('Grains', 'grains', 'Traditional Afghan grains and pulses', 1),
('Herbs', 'herbs', 'Medicinal and culinary herbs', 1),
('Oils', 'oils', 'Pure cooking and essential oils', 1)
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

-- =====================================================
-- 6. SAMPLE DATA - PRODUCTS
-- =====================================================
INSERT INTO `products` (
    `category_id`, 
    `category_slug`, 
    `name`, 
    `slug`, 
    `description`, 
    `short_description`,
    `original_price`, 
    `discount_price`, 
    `discount_percentage`,
    `main_image`,
    `gallery_image_1`,
    `gallery_image_2`,
    `gallery_image_3`,
    `sku`, 
    `stock_quantity`,
    `is_featured`,
    `is_active`
) VALUES (
    1,
    'spices',
    'Premium Saffron',
    'premium-saffron',
    'Premium Grade A Kashmiri Saffron with excellent color and aroma',
    'Authentic Kashmiri Saffron - Premium Grade A',
    5000,
    4200,
    16,
    NULL,
    NULL,
    NULL,
    NULL,
    'SAF-001',
    100,
    1,
    1
)
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

-- =====================================================
-- 7. SAMPLE DATA - PRODUCT VARIANTS
-- =====================================================
INSERT INTO `product_variants` (
    `product_id`,
    `weight_label`,
    `weight_value`,
    `weight_grams`,
    `variant_price`,
    `variant_discount_price`,
    `variant_sku`,
    `stock_quantity`,
    `is_active`
) SELECT 
    p.id,
    w.weight_label,
    w.weight_value,
    w.weight_grams,
    ROUND(p.original_price * w.price_multiplier, 2),
    ROUND(p.discount_price * w.price_multiplier, 2),
    CONCAT(p.sku, '-', w.weight_code),
    100,
    1
FROM (
    SELECT 1 as weight_label, '250g' as weight_value, 250 as weight_grams, 0.5 as price_multiplier, 'A' as weight_code
    UNION ALL
    SELECT 2, '500g', 500, 1, 'B'
    UNION ALL
    SELECT 3, '1kg', 1000, 2, 'C'
) as w,
(SELECT id, original_price, discount_price, sku FROM products WHERE slug = 'premium-saffron') as p
ON DUPLICATE KEY UPDATE `weight_label` = VALUES(`weight_label`);
