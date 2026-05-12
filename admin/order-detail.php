<?php
require_once('auth.php');
require_once('../config/db.php');

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;

if ($orderId > 0) {
    $query = "
        SELECT
            o.*,
            c.first_name,
            c.last_name,
            c.email,
            c.phone
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        // Get order items
        $itemsQuery = "
            SELECT
                oi.*,
                p.name as product_name,
                p.main_image,
                pv.weight_label
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id = ?
        ";

        $itemsStmt = $conn->prepare($itemsQuery);
        $itemsStmt->bind_param("i", $orderId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();

        $order['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $order['items'][] = $item;
        }
    }
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
    <title>Order Details - Admin Panel - Nutri Afghan</title>

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
                                    <h3>Order <?php echo $order ? '#' . $order['order_number'] : 'Not Found'; ?></h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li>
                                            <a href="./"><div class="text-tiny">Dashboard</div></a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <a href="order-list.php"><div class="text-tiny">Order</div></a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Order detail</div>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Order <?php echo $order ? '#' . $order['order_number'] : 'Not Found'; ?></div>
                                        </li>
                                    </ul>
                                </div>

                                <?php if (!$order): ?>
                                <div class="alert alert-danger mb-4">
                                    Order not found.
                                </div>
                                <a href="order-list.php" class="btn btn-primary">Back to Orders</a>
                                <?php else: ?>

                                <!-- order-detail -->
                                <div class="wg-order-detail">
                                    <div class="left flex-grow">
                                        <div class="wg-box mb-20">
                                            <div class="wg-table table-order-detail">
                                                <ul class="table-title flex items-center justify-between gap20 mb-24">
                                                    <li>
                                                        <div class="body-title">All items</div>
                                                    </li>
                                                </ul>
                                                <ul class="flex flex-column">
                                                    <?php foreach ($order['items'] as $item): ?>
                                                    <li class="product-item gap14">
                                                        <div class="image no-bg">
                                                            <img src="../uploads/products/<?php echo htmlspecialchars($item['main_image'] ?: 'default.jpg'); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover;">
                                                        </div>
                                                        <div class="flex items-center justify-between gap40 flex-grow">
                                                            <div class="name">
                                                                <div class="text-tiny mb-1">Product name</div>
                                                                <a href="../product-detail.php?id=<?php echo $item['product_id']; ?>" class="body-title-2"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                                                <?php if ($item['weight_label']): ?>
                                                                <div class="text-tiny text-muted"><?php echo htmlspecialchars($item['weight_label']); ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="name">
                                                                <div class="text-tiny mb-1">Quantity</div>
                                                                <div class="body-title-2"><?php echo $item['quantity']; ?></div>
                                                            </div>
                                                            <div class="name">
                                                                <div class="text-tiny mb-1">Price</div>
                                                                <div class="body-title-2">Rs. <?php echo number_format($item['price'], 2); ?></div>
                                                            </div>
                                                            <div class="name">
                                                                <div class="text-tiny mb-1">Total</div>
                                                                <div class="body-title-2">Rs. <?php echo number_format($item['quantity'] * $item['price'], 2); ?></div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="wg-box">
                                            <div class="wg-table table-cart-totals">
                                                <ul class="table-title flex mb-24">
                                                    <li>
                                                        <div class="body-title">Cart Totals</div>
                                                    </li>
                                                    <li>
                                                        <div class="body-title">Price</div>
                                                    </li>
                                                </ul>
                                                <ul class="flex flex-column gap14">
                                                    <li class="cart-totals-item">
                                                        <span class="body-title">Total price:</span>
                                                        <span class="body-title tf-color-1">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="right">
                                        <div class="wg-box mb-20 gap10">
                                            <div class="body-title">Summary</div>
                                            <div class="summary-item">
                                                <div class="body-text">Order ID</div>
                                                <div class="body-title-2">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="body-text">Date</div>
                                                <div class="body-title-2"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="body-text">Status</div>
                                                <div class="body-title-2">
                                                    <span class="badge <?php
                                                        switch(strtolower($order['status'])) {
                                                            case 'pending': echo 'badge-warning'; break;
                                                            case 'processing': echo 'badge-info'; break;
                                                            case 'shipped': echo 'badge-primary'; break;
                                                            case 'delivered': echo 'badge-success'; break;
                                                            case 'cancelled': echo 'badge-danger'; break;
                                                            default: echo 'badge-secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="body-text">Total</div>
                                                <div class="body-title-2 tf-color-1">Rs. <?php echo number_format($order['total_amount'], 2); ?></div>
                                            </div>
                                        </div>
                                        <div class="wg-box mb-20 gap10">
                                            <div class="body-title">Customer Information</div>
                                            <div class="body-text"><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                            <div class="body-text"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></div>
                                            <div class="body-text"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></div>
                                        </div>
                                        <div class="wg-box mb-20 gap10">
                                            <div class="body-title">Shipping Address</div>
                                            <div class="body-text" style="white-space: pre-line;"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                                        </div>
                                        <div class="wg-box mb-20 gap10">
                                            <div class="body-title">Payment Method</div>
                                            <div class="body-text"><?php echo htmlspecialchars($order['payment_method'] ?: 'N/A'); ?></div>
                                        </div>
                                        <div class="wg-box gap10">
                                            <div class="body-title">Actions</div>
                                            <a class="tf-button style-1 w-full mb-2" href="order-edit.php?id=<?php echo $order['id']; ?>"><i class="icon-edit"></i>Edit Order</a>
                                            <a class="tf-button style-2 w-full" href="order-list.php"><i class="icon-arrow-left"></i>Back to Orders</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- /order-detail -->

                                <?php endif; ?>
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