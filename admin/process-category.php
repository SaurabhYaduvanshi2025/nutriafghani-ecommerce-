<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN CATEGORY PROCESSING
 * -------------------------------------------------
 * PURPOSE:
 * - Handle category creation with slug support
 * - Process image uploads
 * - Store to database
 * -------------------------------------------------
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('../config/db.php');

// Set JSON response header
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'create');

    if ($action === 'update') {
        $categoryId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($categoryId <= 0) {
            throw new Exception('Invalid category selected');
        }

        if (empty($_POST['categoryName'])) {
            throw new Exception('Category name is required');
        }

        if (empty($_POST['slug'])) {
            throw new Exception('Slug is required');
        }

        $categoryName = trim($_POST['categoryName']);
        $slug = sanitize_slug($_POST['slug']);
        $menuId = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;
        $isActive = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
        $isActive = $isActive === 1 ? 1 : 0;

        if ($menuId <= 0) {
            throw new Exception('Please select a menu for this category');
        }

        if (strlen($categoryName) < 2 || strlen($categoryName) > 255) {
            throw new Exception('Category name must be between 2 and 255 characters');
        }

        if (strlen($slug) < 2 || strlen($slug) > 255) {
            throw new Exception('Slug must be between 2 and 255 characters');
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new Exception('Slug can only contain lowercase letters, numbers, and hyphens');
        }

        $menuStmt = $conn->prepare("SELECT id FROM menu_items WHERE id = ? AND menu_type = 'homepage' AND parent_id IS NULL LIMIT 1");
        $menuStmt->bind_param('i', $menuId);
        $menuStmt->execute();
        if ($menuStmt->get_result()->num_rows === 0) {
            throw new Exception('Selected menu was not found');
        }
        $menuStmt->close();

        $existingStmt = $conn->prepare("SELECT id, image FROM categories WHERE id = ? LIMIT 1");
        $existingStmt->bind_param('i', $categoryId);
        $existingStmt->execute();
        $existingCategory = $existingStmt->get_result()->fetch_assoc();
        $existingStmt->close();

        if (!$existingCategory) {
            throw new Exception('Category not found');
        }

        $checkSlug = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1");
        $checkSlug->bind_param('si', $slug, $categoryId);
        $checkSlug->execute();
        if ($checkSlug->get_result()->num_rows > 0) {
            throw new Exception('This slug already exists. Please use a different slug.');
        }
        $checkSlug->close();

        $checkName = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1");
        $checkName->bind_param('si', $categoryName, $categoryId);
        $checkName->execute();
        if ($checkName->get_result()->num_rows > 0) {
            throw new Exception('This category name already exists. Please use a different name.');
        }
        $checkName->close();

        $imagePath = $existingCategory['image'];
        $uploadedFilePath = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Image upload failed. Please try again.');
            }

            $file = $_FILES['image'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxFileSize = 5 * 1024 * 1024;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes)) {
                throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed');
            }

            if ($file['size'] > $maxFileSize) {
                throw new Exception('File size exceeds 5MB limit');
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . sanitize_filename($categoryName) . '.' . $ext;
            $uploadDir = __DIR__ . '/images/category/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }

            $uploadedFilePath = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
                throw new Exception('Failed to save image file');
            }

            $imagePath = 'images/category/' . $filename;
        }

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                UPDATE categories
                SET menu_id = ?, name = ?, slug = ?, image = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->bind_param('isssii', $menuId, $categoryName, $slug, $imagePath, $isActive, $categoryId);
            $stmt->execute();
            $stmt->close();

            $productStmt = $conn->prepare("UPDATE products SET category_slug = ? WHERE category_id = ?");
            $productStmt->bind_param('si', $slug, $categoryId);
            $productStmt->execute();
            $productStmt->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                unlink($uploadedFilePath);
            }
            throw $e;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully!',
            'data' => [
                'id' => $categoryId,
                'name' => $categoryName,
                'slug' => $slug,
                'menu_id' => $menuId,
                'image' => $imagePath,
                'is_active' => $isActive
            ]
        ]);
        exit();
    }

    // Validate required fields
    if (empty($_POST['categoryName'])) {
        throw new Exception('Category name is required');
    }

    if (empty($_POST['slug'])) {
        throw new Exception('Slug is required');
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed. Please try again.');
    }

    // Sanitize inputs
    $categoryName = trim($_POST['categoryName']);
    $slug = sanitize_slug($_POST['slug']);
    $menuId = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;

    if ($menuId <= 0) {
        throw new Exception('Please select a menu for this category');
    }

    // Validate inputs
    if (strlen($categoryName) < 2 || strlen($categoryName) > 255) {
        throw new Exception('Category name must be between 2 and 255 characters');
    }

    if (strlen($slug) < 2 || strlen($slug) > 255) {
        throw new Exception('Slug must be between 2 and 255 characters');
    }

    // Validate slug format (alphanumeric and hyphens only)
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        throw new Exception('Slug can only contain lowercase letters, numbers, and hyphens');
    }

    $menuStmt = $conn->prepare("SELECT id FROM menu_items WHERE id = ? AND menu_type = 'homepage' AND parent_id IS NULL LIMIT 1");
    $menuStmt->bind_param('i', $menuId);
    $menuStmt->execute();
    if ($menuStmt->get_result()->num_rows === 0) {
        throw new Exception('Selected menu was not found');
    }
    $menuStmt->close();

    // Handle file upload
    $file = $_FILES['image'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    // Validate file MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed');
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        throw new Exception('File size exceeds 5MB limit');
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . sanitize_filename($categoryName) . '.' . $ext;

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/images/category/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    $filePath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save image file');
    }

    // Prepare image path for database (relative path)
    $imagePath = 'images/category/' . $filename;

    // Check if slug already exists
    $checkSlug = $conn->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
    if (!$checkSlug) {
        unlink($filePath);
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $checkSlug->bind_param('s', $slug);
    $checkSlug->execute();
    $result = $checkSlug->get_result();

    if ($result->num_rows > 0) {
        unlink($filePath);
        throw new Exception('This slug already exists. Please use a different slug.');
    }
    $checkSlug->close();

    // Check if category name already exists
    $checkName = $conn->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
    if (!$checkName) {
        unlink($filePath);
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $checkName->bind_param('s', $categoryName);
    $checkName->execute();
    $result = $checkName->get_result();

    if ($result->num_rows > 0) {
        unlink($filePath);
        throw new Exception('This category name already exists. Please use a different name.');
    }
    $checkName->close();

    // Insert category into database
    $stmt = $conn->prepare("
        INSERT INTO categories (menu_id, name, slug, image, is_active)
        VALUES (?, ?, ?, ?, 1)
    ");

    if (!$stmt) {
        unlink($filePath);
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('isss', $menuId, $categoryName, $slug, $imagePath);

    if (!$stmt->execute()) {
        unlink($filePath);
        throw new Exception('Failed to insert category: ' . $stmt->error);
    }

    $categoryId = $conn->insert_id;
    $stmt->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully!',
        'data' => [
            'id' => $categoryId,
            'menu_id' => $menuId,
            'name' => $categoryName,
            'slug' => $slug,
            'image' => $imagePath
        ]
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log("Category Processing Error: " . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Helper function to sanitize filename
 */
function sanitize_filename($filename) {
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-z0-9-]+/', '-', $filename);
    $filename = preg_replace('/-+/', '-', $filename);
    $filename = trim($filename, '-');
    return substr($filename, 0, 50); // Limit length
}

/**
 * Helper function to sanitize SEO slug
 */
function sanitize_slug($slug) {
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9\s-]+/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

?>
