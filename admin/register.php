<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<!--<![endif]-->

<head>
    <!-- Basic Page Needs -->
    <meta charset="utf-8">
    <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
    <title>Admin Panel - Nutri Afghan</title>

    <?php
    include_once('includes/header-link.php');
    ?>
</head>

<body class="body">

    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <div class="wrap-login-page">
                <div class="flex-grow flex flex-column justify-center gap30">
                    <a href="./" id="site-logo-inner">
                        <img src="images/main_logo.png" width="150px" alt="">
                    </a>
                    <div class="login-box">
                        <div>
                            <h3>Create your account</h3>
                            <div class="body-text">Enter your personal details to create account</div>
                        </div>
                        <form class="form-login flex flex-column gap24">
                            <fieldset class="name">
                                <div class="body-title mb-10">Your username <span class="tf-color-1">*</span></div>
                                <div class="flex gap10">
                                    <input class="flex-grow" type="text" placeholder="First name" name="name"
                                        tabindex="0" value="" aria-required="true" required="">
                                    <input class="flex-grow" type="text" placeholder="Last name" name="name"
                                        tabindex="0" value="" aria-required="true" required="">
                                </div>
                            </fieldset>
                            <fieldset class="email">
                                <div class="body-title mb-10">Email address <span class="tf-color-1">*</span></div>
                                <input class="flex-grow" type="email" placeholder="Enter your email address"
                                    name="email" tabindex="0" value="" aria-required="true" required="">
                            </fieldset>
                            <fieldset class="password">
                                <div class="body-title mb-10">Password <span class="tf-color-1">*</span></div>
                                <input class="password-input" type="password" placeholder="Enter your password"
                                    name="password" tabindex="0" value="" aria-required="true" required="">
                                <span class="show-pass">
                                    <i class="icon-eye view"></i>
                                    <i class="icon-eye-off hide"></i>
                                </span>
                            </fieldset>
                            <fieldset class="password">
                                <div class="body-title mb-10">Confirm password <span class="tf-color-1">*</span></div>
                                <input class="password-input" type="password" placeholder="Enter your password"
                                    name="password" tabindex="0" value="" aria-required="true" required="">
                                <span class="show-pass">
                                    <i class="icon-eye view"></i>
                                    <i class="icon-eye-off hide"></i>
                                </span>
                            </fieldset>
                            <a href="#" class="tf-button w-full">Login</a>
                        </form>
                        <div class="body-text text-center">
                            You have an account?
                            <a href="#" class="body-text tf-color">Login Now</a>
                        </div>
                    </div>
                </div>

                <!-- Javascript -->
                <?php
                include_once('includes/footer.php');
                ?>

            </div>
        </div>
        <!-- /#page -->
    </div>
    <!-- /#wrapper -->

    <!-- Javascript -->
    <?php
    include_once('includes/footer-link.php');
    ?>

</body>


<!-- Mirrored from themesflat.co/html/remos/sign-up.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 17 Dec 2025 12:18:09 GMT -->

</html>