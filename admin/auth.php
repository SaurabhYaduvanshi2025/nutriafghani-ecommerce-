<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN ADMIN AUTH MIDDLEWARE
 * -------------------------------------------------
 * PURPOSE:
 * - Protect all admin pages
 * - Prevent unauthorized access
 * - Secure session handling
 * -------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * -----------------------------
 * SECURITY CHECK
 * -----------------------------
 * If admin is not logged in → redirect to login
 */
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    
    // Optional: store attempted URL (future use)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    header("Location: admin-login.php");
    exit();
}

/**
 * -----------------------------
 * OPTIONAL SECURITY HARDENING
 * -----------------------------
 * Prevent session hijacking (basic protection)
 */

if (!isset($_SESSION['admin_agent'])) {
    $_SESSION['admin_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    if ($_SESSION['admin_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header("Location: admin-login.php");
        exit();
    }
}

/**
 * -----------------------------
 * OPTIONAL: ADMIN GLOBAL DATA
 * -----------------------------
 */
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';