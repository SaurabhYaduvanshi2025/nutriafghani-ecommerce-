<?php
require_once('config/db.php');

header('Content-Type: application/json');

function search_image_path($path, $base)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return $base . ltrim($path, '/');
}

$query = trim($_GET['q'] ?? '');
$response = [
    'products' => [],
    'categories' => [],
];

if ($query === '') {
    echo json_encode($response);
    exit;
}

$like = '%' . $query . '%';

$categoryStmt = $conn->prepare("
    SELECT c.name, c.slug, c.image, COUNT(DISTINCT p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON (p.category_id = c.id OR p.category_slug = c.slug) AND p.is_active = 1
    WHERE c.is_active = 1 AND c.name LIKE ?
    GROUP BY c.id, c.name, c.slug, c.image
    ORDER BY c.name ASC
    LIMIT 6
");
$categoryStmt->bind_param('s', $like);
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();

while ($category = $categoryResult->fetch_assoc()) {
    $response['categories'][] = [
        'name' => $category['name'],
        'url' => 'shop.php?category=' . urlencode($category['slug']),
        'image' => search_image_path($category['image'], 'admin/'),
        'count' => (int) $category['product_count'],
    ];
}

$productStmt = $conn->prepare("
    SELECT p.name, p.slug, p.main_image, p.original_price, p.discount_price, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.is_active = 1 AND (p.name LIKE ? OR c.name LIKE ?)
    ORDER BY p.created_at DESC
    LIMIT 8
");
$productStmt->bind_param('ss', $like, $like);
$productStmt->execute();
$productResult = $productStmt->get_result();

while ($product = $productResult->fetch_assoc()) {
    $price = (float) ($product['discount_price'] ?: $product['original_price']);
    $response['products'][] = [
        'name' => $product['name'],
        'category' => $product['category_name'] ?? '',
        'url' => 'product-detail.php?slug=' . urlencode($product['slug']),
        'image' => search_image_path($product['main_image'], 'uploads/'),
        'price' => 'Rs. ' . number_format($price, 2),
    ];
}

echo json_encode($response);
