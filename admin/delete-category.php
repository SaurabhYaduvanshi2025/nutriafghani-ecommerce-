<?php
require_once('auth.php');
require_once('../config/db.php');

function category_list_url($limit, $search, $status = '')
{
    $params = [
        'limit' => (int) $limit,
        't' => time(),
    ];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($status !== '') {
        $params['status'] = $status;
    }

    return 'category-list.php?' . http_build_query($params);
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

$allowedLimits = [10, 20, 30];
$redirectLimit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;
if (!in_array($redirectLimit, $allowedLimits, true)) {
    $redirectLimit = 10;
}

$redirectSearch = isset($_POST['search']) ? trim($_POST['search']) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['category_id'])) {
    header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'invalid'));
    exit();
}

$categoryId = (int) $_POST['category_id'];
if ($categoryId <= 0) {
    header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'invalid'));
    exit();
}

$transactionStarted = false;

try {
    $existingStmt = $conn->prepare("SELECT image FROM categories WHERE id = ? LIMIT 1");
    $existingStmt->bind_param('i', $categoryId);
    $existingStmt->execute();
    $category = $existingStmt->get_result()->fetch_assoc();
    $existingStmt->close();

    if (!$category) {
        header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'not_found'));
        exit();
    }

    $conn->begin_transaction();
    $transactionStarted = true;

    if (table_exists($conn, 'menu_items')) {
        $menuStmt = $conn->prepare("DELETE FROM menu_items WHERE category_id = ?");
        $menuStmt->bind_param('i', $categoryId);
        $menuStmt->execute();
        $menuStmt->close();
    }

    if (table_exists($conn, 'product_variants')) {
        $variantStmt = $conn->prepare("
            DELETE pv
            FROM product_variants pv
            INNER JOIN products p ON p.id = pv.product_id
            WHERE p.category_id = ?
        ");
        $variantStmt->bind_param('i', $categoryId);
        $variantStmt->execute();
        $variantStmt->close();
    }

    $productStmt = $conn->prepare("DELETE FROM products WHERE category_id = ?");
    $productStmt->bind_param('i', $categoryId);
    $productStmt->execute();
    $productStmt->close();

    $categoryStmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $categoryStmt->bind_param('i', $categoryId);
    $categoryStmt->execute();
    $deletedRows = $categoryStmt->affected_rows;
    $categoryStmt->close();

    $verifyStmt = $conn->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
    $verifyStmt->bind_param('i', $categoryId);
    $verifyStmt->execute();
    $categoryStillExists = $verifyStmt->get_result()->num_rows > 0;
    $verifyStmt->close();

    if ($deletedRows <= 0 || $categoryStillExists) {
        $conn->rollback();
        $transactionStarted = false;
        header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'delete_error'));
        exit();
    }

    $conn->commit();
    $transactionStarted = false;

    if (!empty($category['image']) && !preg_match('/^https?:\/\//i', $category['image'])) {
        $imageFile = __DIR__ . '/' . ltrim($category['image'], '/');
        if (is_file($imageFile)) {
            unlink($imageFile);
        }
    }

    header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'deleted'));
    exit();
} catch (Exception $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    error_log('Permanent category delete error: ' . $e->getMessage());
    header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'delete_error'));
    exit();
}
