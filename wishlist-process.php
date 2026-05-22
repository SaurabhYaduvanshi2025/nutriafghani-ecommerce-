<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

header('Content-Type: application/json');

function ensure_wishlist_table_exists($conn)
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS `wishlist_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_id` INT NOT NULL,
            `product_id` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_wishlist_item` (`customer_id`, `product_id`),
            INDEX `idx_wishlist_customer_id` (`customer_id`),
            INDEX `idx_wishlist_product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function wishlist_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function get_wishlist_items($conn, $customerId)
{
    $stmt = $conn->prepare("
        SELECT wi.id, p.id AS product_id, p.name, p.slug, p.main_image, p.gallery_image_1,
               p.original_price, p.discount_price
        FROM wishlist_items wi
        JOIN products p ON wi.product_id = p.id
        WHERE wi.customer_id = ? AND p.is_active = 1
        ORDER BY wi.created_at DESC
    ");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($item = $result->fetch_assoc()) {
        $price = (float) ($item['discount_price'] ?: $item['original_price']);
        $items[] = [
            'id' => (int) $item['id'],
            'product_id' => (int) $item['product_id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'image' => wishlist_image_path($item['main_image'] ?: $item['gallery_image_1']),
            'price' => $price,
        ];
    }
    $stmt->close();

    return $items;
}

try {
    ensure_wishlist_table_exists($conn);

    $customerId = get_customer_id();
    if (!$customerId) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Please login to use your wishlist.',
            'wishlist_count' => 0,
        ]);
        exit;
    }

    $action = $_POST['action'] ?? ($_GET['action'] ?? 'list');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            throw new Exception('Invalid product.');
        }

        $productStmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
        $productStmt->bind_param("i", $productId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $productStmt->close();

        if ($productResult->num_rows === 0) {
            throw new Exception('Product not found.');
        }

        $insertStmt = $conn->prepare("
            INSERT IGNORE INTO wishlist_items (customer_id, product_id)
            VALUES (?, ?)
        ");
        $insertStmt->bind_param("ii", $customerId, $productId);
        $insertStmt->execute();
        $insertStmt->close();

        $items = get_wishlist_items($conn, $customerId);
        echo json_encode([
            'success' => true,
            'message' => 'Product added to wishlist.',
            'wishlist_items' => $items,
            'wishlist_count' => count($items),
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'remove') {
        $wishlistItemId = (int) ($_POST['wishlist_item_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($wishlistItemId > 0) {
            $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE id = ? AND customer_id = ?");
            $deleteStmt->bind_param("ii", $wishlistItemId, $customerId);
        } elseif ($productId > 0) {
            $deleteStmt = $conn->prepare("DELETE FROM wishlist_items WHERE product_id = ? AND customer_id = ?");
            $deleteStmt->bind_param("ii", $productId, $customerId);
        } else {
            throw new Exception('Invalid wishlist item.');
        }

        $deleteStmt->execute();
        $deleteStmt->close();

        $items = get_wishlist_items($conn, $customerId);
        echo json_encode([
            'success' => true,
            'message' => 'Product removed from wishlist.',
            'wishlist_items' => $items,
            'wishlist_count' => count($items),
        ]);
        exit;
    }

    $items = get_wishlist_items($conn, $customerId);
    echo json_encode([
        'success' => true,
        'wishlist_items' => $items,
        'wishlist_count' => count($items),
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'wishlist_count' => 0,
    ]);
}
