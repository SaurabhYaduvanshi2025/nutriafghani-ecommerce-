<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN ADMIN LOGOUT
 * -------------------------------------------------
 * PURPOSE:
 * - Destroy session
 * - Clear all session variables
 * - Redirect to login page
 * -------------------------------------------------
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Clear all session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_agent']);
unset($_SESSION['redirect_after_login']);

// 2. Empty the entire session array
$_SESSION = array();

// 3. Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destroy the session
session_destroy();

// 5. Clear any cached session data from superglobal
if (isset($_SERVER['PHPSESSID'])) {
    unset($_SERVER['PHPSESSID']);
}

// 6. Redirect to login page with cache-busting headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Location: admin-login.php");
exit();
?>
