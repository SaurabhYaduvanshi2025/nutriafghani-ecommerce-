<?php
require_once('config/db.php');

function redirect_contact($status)
{
    header('Location: contact.php?status=' . urlencode($status) . '#contactform');
    exit;
}

function ensure_contact_messages_table($conn)
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(150) NOT NULL,
            `mobile` VARCHAR(30) NOT NULL,
            `message` TEXT NOT NULL,
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_contact_created_at` (`created_at`),
            INDEX `idx_contact_is_read` (`is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_contact('invalid');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $mobile === '' || $message === '') {
    redirect_contact('missing');
}

if (strlen($name) > 80 || strlen($email) > 150 || strlen($mobile) > 20 || strlen($message) > 1000) {
    redirect_contact('too_long');
}

if (strlen($name) < 2) {
    redirect_contact('invalid_name');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_contact('invalid_email');
}

$mobileDigits = preg_replace('/\D/', '', $mobile);
if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) || strlen($mobileDigits) < 7 || strlen($mobileDigits) > 15) {
    redirect_contact('invalid_mobile');
}

if (strlen($message) < 10) {
    redirect_contact('invalid_message');
}

try {
    ensure_contact_messages_table($conn);

    $stmt = $conn->prepare("
        INSERT INTO contact_messages (name, email, mobile, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('ssss', $name, $email, $mobile, $message);
    $stmt->execute();
    $stmt->close();

    redirect_contact('sent');
} catch (Exception $e) {
    error_log('Contact form save failed: ' . $e->getMessage());
    redirect_contact('error');
}
