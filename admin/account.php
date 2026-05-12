<?php
/**
 * -------------------------------------------------
 * NUTRIAFGHAN ADMIN ACCOUNT PAGE
 * -------------------------------------------------
 * PURPOSE:
 * - Display admin profile/account information
 * - Allow admin to update profile details
 * -------------------------------------------------
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Authentication check (redirects to admin-login.php if not logged in)
require_once('auth.php');

// Database connection
require_once('../config/db.php');

// Get admin details from session
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? 'N/A';

// Initialize variables
$admin_data = null;
$error_msg = "";
$success = "";
$error = "";

// Fetch full admin details from database
if (isset($conn) && $conn) {
    try {
        // Try to fetch with all columns first
        $stmt = $conn->prepare("SELECT id, email FROM admin WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin_data = $result->fetch_assoc();
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email'] ?? '');
    
    if (empty($new_email)) {
        $error = "Email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($admin_data && isset($conn)) {
        $stmt = $conn->prepare("UPDATE admin SET email = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $new_email, $admin_id);
            if ($stmt->execute()) {
                $_SESSION['admin_email'] = $new_email;
                $admin_data['email'] = $new_email;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Admin Account - Nutri Afghan</title>
    
    <?php
    if (file_exists('includes/header-link.php')) {
        include_once('includes/header-link.php');
    }
    ?>
    
    <style>
        .account-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .account-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .account-card h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:disabled {
            background: #f0f0f0;
            cursor: not-allowed;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>

<body class="body">
    <div id="wrapper">
        <div id="page">
            <div class="layout-wrap menu-style-icon">
                <?php
                if (file_exists('includes/preloader.php')) {
                    include_once('includes/preloader.php');
                }
                ?>

                <?php
                if (file_exists('includes/sidebar.php')) {
                    include_once('includes/sidebar.php');
                }
                ?>

                <div class="section-content-right">
                    <?php
                    if (file_exists('includes/top-header.php')) {
                        include_once('includes/top-header.php');
                    }
                    ?>

                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="account-container">
                                <h1 style="text-align: center; margin-bottom: 30px; color: #333;">Admin Account</h1>

                                <!-- Success Message -->
                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success">
                                        ✓ <?php echo htmlspecialchars($success); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Error Message -->
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger">
                                        ✕ <?php echo htmlspecialchars($error); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Profile Card -->
                                <div class="account-card">
                                    <h2>Profile Information</h2>
                                    
                                    <?php if ($admin_data): ?>
                                        <!-- Display from Database -->
                                        <form method="POST">
                                            <div class="form-group">
                                                <label>Admin ID:</label>
                                                <input type="text" value="<?php echo htmlspecialchars($admin_data['id']); ?>" disabled>
                                            </div>

                                            <div class="form-group">
                                                <label>Email Address:</label>
                                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                                            </div>

                                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Fallback: Display from Session -->
                                        <div class="alert alert-info">
                                            Could not load full profile from database. Showing session data:
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Admin Name:</label>
                                            <input type="text" value="<?php echo htmlspecialchars($admin_name); ?>" disabled>
                                        </div>

                                        <div class="form-group">
                                            <label>Admin Email:</label>
                                            <input type="text" value="<?php echo htmlspecialchars($admin_email); ?>" disabled>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Security Card -->
                                <div class="account-card">
                                    <h2>Security & Logout</h2>
                                    
                                    <div class="alert alert-info">
                                        <strong>Session Status:</strong> Active ✓<br>
                                        <strong>Logged in as:</strong> <?php echo htmlspecialchars($admin_name); ?>
                                    </div>

                                    <a href="logout.php" class="btn btn-danger">Log Out</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (file_exists('includes/footer.php')) {
        include_once('includes/footer.php');
    }
    ?>

    <!-- Javascript -->
    <?php
    if (file_exists('includes/footer-link.php')) {
        include_once('includes/footer-link.php');
    }
    ?>

</body>

</html>
