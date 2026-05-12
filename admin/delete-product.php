<?php
require_once('auth.php');
require_once('../config/db.php');

function product_list_url($limit, $search, $status = '')
{
    $params = ['limit' => (int) $limit];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($status !== '') {
        $params['status'] = $status;
    }

    return 'product-list.php?' . http_build_query($params);
}

function table_exists($conn, $tableName)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS table_count
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
    ");
    $stmt->bind_param('s', $tableName);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return isset($result['table_count']) && (int) $result['table_count'] > 0;
}

function delete_product_image($path)
{
    if (empty($path) || preg_match('/^https?:\/\//i', $path)) {
        return;
    }

    $imageFile = realpath(__DIR__ . '/../uploads/' . ltrim($path, '/'));
    $uploadsRoot = realpath(__DIR__ . '/../uploads');

    if ($imageFile && $uploadsRoot && strpos($imageFile, $uploadsRoot) === 0 && is_file($imageFile)) {
        unlink($imageFile);
    }
}

$allowedLimits = [10, 20, 30];
$redirectLimit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;
if (!in_array($redirectLimit, $allowedLimits, true)) {
    $redirectLimit = 10;
}

$redirectSearch = isset($_POST['search']) ? trim($_POST['search']) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'invalid'));
    exit();
}

$productId = (int) $_POST['product_id'];
if ($productId <= 0) {
    header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'invalid'));
    exit();
}

$transactionStarted = false;

try {
    $existingStmt = $conn->prepare("
        SELECT main_image, gallery_image_1, gallery_image_2, gallery_image_3
        FROM products
        WHERE id = ?
        LIMIT 1
    ");
    $existingStmt->bind_param('i', $productId);
    $existingStmt->execute();
    $product = $existingStmt->get_result()->fetch_assoc();
    $existingStmt->close();

    if (!$product) {
        header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'not_found'));
        exit();
    }

    $conn->begin_transaction();
    $transactionStarted = true;

    if (table_exists($conn, 'product_variants')) {
        $variantStmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $variantStmt->bind_param('i', $productId);
        $variantStmt->execute();
        $variantStmt->close();
    }

    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->bind_param('i', $productId);
    $deleteStmt->execute();
    $deletedRows = $deleteStmt->affected_rows;
    $deleteStmt->close();

    $verifyStmt = $conn->prepare("SELECT id FROM products WHERE id = ? LIMIT 1");
    $verifyStmt->bind_param('i', $productId);
    $verifyStmt->execute();
    $productStillExists = $verifyStmt->get_result()->num_rows > 0;
    $verifyStmt->close();

    if ($deletedRows <= 0 || $productStillExists) {
        $conn->rollback();
        $transactionStarted = false;
        header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'delete_error'));
        exit();
    }

    $conn->commit();
    $transactionStarted = false;

    foreach (['main_image', 'gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $imageField) {
        delete_product_image($product[$imageField] ?? '');
    }

    header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'deleted'));
    exit();
} catch (Exception $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    error_log('Product delete error: ' . $e->getMessage());
    header('Location: ' . product_list_url($redirectLimit, $redirectSearch, 'delete_error'));
    exit();
}
