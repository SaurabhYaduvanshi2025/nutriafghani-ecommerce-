<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once('../config/db.php');
require_once('auth.php');

$response = [
    'success' => false,
    'message' => '',
];

function uploadProductImageOptional($fileInputName)
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileInputName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error uploading ' . $fileInputName);
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception($fileInputName . ' size exceeds 5MB');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        throw new Exception($fileInputName . ' must be a JPG, PNG, or WebP image');
    }

    $uploadDir = '../uploads/products/' . date('Y/m/');
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception('Failed to create upload directory');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save ' . $fileInputName);
    }

    return 'products/' . date('Y/m/') . $filename;
}

try {
    $action = isset($_GET['action']) ? trim($_GET['action']) : '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action !== 'update') {
        throw new Exception('Invalid request');
    }

    $productId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($productId <= 0) {
        throw new Exception('Invalid product selected');
    }

    $existingStmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $existingStmt->bind_param('i', $productId);
    $existingStmt->execute();
    $existingProduct = $existingStmt->get_result()->fetch_assoc();
    $existingStmt->close();

    if (!$existingProduct) {
        throw new Exception('Product not found');
    }

    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $shortDescription = isset($_POST['short_description']) ? trim($_POST['short_description']) : '';
    $originalPrice = isset($_POST['original_price']) ? (float) $_POST['original_price'] : 0;
    $discountPrice = isset($_POST['discount_price']) ? (float) $_POST['discount_price'] : 0;
    $sku = isset($_POST['sku']) ? trim($_POST['sku']) : '';
    $sku = $sku === '' ? null : $sku;
    $stockQuantity = isset($_POST['stock_quantity']) ? (int) $_POST['stock_quantity'] : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) && (int) $_POST['is_active'] === 0 ? 0 : 1;

    if ($productName === '' || strlen($productName) > 255) {
        throw new Exception('Product name is required and must be under 255 characters');
    }

    if ($categoryId <= 0) {
        throw new Exception('Invalid category selected');
    }

    $categoryStmt = $conn->prepare("SELECT id, slug FROM categories WHERE id = ? LIMIT 1");
    $categoryStmt->bind_param('i', $categoryId);
    $categoryStmt->execute();
    $category = $categoryStmt->get_result()->fetch_assoc();
    $categoryStmt->close();

    if (!$category) {
        throw new Exception('Selected category does not exist');
    }

    if ($description === '' || strlen($description) > 1000) {
        throw new Exception('Description is required and must be under 1000 characters');
    }

    $shortDescription = substr($shortDescription, 0, 500);

    if ($originalPrice <= 0 || $discountPrice <= 0 || $discountPrice >= $originalPrice) {
        throw new Exception('Prices are invalid. Discount price must be less than original price.');
    }

    if ($stockQuantity < 0) {
        throw new Exception('Stock quantity must be 0 or more');
    }

    if ($sku !== null) {
        $skuStmt = $conn->prepare("SELECT id FROM products WHERE sku = ? AND id != ? LIMIT 1");
        $skuStmt->bind_param('si', $sku, $productId);
        $skuStmt->execute();
        if ($skuStmt->get_result()->num_rows > 0) {
            throw new Exception('SKU already exists. Please use a unique SKU');
        }
        $skuStmt->close();
    }

    $mainImage = uploadProductImageOptional('main_image') ?: $existingProduct['main_image'];
    $galleryImage1 = uploadProductImageOptional('gallery_image_1') ?: $existingProduct['gallery_image_1'];
    $galleryImage2 = uploadProductImageOptional('gallery_image_2') ?: $existingProduct['gallery_image_2'];
    $galleryImage3 = uploadProductImageOptional('gallery_image_3') ?: $existingProduct['gallery_image_3'];
    $discountPercentage = round((($originalPrice - $discountPrice) / $originalPrice) * 100);

    $selectedWeight = isset($_POST['selected_weight']) ? trim($_POST['selected_weight']) : '';
    $weightGrams = isset($_POST['weight_grams']) ? (int) $_POST['weight_grams'] : 0;
    $weightMultiplier = isset($_POST['weight_multiplier']) ? (float) $_POST['weight_multiplier'] : 1;

    $conn->begin_transaction();

    $updateStmt = $conn->prepare("
        UPDATE products
        SET category_id = ?,
            category_slug = ?,
            name = ?,
            description = ?,
            short_description = ?,
            original_price = ?,
            discount_price = ?,
            discount_percentage = ?,
            main_image = ?,
            gallery_image_1 = ?,
            gallery_image_2 = ?,
            gallery_image_3 = ?,
            sku = ?,
            stock_quantity = ?,
            is_featured = ?,
            is_active = ?
        WHERE id = ?
    ");
    $updateStmt->bind_param(
        'issssddisssssiiii',
        $categoryId,
        $category['slug'],
        $productName,
        $description,
        $shortDescription,
        $originalPrice,
        $discountPrice,
        $discountPercentage,
        $mainImage,
        $galleryImage1,
        $galleryImage2,
        $galleryImage3,
        $sku,
        $stockQuantity,
        $isFeatured,
        $isActive,
        $productId
    );
    $updateStmt->execute();
    $updateStmt->close();

    if ($selectedWeight !== '' && $weightGrams > 0) {
        $variantOriginalPrice = $originalPrice * $weightMultiplier;
        $variantDiscountPrice = $discountPrice * $weightMultiplier;
        $variantSku = $sku !== null ? $sku . '-' . ($weightGrams <= 250 ? 'A' : ($weightGrams <= 500 ? 'B' : 'C')) : null;

        $variantStmt = $conn->prepare("
            SELECT id FROM product_variants
            WHERE product_id = ?
            ORDER BY weight_grams ASC, id ASC
            LIMIT 1
        ");
        $variantStmt->bind_param('i', $productId);
        $variantStmt->execute();
        $variant = $variantStmt->get_result()->fetch_assoc();
        $variantStmt->close();

        if ($variant) {
            $updateVariant = $conn->prepare("
                UPDATE product_variants
                SET weight_label = ?,
                    weight_value = ?,
                    weight_grams = ?,
                    variant_price = ?,
                    variant_discount_price = ?,
                    variant_sku = ?,
                    stock_quantity = ?,
                    is_active = 1
                WHERE id = ?
            ");
            $updateVariant->bind_param('ssiddsii', $selectedWeight, $selectedWeight, $weightGrams, $variantOriginalPrice, $variantDiscountPrice, $variantSku, $stockQuantity, $variant['id']);
            $updateVariant->execute();
            $updateVariant->close();
        } else {
            $insertVariant = $conn->prepare("
                INSERT INTO product_variants (
                    product_id, weight_label, weight_value, weight_grams, variant_price,
                    variant_discount_price, variant_sku, stock_quantity, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $insertVariant->bind_param('issiddsi', $productId, $selectedWeight, $selectedWeight, $weightGrams, $variantOriginalPrice, $variantDiscountPrice, $variantSku, $stockQuantity);
            $insertVariant->execute();
            $insertVariant->close();
        }
    }

    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Product updated successfully!';
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }
    }

    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
} catch (Throwable $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
exit();
?>
