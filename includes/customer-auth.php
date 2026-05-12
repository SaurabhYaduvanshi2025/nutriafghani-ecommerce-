<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN CUSTOMER AUTHENTICATION
 * -------------------------------------------------
 * PURPOSE:
 * - Handle customer login/logout
 * - Manage customer sessions
 * - Provide authentication helper functions
 * -------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db.php');

/**
 * Check if customer is logged in
 * @return bool
 */
function is_customer_logged_in()
{
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

/**
 * Get current logged-in customer ID
 * @return int|null
 */
function get_customer_id()
{
    return isset($_SESSION['customer_id']) ? intval($_SESSION['customer_id']) : null;
}

/**
 * Get current logged-in customer email
 * @return string|null
 */
function get_customer_email()
{
    return isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : null;
}

/**
 * Get current logged-in customer name
 * @return string|null
 */
function get_customer_name()
{
    if (isset($_SESSION['customer_first_name'])) {
        $name = $_SESSION['customer_first_name'];
        if (isset($_SESSION['customer_last_name']) && !empty($_SESSION['customer_last_name'])) {
            $name .= ' ' . $_SESSION['customer_last_name'];
        }
        return $name;
    }
    return null;
}

/**
 * Login a customer
 * @param string $email Customer email
 * @param string $password Customer password (plain text)
 * @return array ['success' => bool, 'message' => string]
 */
function login_customer($email, $password)
{
    global $conn;

    // Validate inputs
    $email = trim($email);
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format.'];
    }

    // Get customer from database
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, is_active FROM customers WHERE email = ? LIMIT 1");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error. Please try again later.'];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Email or password is incorrect.'];
    }

    $customer = $result->fetch_assoc();

    // Check if account is active
    if (!$customer['is_active']) {
        return ['success' => false, 'message' => 'Your account has been deactivated.'];
    }

    // Verify password
    if (!password_verify($password, $customer['password_hash'])) {
        return ['success' => false, 'message' => 'Email or password is incorrect.'];
    }

    // Set session variables
    $_SESSION['customer_id'] = $customer['id'];
    $_SESSION['customer_email'] = $customer['email'];
    $_SESSION['customer_first_name'] = $customer['first_name'];
    $_SESSION['customer_last_name'] = $customer['last_name'];

    // Update last login
    $updateStmt = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE id = ?");
    if ($updateStmt) {
        $updateStmt->bind_param("i", $customer['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }

    return ['success' => true, 'message' => 'Login successful!'];
}

/**
 * Register a new customer
 * @param array $data Customer data
 * @return array ['success' => bool, 'message' => string]
 */
function register_customer($data)
{
    global $conn;

    // Validate required fields
    $first_name = isset($data['first_name']) ? trim($data['first_name']) : '';
    $last_name = isset($data['last_name']) ? trim($data['last_name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';
    $password_confirm = isset($data['password_confirm']) ? $data['password_confirm'] : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';

    // Validation
    if (empty($first_name)) {
        return ['success' => false, 'message' => 'First name is required.'];
    }

    if (strlen($first_name) < 2) {
        return ['success' => false, 'message' => 'First name must be at least 2 characters long.'];
    }

    if (strlen($first_name) > 100) {
        return ['success' => false, 'message' => 'First name cannot exceed 100 characters.'];
    }

    if (!preg_match('/^[a-zA-Z\s\'-]+$/', $first_name)) {
        return ['success' => false, 'message' => 'First name can only contain letters, spaces, hyphens, and apostrophes.'];
    }

    if (!empty($last_name) && strlen($last_name) > 0) {
        if (strlen($last_name) < 2) {
            return ['success' => false, 'message' => 'Last name must be at least 2 characters long.'];
        }

        if (strlen($last_name) > 100) {
            return ['success' => false, 'message' => 'Last name cannot exceed 100 characters.'];
        }

        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $last_name)) {
            return ['success' => false, 'message' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.'];
        }
    } else {
        return ['success' => false, 'message' => 'Last name is required.'];
    }

    if (empty($email)) {
        return ['success' => false, 'message' => 'Email is required.'];
    }

    if (strlen($email) > 255) {
        return ['success' => false, 'message' => 'Email is too long.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format.'];
    }

    if (empty($password)) {
        return ['success' => false, 'message' => 'Password is required.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    if ($password !== $password_confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
    if (!$checkStmt) {
        return ['success' => false, 'message' => 'Database error. Please try again later.'];
    }

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        return ['success' => false, 'message' => 'Email is already registered. Please use a different email or login.'];
    }
    $checkStmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new customer
    $insertStmt = $conn->prepare(
        "INSERT INTO customers (first_name, last_name, email, phone, password_hash, is_active) VALUES (?, ?, ?, ?, ?, 1)"
    );

    if (!$insertStmt) {
        return ['success' => false, 'message' => 'Database error. Please try again later.'];
    }

    $insertStmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $password_hash);

    if (!$insertStmt->execute()) {
        return ['success' => false, 'message' => 'Failed to create account. Please try again.'];
    }

    $customer_id = $insertStmt->insert_id;
    $insertStmt->close();

    // Auto-login after registration
    $_SESSION['customer_id'] = $customer_id;
    $_SESSION['customer_email'] = $email;
    $_SESSION['customer_first_name'] = $first_name;
    $_SESSION['customer_last_name'] = $last_name;

    return ['success' => true, 'message' => 'Account created successfully!'];
}

/**
 * Logout customer
 */
function logout_customer()
{
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

/**
 * Require customer login, redirect if not logged in
 * @param string $redirect_to Optional redirect URL after login
 */
function require_customer_login($redirect_to = '')
{
    if (!is_customer_logged_in()) {
        $_SESSION['redirect_after_login'] = $redirect_to ?: $_SERVER['REQUEST_URI'];
        header('Location: customer-login.php');
        exit();
    }
}
