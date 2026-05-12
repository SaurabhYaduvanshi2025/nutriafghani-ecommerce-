<?php
/**
 * =====================================================
 * ADD PRODUCT PROCESSOR
 * Handles form submission, validation, and database insertion
 * =====================================================
 */

// Start output buffering to prevent accidental output
ob_start();

// Set header before any output
header('Content-Type: application/json');

// Set error handling to not output HTML errors
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Include database connection
require_once('../config/db.php');
require_once('auth.php');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'product_id' => null,
    'errors' => []
];

try {
    error_log("=== Product Add Form Submission Started ===");
    
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    error_log("Request method: POST - OK");

    // =====================================================
    // 1. VALIDATE AND SANITIZE INPUT
    // =====================================================

    // Product Name
    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    if (empty($productName)) {
        throw new Exception('Product name is required');
    }
    if (strlen($productName) > 255) {
        throw new Exception('Product name exceeds 255 characters');
    }

    // Category
    $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $categorySlug = isset($_POST['category_slug']) ? trim($_POST['category_slug']) : '';
    if ($categoryId <= 0) {
        throw new Exception('Invalid category selected');
    }

    // Verify category exists
    $categoryCheck = $conn->prepare("SELECT id, slug FROM categories WHERE id = ? AND is_active = 1");
    $categoryCheck->bind_param("i", $categoryId);
    $categoryCheck->execute();
    $categoryResult = $categoryCheck->get_result();
    
    if ($categoryResult->num_rows === 0) {
        throw new Exception('Selected category does not exist');
    }
    
    $categoryData = $categoryResult->fetch_assoc();
    $categorySlug = $categoryData['slug']; // Use verified slug from database

    // Description
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    if (strlen($description) > 1000) {
        throw new Exception('Description exceeds 1000 characters');
    }

    // Short Description (Optional)
    $shortDescription = isset($_POST['short_description']) ? trim($_POST['short_description']) : '';
    $shortDescription = substr($shortDescription, 0, 500);

    // Original Price
    $originalPrice = isset($_POST['original_price']) ? floatval($_POST['original_price']) : 0;
    if ($originalPrice <= 0) {
        throw new Exception('Original price must be greater than 0');
    }

    // Discount Price
    $discountPrice = isset($_POST['discount_price']) ? floatval($_POST['discount_price']) : 0;
    if ($discountPrice <= 0) {
        throw new Exception('Discount price must be greater than 0');
    }

    if ($discountPrice >= $originalPrice) {
        throw new Exception('Discount price must be less than original price');
    }

    // Calculate discount percentage
    $discountPercentage = round((($originalPrice - $discountPrice) / $originalPrice) * 100);

    // SKU (Optional)
    $sku = isset($_POST['sku']) ? trim($_POST['sku']) : '';
    if (!empty($sku)) {
        // Check if SKU already exists
        $skuCheck = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $skuCheck->bind_param("s", $sku);
        $skuCheck->execute();
        if ($skuCheck->get_result()->num_rows > 0) {
            throw new Exception('SKU already exists. Please use a unique SKU');
        }
    }

    // Stock Quantity
    $stockQuantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    if ($stockQuantity < 0) {
        throw new Exception('Stock quantity must be 0 or more');
    }

    // Featured Flag
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Weight Information
    $selectedWeight = isset($_POST['selected_weight']) ? trim($_POST['selected_weight']) : '';
    $weightGrams = isset($_POST['weight_grams']) ? intval($_POST['weight_grams']) : 0;
    $weightMultiplier = isset($_POST['weight_multiplier']) ? floatval($_POST['weight_multiplier']) : 1;

    if (empty($selectedWeight) || $weightGrams <= 0) {
        throw new Exception('Invalid weight selection');
    }

    // =====================================================
    // 2. HANDLE FILE UPLOADS
    // =====================================================

    $uploadDir = '../uploads/products/' . date('Y/m/');
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Function to handle file upload
    function uploadProductImage($fileInputName, $uploadDir) {
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$fileInputName];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error uploading ' . $fileInputName);
        }

        $fileSize = $file['size'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if ($fileSize > $maxFileSize) {
            throw new Exception($fileInputName . ' size exceeds 5MB');
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception($fileInputName . ' must be a valid image file');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save ' . $fileInputName);
        }

        return 'products/' . date('Y/m/') . $filename;
    }

    // Upload main image (required)
    error_log("Starting file uploads...");
    $mainImage = uploadProductImage('main_image', $uploadDir);
    if (!$mainImage) {
        throw new Exception('Main image is required');
    }
    error_log("Main image uploaded: $mainImage");

    // Upload gallery images (optional)
    $galleryImage1 = uploadProductImage('gallery_image_1', $uploadDir);
    $galleryImage2 = uploadProductImage('gallery_image_2', $uploadDir);
    $galleryImage3 = uploadProductImage('gallery_image_3', $uploadDir);
    error_log("Gallery images uploaded");

    // =====================================================
    // 3. GENERATE PRODUCT SLUG
    // =====================================================

    function generateSlug($text) {
        // Convert to lowercase
        $text = strtolower($text);
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        // Remove hyphens from start and end
        $text = trim($text, '-');
        return $text;
    }

    $baseSlug = generateSlug($productName);
    $productSlug = $baseSlug;
    $slugCounter = 1;

    // Check if slug exists and make it unique
    while (true) {
        $slugCheck = $conn->prepare("SELECT id FROM products WHERE slug = ?");
        $slugCheck->bind_param("s", $productSlug);
        $slugCheck->execute();
        
        if ($slugCheck->get_result()->num_rows === 0) {
            break;
        }
        
        $productSlug = $baseSlug . '-' . $slugCounter;
        $slugCounter++;
    }

    // =====================================================
    // 4. INSERT PRODUCT INTO DATABASE
    // =====================================================

    error_log("Preparing product insert...");
    error_log("Category ID: $categoryId, Product Name: $productName, Slug: $productSlug");
    
    $insertProduct = $conn->prepare("
        INSERT INTO products (
            category_id,
            category_slug,
            name,
            slug,
            description,
            short_description,
            original_price,
            discount_price,
            discount_percentage,
            main_image,
            gallery_image_1,
            gallery_image_2,
            gallery_image_3,
            sku,
            stock_quantity,
            is_featured,
            is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    if (!$insertProduct) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    error_log("Binding parameters for product insert...");

    $insertProduct->bind_param(
        "isssssddisssssii",
        $categoryId,
        $categorySlug,
        $productName,
        $productSlug,
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
        $isFeatured
    );
    
    error_log("Executing product insert...");

    if (!$insertProduct->execute()) {
        throw new Exception('Failed to insert product: ' . $insertProduct->error);
    }
    
    error_log("Product inserted successfully");

    $productId = $conn->insert_id;

    // =====================================================
    // 5. INSERT PRODUCT VARIANT (WEIGHT OPTION)
    // =====================================================

    // Calculate variant prices
    $variantOriginalPrice = $originalPrice * $weightMultiplier;
    $variantDiscountPrice = $discountPrice * $weightMultiplier;

    // Generate variant SKU
    $variantSku = '';
    if (!empty($sku)) {
        // Generate weight code based on weight in grams
        $weightCode = $weightGrams <= 250 ? 'A' : ($weightGrams <= 500 ? 'B' : 'C');
        $variantSku = $sku . '-' . $weightCode;
    }
    
    error_log("Preparing variant insert... Variant Price: $variantOriginalPrice, Discount: $variantDiscountPrice");

    $insertVariant = $conn->prepare("
        INSERT INTO product_variants (
            product_id,
            weight_label,
            weight_value,
            weight_grams,
            variant_price,
            variant_discount_price,
            variant_sku,
            stock_quantity,
            is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    if (!$insertVariant) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    error_log("Binding parameters for variant insert...");

    $insertVariant->bind_param(
        "issiddsi",
        $productId,
        $selectedWeight,
        $selectedWeight,
        $weightGrams,
        $variantOriginalPrice,
        $variantDiscountPrice,
        $variantSku,
        $stockQuantity
    );
    
    error_log("Executing variant insert...");

    if (!$insertVariant->execute()) {
        throw new Exception('Failed to insert product variant: ' . $insertVariant->error);
    }
    
    error_log("Variant inserted successfully");

    // =====================================================
    // 6. SUCCESS RESPONSE
    // =====================================================

    $response['success'] = true;
    $response['message'] = 'Product "' . $productName . '" has been added successfully!';
    $response['product_id'] = $productId;
    $response['product_slug'] = $productSlug;

    // Log successful product creation (optional)
    error_log("Product created successfully. ID: $productId, Name: $productName, Category: $categorySlug");

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Product creation error: " . $e->getMessage());
} catch (Throwable $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Unexpected error in process-add-product.php: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
}

// Clear any buffered output
ob_end_clean();

// Return JSON response
echo json_encode($response);
exit();
?>
