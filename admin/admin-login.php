<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN ADMIN LOGIN SYSTEM
 * -------------------------------------------------
 * PURPOSE:
 * - Secure admin authentication
 * - Password verification
 * - Session protection
 * - Session hijacking prevention
 * -------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/db.php');

// Check if user is already logged in AND admin_id exists
// If session is stale or invalid, clear it
if (isset($_SESSION['admin_id'])) {
    // Verify the session is still valid (admin exists in database)
    $stmt = $conn->prepare("SELECT id FROM admin WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Admin exists, redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            // Admin doesn't exist or session is invalid, clear session
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
        }
        $stmt->close();
    }
}

$error = "";

// Handle login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {

        // Secure prepared statement
        $stmt = $conn->prepare("
            SELECT id, email, password 
            FROM admin
            WHERE email = ? 
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                // Verify hashed password
                if (password_verify($password, $admin['password'])) {

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Secure session variables
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['email'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_agent'] = $_SERVER['HTTP_USER_AGENT'];

                    // Redirect to intended page if exists
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $redirect");
                    } else {
                        header("Location: index.php");
                    }

                    exit();

                } else {
                    $error = "Incorrect password.";
                }

            } else {
                $error = "Admin account not found.";
            }

            $stmt->close();

        } else {
            $error = "System error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Admin Panel - Nutri Afghan</title>

    <?php include_once('includes/header-link.php'); ?>
</head>

<body class="body">

<div id="wrapper">
<div id="page">

<div class="wrap-login-page">
<div class="flex-grow flex flex-column justify-center gap30">

    <a href="./" id="site-logo-inner">
        <img src="images/main_logo.png" width="150px" alt="">
    </a>

    <div class="login-box">

        <h3>Login to Admin Panel</h3>
        <div class="body-text">Enter your email & password to login</div>

        <?php if (!empty($error)) { ?>
            <div style="color:red; margin-top:10px; font-weight:500;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <!-- LOGIN FORM -->
        <form class="form-login flex flex-column gap24" method="POST" action="">

            <fieldset class="email">
                <div class="body-title mb-10">
                    Email address <span class="tf-color-1">*</span>
                </div>
                <input class="flex-grow"
                       type="email"
                       name="email"
                       placeholder="Enter your email address"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       required>
            </fieldset>

            <fieldset class="password">
                <div class="body-title mb-10">
                    Password <span class="tf-color-1">*</span>
                </div>

                <input class="password-input"
                       type="password"
                       name="password"
                       placeholder="Enter your password"
                       required>

                <span class="show-pass">
                    <i class="icon-eye view"></i>
                    <i class="icon-eye-off hide"></i>
                </span>
            </fieldset>

            <button type="submit" class="tf-button w-full">
                Login
            </button>

        </form>

    </div>

</div>

<?php include_once('includes/footer.php'); ?>

</div>
</div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>