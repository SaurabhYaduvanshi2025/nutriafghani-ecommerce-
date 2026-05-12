<?php
/**
 * ------------------------------------------------------------
 * Nutriafghan Database Configuration File
 * ------------------------------------------------------------
 * Secure MySQLi Database Connection
 * Industry-Level Setup
 * ------------------------------------------------------------
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nutriafghan');

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create Database Connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Set Character Encoding
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {

    // Log actual error privately (for developers)
    error_log("Database Connection Error: " . $e->getMessage());

    // For AJAX requests, return JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
        exit();
    }

    // For regular requests, show generic error
    die("Database connection failed. Please try again later.");
}