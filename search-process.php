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
];

if ($query === '') {
    echo json_encode($response);
    exit;
}

$like = '%' . $query . '%';

$productStmt = $conn->prepare("
    SELECT p.name, p.slug, p.main_image, p.original_price, p.discount_price
    FROM products p
    WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)
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
        'category' => '',
        'url' => 'product-detail.php?slug=' . urlencode($product['slug']),
        'image' => search_image_path($product['main_image'], 'uploads/'),
        'price' => 'Rs. ' . number_format($price, 2),
    ];
}

echo json_encode($response);
