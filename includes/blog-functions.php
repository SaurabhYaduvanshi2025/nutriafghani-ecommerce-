<?php
/**
 * Shared helpers for the dynamic blog system.
 */

function blog_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function blog_ensure_table(mysqli $conn)
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS `blogs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `image` varchar(255) DEFAULT NULL,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_description` text DEFAULT NULL,
            `meta_keywords` text DEFAULT NULL,
            `content` longtext NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_blog_slug` (`slug`),
            KEY `idx_blogs_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    blog_ensure_column($conn, 'meta_title', "ALTER TABLE `blogs` ADD `meta_title` varchar(255) DEFAULT NULL AFTER `image`");
    blog_ensure_column($conn, 'meta_description', "ALTER TABLE `blogs` ADD `meta_description` text DEFAULT NULL AFTER `meta_title`");
    blog_ensure_column($conn, 'meta_keywords', "ALTER TABLE `blogs` ADD `meta_keywords` text DEFAULT NULL AFTER `meta_description`");
}

function blog_ensure_column(mysqli $conn, $column, $alterSql)
{
    $stmt = $conn->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'blogs'
        AND COLUMN_NAME = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$exists) {
        $conn->query($alterSql);
    }
}

function blog_normalize_keywords($keywords)
{
    $parts = preg_split('/[\r\n,]+/', (string) $keywords);
    $clean = [];

    foreach ($parts as $part) {
        $keyword = trim($part);
        if ($keyword !== '') {
            $clean[] = $keyword;
        }
    }

    return implode(', ', array_unique($clean));
}

function blog_slugify($title)
{
    $slug = strtolower(trim((string) $title));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'blog-post';
}

function blog_unique_slug(mysqli $conn, $title, $excludeId = null)
{
    $baseSlug = blog_slugify($title);
    $slug = $baseSlug;
    $counter = 2;

    while (true) {
        if ($excludeId) {
            $stmt = $conn->prepare('SELECT id FROM blogs WHERE slug = ? AND id != ? LIMIT 1');
            $stmt->bind_param('si', $slug, $excludeId);
        } else {
            $stmt = $conn->prepare('SELECT id FROM blogs WHERE slug = ? LIMIT 1');
            $stmt->bind_param('s', $slug);
        }

        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$exists) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function blog_upload_image($file, $targetSubdir = 'blogs')
{
    if (empty($file) || empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => ''];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Image upload failed. Please try again.'];
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowedTypes[$mime])) {
        return ['success' => false, 'message' => 'Only JPG, PNG, WEBP, and GIF images are allowed.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image size must be 5MB or smaller.'];
    }

    $uploadRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    $targetDir = $uploadRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetSubdir);

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return ['success' => false, 'message' => 'Could not create upload folder.'];
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $allowedTypes[$mime];
    $absolutePath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        return ['success' => false, 'message' => 'Could not save uploaded image.'];
    }

    return [
        'success' => true,
        'path' => str_replace('\\', '/', $targetSubdir . '/' . $filename),
    ];
}

function blog_image_url($path)
{
    if (!$path) {
        return 'images/blog/blog-grid-1.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function blog_admin_image_url($path)
{
    if (!$path) {
        return '../images/blog/blog-grid-1.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return '../uploads/' . ltrim($path, '/');
}

function blog_excerpt($html, $length = 160)
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length - 3) . '...';
}
