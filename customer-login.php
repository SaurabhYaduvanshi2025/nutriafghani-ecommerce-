<?php
require_once('includes/customer-auth.php');

$requestedRedirect = $_GET['redirect'] ?? '';
if (is_safe_customer_redirect($requestedRedirect)) {
    $_SESSION['redirect_after_login'] = $requestedRedirect;
}

// If already logged in, redirect to home
if (is_customer_logged_in()) {
    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . (is_safe_customer_redirect($redirect) ? $redirect : 'index.php'));
    exit();
}

// Handle login form submission
$login_error = '';
$login_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = login_customer($email, $password);
    
    if ($result['success']) {
        $login_success = $result['message'];
        
        // Redirect to previous page or home
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        
        header('Location: ' . (is_safe_customer_redirect($redirect) ? $redirect : 'index.php'));
        exit();
    } else {
        $login_error = $result['message'];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Customer Login - Nutri Afghan</title>

    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />

    <?php
    include_once('includes/header-link.php');
    ?>
</head>

<body class="preload-wrapper popup-loader">
    <!-- Scroll Top -->
    <?php
    include_once('includes/scroll-top.php');
    ?>
    <!-- /Scroll Top -->

    <!-- preload -->
    <?php
    include_once('includes/preloader.php');
    ?>
    <!-- /preload -->

    <div id="wrapper">
        <!-- Header -->
        <?php
        include_once('includes/header.php');
        ?>
        <!-- /Header -->

        <!-- page-title -->
        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container">
                <h3 class="heading text-center">Customer Login</h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>Login</li>
                </ul>
            </div>
        </div>
        <!-- /page-title -->

        <!-- Login Section -->
        <section class="flat-spacing">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="login-register-wrapper">
                            <div class="login-box">
                                <div class="login-content">
                                    <h3 class="heading-login">Login to Your Account</h3>
                                    <p class="text-secondary mb_24">Enter your email and password to access your account.</p>

                                    <!-- Error Message -->
                                    <?php if (!empty($login_error)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>Error!</strong> <?php echo htmlspecialchars($login_error); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Success Message -->
                                    <?php if (!empty($login_success)): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>Success!</strong> <?php echo htmlspecialchars($login_success); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Login Form -->
                                    <form method="POST" action="" class="form-login">
                                        <fieldset class="email mb_24">
                                            <label for="login-email" class="label-form">Email Address <span class="required">*</span></label>
                                            <input 
                                                type="email" 
                                                id="login-email" 
                                                name="email" 
                                                placeholder="Enter your email address"
                                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                required
                                                class="form-control"
                                            >
                                        </fieldset>

                                        <fieldset class="password mb_12">
                                            <label for="login-password" class="label-form">Password <span class="required">*</span></label>
                                            <div class="password-wrapper">
                                                <input 
                                                    type="password" 
                                                    id="login-password" 
                                                    name="password" 
                                                    placeholder="Enter your password"
                                                    required
                                                    class="form-control"
                                                >
                                                <span class="show-pass toggle-password">
                                                    <i class="icon-eye view"></i>
                                                    <i class="icon-eye-off hide" style="display: none;"></i>
                                                </span>
                                            </div>
                                        </fieldset>

                                        <div class="mb_24">
                                            <a href="customer-forgot-password.php" class="text-secondary">Forgot Password?</a>
                                        </div>

                                        <button type="submit" name="login_submit" class="btn-style-2 w-100 text-btn-uppercase">Login</button>
                                    </form>

                                    <!-- Divider -->
                                    <div class="divider-login mb_24">
                                        <span>Don't have an account?</span>
                                    </div>

                                    <!-- Register Link -->
                                    <p class="text-center text-secondary mb_12">Create a new account to get started</p>
                                    <a href="customer-register.php" class="btn-style-3 w-100 text-btn-uppercase">Create New Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Login Section -->

        <!-- Footer -->
        <?php
        include_once('includes/footer.php');
        ?>
        <!-- /Footer -->
    </div>

    <!-- Javascript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/main.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const viewIcon = this.querySelector('.view');
                const hideIcon = this.querySelector('.hide');

                if (input.type === 'password') {
                    input.type = 'text';
                    viewIcon.style.display = 'none';
                    hideIcon.style.display = 'inline';
                } else {
                    input.type = 'password';
                    viewIcon.style.display = 'inline';
                    hideIcon.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
