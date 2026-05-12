<?php
require_once('auth.php');
?>


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
      <!-- layout-wrap -->
      <div class="layout-wrap menu-style-icon">
        <!-- preload -->
        <?php
        include_once('includes/preloader.php');
        ?>
        <!-- /preload -->

        <!-- section-menu-left -->
        <?php
        include_once('includes/sidebar.php');
        ?>
        <!-- /section-menu-left -->

        <!-- section-content-right -->
        <div class="section-content-right">
          <!-- header-dashboard -->
          <?php
          include_once('includes/top-header.php');
          ?>
          <!-- /header-dashboard -->


          <!-- main-content -->
          <div class="main-content">
            <!-- main-content-wrap -->
            <div class="main-content-inner">
              <!-- main-content-wrap -->
              <div class="main-content-wrap">
                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                  <h3>User Details</h3>
                  <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                      <a href="./">
                        <div class="text-tiny">Dashboard</div>
                      </a>
                    </li>
                    <li>
                      <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                      <a href="all-user.php">
                        <div class="text-tiny">All User</div>
                      </a>
                    </li>
                    <li>
                      <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                      <div class="text-tiny">User Details</div>
                    </li>
                  </ul>
                </div>
                <!-- order-detail -->
                <div class="">
                  <div class="">
                    <div class="wg-box mb-20 gap10">
                      <div class="body-title">Username</div>
                      <div class="body-text">Yash Sharma</div>
                      <div class="body-title">Phone</div>
                      <div class="body-text">+91-9087876764</div>
                      <div class="body-title">Email</div>
                      <div class="body-text">yashsharma123@gmail.com</div>
                      <div class="body-title">Address</div>
                      <div class="body-text">R-Z 52-B, A3-block Dharampura Najafgarh, New Delhi-110043</div>
                    </div>
                  </div>
                </div>
                <!-- /order-detail -->
              </div>
              <!-- /main-content-wrap -->
            </div>
            <!-- /main-content-wrap -->
             
            <!-- bottom-page -->
            <?php
            include_once('includes/footer.php');
            ?>
            <!-- /bottom-page -->
          </div>
          <!-- /main-content -->
        </div>
        <!-- /section-content-right -->

      </div>
      <!-- /layout-wrap -->
    </div>
    <!-- /#page -->
  </div>
  <!-- /#wrapper -->

  <!-- Javascript -->
  <?php
  include_once('includes/footer-link.php');
  ?>

</body>

</html>