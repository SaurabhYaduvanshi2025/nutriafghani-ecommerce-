<?php
/**
 * Cart Management Script
 * Handles adding, updating, and removing items from cart
 */

require_once('config/db.php');
require_once('includes/customer-auth.php');

// Require customer login
require_customer_login();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

function ensure_ecommerce_tables_exist($conn)
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS `cart_items` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `orders` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `order_items` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            throw new Exception('Failed to ensure ecommerce tables: ' . $conn->error);
        }
    }
}

try {
    ensure_ecommerce_tables_exist($conn);
    $customerId = get_customer_id();
    
    // Debug logging
    error_log("Cart-process: Request method = " . $_SERVER['REQUEST_METHOD']);
    error_log("Cart-process: Customer ID = " . ($customerId ? $customerId : 'NULL'));
    error_log("Cart-process: Session customer_id = " . (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'NOT SET'));

    if (!$customerId) {
        throw new Exception('Customer login required');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                // Add item to cart
                $productId = intval($_POST['product_id'] ?? 0);
                $variantId = !empty($_POST['variant_id']) ? intval($_POST['variant_id']) : null;
                $quantity = intval($_POST['quantity'] ?? 1);

                if ($productId <= 0 || $quantity <= 0) {
                    throw new Exception('Invalid product or quantity');
                }

                // Check if product exists and is active
                $productQuery = "SELECT id, stock_quantity FROM products WHERE id = ? AND is_active = 1";
                $productStmt = $conn->prepare($productQuery);
                $productStmt->bind_param("i", $productId);
                $productStmt->execute();
                $productResult = $productStmt->get_result();

                if ($productResult->num_rows === 0) {
                    throw new Exception('Product not found');
                }

                $product = $productResult->fetch_assoc();

                // Check stock availability
                $availableStock = $product['stock_quantity'];
                if ($availableStock < $quantity) {
                    throw new Exception('Insufficient stock. Available: ' . $availableStock);
                }

                // Get price
                $price = 0;
                if ($variantId) {
                    // Get variant price
                    $variantQuery = "SELECT variant_discount_price, stock_quantity FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1";
                    $variantStmt = $conn->prepare($variantQuery);
                    $variantStmt->bind_param("ii", $variantId, $productId);
                    $variantStmt->execute();
                    $variantResult = $variantStmt->get_result();

                    if ($variantResult->num_rows === 0) {
                        throw new Exception('Product variant not found');
                    }

                    $variant = $variantResult->fetch_assoc();
                    $price = $variant['variant_discount_price'];
                    $availableStock = min($availableStock, $variant['stock_quantity']);
                } else {
                    // Get base product price
                    $priceQuery = "SELECT discount_price FROM products WHERE id = ?";
                    $priceStmt = $conn->prepare($priceQuery);
                    $priceStmt->bind_param("i", $productId);
                    $priceStmt->execute();
                    $priceResult = $priceStmt->get_result();
                    $priceData = $priceResult->fetch_assoc();
                    $price = $priceData['discount_price'];
                }

                if ($availableStock < $quantity) {
                    throw new Exception('Insufficient stock for selected variant. Available: ' . $availableStock);
                }

                // Check if item already exists in cart
                $checkQuery = "SELECT id, quantity FROM cart_items WHERE customer_id = ? AND product_id = ?";
                $params = [$customerId, $productId];
                $types = "ii";

                if ($variantId) {
                    $checkQuery .= " AND variant_id = ?";
                    $params[] = $variantId;
                    $types .= "i";
                } else {
                    $checkQuery .= " AND variant_id IS NULL";
                }

                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param($types, ...$params);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    // Update existing cart item
                    $existingItem = $checkResult->fetch_assoc();
                    $newQuantity = $existingItem['quantity'] + $quantity;

                    if ($availableStock < $newQuantity) {
                        throw new Exception('Insufficient stock. Available: ' . $availableStock . ', Requested: ' . $newQuantity);
                    }

                    $updateQuery = "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);
                    $updateStmt->execute();

                    $response['message'] = 'Cart updated successfully';
                } else {
                    // Insert new cart item
                    $insertQuery = "INSERT INTO cart_items (customer_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("iiiid", $customerId, $productId, $variantId, $quantity, $price);
                    $insertStmt->execute();

                    $response['message'] = 'Product added to cart successfully';
                }

                $response['success'] = true;
                break;

            case 'update':
                // Update cart item quantity
                $cartItemId = intval($_POST['cart_item_id'] ?? 0);
                $quantity = intval($_POST['quantity'] ?? 1);

                if ($cartItemId <= 0 || $quantity < 0) {
                    throw new Exception('Invalid cart item or quantity');
                }

                if ($quantity === 0) {
                    // Remove item from cart
                    $deleteQuery = "DELETE FROM cart_items WHERE id = ? AND customer_id = ?";
                    $deleteStmt = $conn->prepare($deleteQuery);
                    $deleteStmt->bind_param("ii", $cartItemId, $customerId);
                    $deleteStmt->execute();

                    $response['message'] = 'Item removed from cart';
                } else {
                    // Check stock availability
                    $stockQuery = "
                        SELECT ci.quantity, p.stock_quantity, pv.stock_quantity as variant_stock
                        FROM cart_items ci
                        LEFT JOIN products p ON ci.product_id = p.id
                        LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                        WHERE ci.id = ? AND ci.customer_id = ?
                    ";
                    $stockStmt = $conn->prepare($stockQuery);
                    $stockStmt->bind_param("ii", $cartItemId, $customerId);
                    $stockStmt->execute();
                    $stockResult = $stockStmt->get_result();

                    if ($stockResult->num_rows === 0) {
                        throw new Exception('Cart item not found');
                    }

                    $stockData = $stockResult->fetch_assoc();
                    $availableStock = $stockData['variant_stock'] ?? $stockData['stock_quantity'];

                    if ($availableStock < $quantity) {
                        throw new Exception('Insufficient stock. Available: ' . $availableStock);
                    }

                    // Update quantity
                    $updateQuery = "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ? AND customer_id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("iii", $quantity, $cartItemId, $customerId);
                    $updateStmt->execute();

                    $response['message'] = 'Cart updated successfully';
                }

                $response['success'] = true;
                break;

            case 'remove':
                // Remove item from cart
                $cartItemId = intval($_POST['cart_item_id'] ?? 0);

                $deleteQuery = "DELETE FROM cart_items WHERE id = ? AND customer_id = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("ii", $cartItemId, $customerId);
                $deleteStmt->execute();

                $response['success'] = true;
                $response['message'] = 'Item removed from cart';
                break;

            default:
                throw new Exception('Invalid action');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        error_log("Cart-process: Processing GET request for customer " . $customerId);
        // Get cart items
        $cartQuery = "
            SELECT
                ci.id,
                ci.quantity,
                ci.price,
                p.id as product_id,
                p.name as product_name,
                p.slug as product_slug,
                p.main_image,
                pv.id as variant_id,
                pv.weight_label,
                pv.weight_value
            FROM cart_items ci
            LEFT JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.customer_id = ?
            ORDER BY ci.created_at DESC
        ";

        $cartStmt = $conn->prepare($cartQuery);
        if (!$cartStmt) {
            error_log("Cart-process: Prepare failed: " . $conn->error);
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $cartStmt->bind_param("i", $customerId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        $cartItems = [];
        $totalAmount = 0;

        while ($item = $cartResult->fetch_assoc()) {
            $price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $itemTotal = $quantity * $price;
            $totalAmount += $itemTotal;

            $cartItems[] = [
                'id' => intval($item['id']),
                'product_id' => intval($item['product_id']),
                'product_name' => $item['product_name'],
                'product_slug' => $item['product_slug'],
                'main_image' => $item['main_image'],
                'variant_id' => intval($item['variant_id']),
                'weight_label' => $item['weight_label'],
                'weight_value' => $item['weight_value'],
                'quantity' => $quantity,
                'price' => $price,
                'total' => floatval($itemTotal)
            ];
        }

        error_log("Cart-process: Found " . count($cartItems) . " items, total: " . $totalAmount);

        $response = [
            'success' => true,
            'cart_items' => $cartItems,
            'total_amount' => floatval($totalAmount),
            'cart_count' => count($cartItems)
        ];
    }

    // Get cart count for all responses
    $countQuery = "SELECT COUNT(*) as count FROM cart_items WHERE customer_id = ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("i", $customerId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countData = $countResult->fetch_assoc();
    $response['cart_count'] = $countData['count'];

} catch (Exception $e) {
    error_log("Cart-process: Exception caught: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
