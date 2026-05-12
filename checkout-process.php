<?php
/**
 * Checkout Processing Script
 * Handles order creation and stock reduction
 */

require_once('config/db.php');
require_once('includes/customer-auth.php');

// Require customer login
require_customer_login();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'order_id' => null,
    'order_number' => null
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $customerId = get_customer_id();
    if (!$customerId) {
        throw new Exception('Customer login required');
    }

    // Get cart items
    $cartQuery = "
        SELECT
            ci.id,
            ci.product_id,
            ci.variant_id,
            ci.quantity,
            ci.price,
            p.stock_quantity as product_stock,
            pv.stock_quantity as variant_stock,
            p.name as product_name
        FROM cart_items ci
        LEFT JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_variants pv ON ci.variant_id = pv.id
        WHERE ci.customer_id = ?
        ORDER BY ci.created_at ASC
    ";

    $cartStmt = $conn->prepare($cartQuery);
    $cartStmt->bind_param("i", $customerId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    if ($cartResult->num_rows === 0) {
        throw new Exception('Your cart is empty');
    }

    $cartItems = [];
    $totalAmount = 0;
    $hasStockIssues = false;

    while ($item = $cartResult->fetch_assoc()) {
        $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['product_stock'];

        if ($availableStock < $item['quantity']) {
            $hasStockIssues = true;
            $response['message'] = "Insufficient stock for {$item['product_name']}. Available: {$availableStock}, Requested: {$item['quantity']}";
            break;
        }

        $itemTotal = $item['quantity'] * $item['price'];
        $totalAmount += $itemTotal;

        $cartItems[] = $item;
    }

    if ($hasStockIssues) {
        $response['success'] = false;
        echo json_encode($response);
        exit;
    }

    // Get customer info
    $customerQuery = "SELECT first_name, last_name, email, phone, address, city, postal_code FROM customers WHERE id = ?";
    $customerStmt = $conn->prepare($customerQuery);
    $customerStmt->bind_param("i", $customerId);
    $customerStmt->execute();
    $customerResult = $customerStmt->get_result();
    $customer = $customerResult->fetch_assoc();

    // Get form data
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cod');

    if (empty($shippingAddress)) {
        $shippingAddress = trim($customer['address'] . ', ' . $customer['city'] . ', ' . $customer['postal_code']);
    }

    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad($customerId, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order
        $orderQuery = "
            INSERT INTO orders (
                customer_id,
                order_number,
                total_amount,
                status,
                shipping_address,
                payment_method
            ) VALUES (?, ?, ?, 'pending', ?, ?)
        ";

        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->bind_param("isdss", $customerId, $orderNumber, $totalAmount, $shippingAddress, $paymentMethod);
        $orderStmt->execute();
        $orderId = $conn->insert_id;

        // Insert order items and reduce stock
        $orderItemQuery = "
            INSERT INTO order_items (
                order_id,
                product_id,
                variant_id,
                quantity,
                price
            ) VALUES (?, ?, ?, ?, ?)
        ";

        $orderItemStmt = $conn->prepare($orderItemQuery);

        foreach ($cartItems as $item) {
            // Insert order item
            $orderItemStmt->bind_param("iiidi", $orderId, $item['product_id'], $item['variant_id'], $item['quantity'], $item['price']);
            $orderItemStmt->execute();

            // Reduce stock
            if ($item['variant_id']) {
                // Reduce variant stock
                $stockQuery = "UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $stockStmt = $conn->prepare($stockQuery);
                $stockStmt->bind_param("ii", $item['quantity'], $item['variant_id']);
                $stockStmt->execute();
            } else {
                // Reduce product stock
                $stockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $stockStmt = $conn->prepare($stockQuery);
                $stockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stockStmt->execute();
            }
        }

        // Clear cart
        $clearCartQuery = "DELETE FROM cart_items WHERE customer_id = ?";
        $clearCartStmt = $conn->prepare($clearCartQuery);
        $clearCartStmt->bind_param("i", $customerId);
        $clearCartStmt->execute();

        // Commit transaction
        $conn->commit();

        $response['success'] = true;
        $response['message'] = 'Order placed successfully!';
        $response['order_id'] = $orderId;
        $response['order_number'] = $orderNumber;

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>