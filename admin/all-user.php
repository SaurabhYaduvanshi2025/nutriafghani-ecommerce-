<?php
require_once('auth.php');
require_once('../config/db.php');

// Fetch all registered customers
$customers = [];
$customersStmt = $conn->prepare("
    SELECT id, first_name, last_name, email, phone, is_active, created_at, last_login 
    FROM customers 
    ORDER BY created_at DESC
");

if ($customersStmt) {
    $customersStmt->execute();
    $customersResult = $customersStmt->get_result();
    $customers = $customersResult->fetch_all(MYSQLI_ASSOC);
    $customersStmt->close();
}

function formatDate($date) {
    if (empty($date)) {
        return 'Never';
    }
    return date('M d, Y', strtotime($date));
}
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
                                    <h3>All Registered Customers</h3>
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
                                            <a href="#">
                                                <div class="text-tiny">Customers</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">All Customers</div>
                                        </li>
                                    </ul>
                                </div>
                                <!-- all-user -->
                                <div class="wg-box">
                                    <div class="wg-table table-all-user">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Full Name</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Email</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Phone</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Registered</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Last Login</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Status</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Action</div>
                                            </li>
                                        </ul>
                                        <ul class="flex flex-column">
                                            <?php if (!empty($customers)): ?>
                                                <?php foreach ($customers as $customer): ?>
                                                    <li class="user-item gap14">
                                                        <div class="image">
                                                            <img src="images/user.png" alt="Customer Avatar">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="#" class="body-title-2"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></a>
                                                                <p class="text-tiny" style="color: #666; margin-top: 5px;">Customer</p>
                                                            </div>
                                                            <div class="body-text"><?php echo htmlspecialchars($customer['email']); ?></div>
                                                            <div class="body-text"><?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A'; ?></div>
                                                            <div class="body-text"><?php echo formatDate($customer['created_at']); ?></div>
                                                            <div class="body-text"><?php echo formatDate($customer['last_login']); ?></div>
                                                            <div class="body-text">
                                                                <span style="background-color: <?php echo $customer['is_active'] ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $customer['is_active'] ? '#155724' : '#721c24'; ?>; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                                                    <?php echo $customer['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </div>
                                                            <div class="list-icon-function">
                                                                <a href="#" title="View">
                                                                    <div class="item eye">
                                                                        <i class="icon-eye"></i>
                                                                    </div>
                                                                </a>
                                                                <a href="#" title="Edit">
                                                                    <div class="item edit">
                                                                        <i class="icon-edit-3"></i>
                                                                    </div>
                                                                </a>
                                                                <a href="#" title="Delete">
                                                                    <div class="item trash">
                                                                        <i class="icon-trash-2"></i>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li class="user-item gap14">
                                                    <div class="flex items-center justify-center gap20 flex-grow" style="padding: 20px; text-align: center; color: #999;">
                                                        <p>No registered customers found</p>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10">
                                        <div class="text-tiny">Showing <?php echo count($customers); ?> registered customer(s)</div>
                                        <ul class="wg-pagination">
                                            <li>
                                                <a href="#"><i class="icon-chevron-left"></i></a>
                                            </li>
                                            <li>
                                                <a href="#">1</a>
                                            </li>
                                            <li class="active">
                                                <a href="#">2</a>
                                            </li>
                                            <li>
                                                <a href="#">3</a>
                                            </li>
                                            <li>
                                                <a href="#"><i class="icon-chevron-right"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- /all-user -->
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