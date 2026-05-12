<?php
require_once('includes/customer-auth.php');

// If already logged in, redirect to home
if (is_customer_logged_in()) {
    header('Location: index.php');
    exit();
}

// Handle registration form submission
$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
    ];
    
    $result = register_customer($data);
    
    if ($result['success']) {
        $register_success = $result['message'];
        
        // Redirect to previous page or home
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        
        header('Location: ' . $redirect);
        exit();
    } else {
        $register_error = $result['message'];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Customer Registration - Nutri Afghan</title>

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
                <h3 class="heading text-center">Create Account</h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>Register</li>
                </ul>
            </div>
        </div>
        <!-- /page-title -->

        <!-- Registration Section -->
        <section class="flat-spacing">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="register-wrapper">
                            <div class="register-box">
                                <div class="register-content">
                                    <h3 class="heading-register">Create Your Account</h3>
                                    <p class="text-secondary mb_24">Fill in the form below to create a new account and start shopping.</p>

                                    <!-- Error Message -->
                                    <?php if (!empty($register_error)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>Error!</strong> <?php echo htmlspecialchars($register_error); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Success Message -->
                                    <?php if (!empty($register_success)): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>Success!</strong> <?php echo htmlspecialchars($register_success); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Registration Form -->
                                    <form method="POST" action="" class="form-register" id="registerForm">
                                        <div class="row">
                                            <div class="col-md-6 mb_20">
                                                <fieldset class="firstname">
                                                    <label for="first_name" class="label-form">First Name <span class="required">*</span></label>
                                                    <input 
                                                        type="text" 
                                                        id="first_name" 
                                                        name="first_name" 
                                                        placeholder="Enter your first name"
                                                        value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                                        required
                                                        minlength="2"
                                                        maxlength="100"
                                                        pattern="[a-zA-Z\s\'-]{2,100}"
                                                        title="First name must be 2-100 characters, letters only (spaces, hyphens, and apostrophes allowed)"
                                                        class="form-control"
                                                    >
                                                    <small class="text-secondary">2-100 characters, letters only</small>
                                                </fieldset>
                                            </div>

                                            <div class="col-md-6 mb_20">
                                                <fieldset class="lastname">
                                                    <label for="last_name" class="label-form">Last Name <span class="required">*</span></label>
                                                    <input 
                                                        type="text" 
                                                        id="last_name" 
                                                        name="last_name" 
                                                        placeholder="Enter your last name"
                                                        value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                                        required
                                                        minlength="2"
                                                        maxlength="100"
                                                        pattern="[a-zA-Z\s\'-]{2,100}"
                                                        title="Last name must be 2-100 characters, letters only (spaces, hyphens, and apostrophes allowed)"
                                                        class="form-control"
                                                    >
                                                    <small class="text-secondary">2-100 characters, letters only</small>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <fieldset class="email mb_20">
                                            <label for="register-email" class="label-form">Email Address <span class="required">*</span></label>
                                            <input 
                                                type="email" 
                                                id="register-email" 
                                                name="email" 
                                                placeholder="Enter your email address"
                                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                required
                                                maxlength="255"
                                                title="Please enter a valid email address"
                                                class="form-control"
                                            >
                                            <small class="text-secondary">Max 255 characters. Must be a valid email format</small>
                                        </fieldset>

                                        <fieldset class="phone mb_20">
                                            <label for="phone" class="label-form">Phone Number (Optional)</label>
                                            <input 
                                                type="tel" 
                                                id="phone" 
                                                name="phone" 
                                                placeholder="Enter your phone number"
                                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                                class="form-control"
                                            >
                                        </fieldset>

                                        <fieldset class="password mb_20">
                                            <label for="register-password" class="label-form">Password <span class="required">*</span></label>
                                            <div class="password-wrapper">
                                                <input 
                                                    type="password" 
                                                    id="register-password" 
                                                    name="password" 
                                                    placeholder="Enter a strong password (minimum 6 characters)"
                                                    required
                                                    class="form-control"
                                                >
                                                <span class="show-pass toggle-password">
                                                    <i class="icon-eye view"></i>
                                                    <i class="icon-eye-off hide" style="display: none;"></i>
                                                </span>
                                            </div>
                                            <small class="text-secondary">Password must be at least 6 characters long</small>
                                        </fieldset>

                                        <fieldset class="password-confirm mb_24">
                                            <label for="password_confirm" class="label-form">Confirm Password <span class="required">*</span></label>
                                            <div class="password-wrapper">
                                                <input 
                                                    type="password" 
                                                    id="password_confirm" 
                                                    name="password_confirm" 
                                                    placeholder="Confirm your password"
                                                    required
                                                    class="form-control"
                                                >
                                                <span class="show-pass toggle-password">
                                                    <i class="icon-eye view"></i>
                                                    <i class="icon-eye-off hide" style="display: none;"></i>
                                                </span>
                                            </div>
                                        </fieldset>

                                        <button type="submit" name="register_submit" class="btn-style-2 w-100 text-btn-uppercase mb_20">Create Account</button>
                                    </form>

                                    <!-- Divider -->
                                    <div class="divider-register mb_24">
                                        <span>Already have an account?</span>
                                    </div>

                                    <!-- Login Link -->
                                    <p class="text-center text-secondary mb_12">Sign in to your existing account</p>
                                    <a href="customer-login.php" class="btn-style-3 w-100 text-btn-uppercase">Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Registration Section -->

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

        // Validate passwords match on submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
            }
        });
    </script>
</body>

</html>
