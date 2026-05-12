<?php
require_once('auth.php');
require_once('../config/db.php');

// Fetch orders with customer and order details
$query = "
    SELECT
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.shipping_address,
        o.payment_method,
        o.created_at,
        c.first_name,
        c.last_name,
        c.email,
        c.phone,
        GROUP_CONCAT(
            CONCAT(p.name, ' (', COALESCE(pv.weight_label, 'Standard'), ') x ', oi.quantity, ' @ Rs.', oi.price)
            SEPARATOR '; '
        ) as order_items,
        SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_variants pv ON oi.variant_id = pv.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$result = $conn->query($query);
$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
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
                                    <h3>Order List</h3>
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
                                                <div class="text-tiny">Order</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Order List</div>
                                        </li>
                                    </ul>
                                </div>

                                <?php if (isset($_GET['message'])): ?>
                                <div class="alert alert-success mb-3">
                                    <?php echo htmlspecialchars($_GET['message']); ?>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mb-3">
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                </div>
                                <?php endif; ?>

                                <!-- order-list -->
                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Search here..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" required="">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="wg-table table-all-category">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Order ID</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Customer Details</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Order Items</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Total Amount</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Quantity</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Payment Method</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Shipping Address</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Order Date</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Status</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Action</div>
                                            </li>
                                        </ul>
                                        <ul class="flex flex-column">
                                            <?php if (empty($orders)): ?>
                                            <li class="product-item gap14">
                                                <div class="flex items-center justify-center w-full py-4">
                                                    <div class="body-text text-muted">No orders found</div>
                                                </div>
                                            </li>
                                            <?php else: ?>
                                                <?php foreach ($orders as $order): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="body-text">#<?php echo htmlspecialchars($order['order_number']); ?></div>

                                                        <div class="customer-info">
                                                            <div class="body-title-2"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                                            <div class="body-text text-small"><?php echo htmlspecialchars($order['email']); ?></div>
                                                            <div class="body-text text-small"><?php echo htmlspecialchars($order['phone']); ?></div>
                                                        </div>

                                                        <div class="order-items">
                                                            <div class="body-text">
                                                                <?php
                                                                $items = explode('; ', $order['order_items']);
                                                                foreach ($items as $item) {
                                                                    echo htmlspecialchars($item) . '<br>';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>

                                                        <div class="body-text">Rs. <?php echo number_format($order['total_amount'], 2); ?></div>
                                                        <div class="body-text"><?php echo htmlspecialchars($order['total_quantity']); ?></div>
                                                        <div class="body-text"><?php echo htmlspecialchars($order['payment_method'] ?: 'N/A'); ?></div>

                                                        <div class="body-text shipping-address">
                                                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                                        </div>

                                                        <div class="body-text"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>

                                                        <div>
                                                            <?php
                                                            $statusClass = '';
                                                            switch (strtolower($order['status'])) {
                                                                case 'pending':
                                                                    $statusClass = 'block-pending';
                                                                    break;
                                                                case 'processing':
                                                                    $statusClass = 'block-processing';
                                                                    break;
                                                                case 'shipped':
                                                                    $statusClass = 'block-shipped';
                                                                    break;
                                                                case 'delivered':
                                                                    $statusClass = 'block-available';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusClass = 'block-cancelled';
                                                                    break;
                                                                default:
                                                                    $statusClass = 'block-pending';
                                                            }
                                                            ?>
                                                            <div class="<?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></div>
                                                        </div>

                                                        <div class="list-icon-function">
                                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>">
                                                                <div class="item eye">
                                                                    <i class="icon-eye"></i>
                                                                </div>
                                                            </a>
                                                            <a href="order-edit.php?id=<?php echo $order['id']; ?>">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                            <a href="#" onclick="deleteOrder(<?php echo $order['id']; ?>)">
                                                                <div class="item trash">
                                                                    <i class="icon-trash-2"></i>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                                                <i class="icon-trash-2"></i>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="product-item gap14">
                                                <div class="image no-bg">
                                                    <img src="images/demo_img.jpg" alt="">
                                                </div>
                                                <div class="flex items-center justify-between gap20 flex-grow">
                                                    <div class="name">
                                                        <a href="product-detail.php" class="body-title-2">Premium Jumbo
                                                            Pishta Giri</a>
                                                    </div>
                                                    <div class="body-text">#5123095</div>
                                                    <div class="body-text">₹999.00</div>
                                                    <div class="body-text">1</div>
                                                    <div class="body-text">Single</div>
                                                    <div class="body-text">COD</div>
                                                    <div class="body-text">R-Z 52-B, A3-block Dharampura Najafgarh, New
                                                        Delhi-110043</div>
                                                    <div class="body-text">26/12/2025</div>
                                                    <div>
                                                        <div class="block-not-available">Cancel</div>
                                                    </div>
                                                    <div class="list-icon-function">
                                                        <a href="#">
                                                            <div class="item eye">
                                                                <i class="icon-eye"></i>
                                                            </div>
                                                        </a>
                                                        <a href="#">
                                                            <div class="item edit">
                                                                <i class="icon-edit-3"></i>
                                                            </div>
                                                        </a>
                                                        <a href="#">
                                                            <div class="item trash">
                                                                <i class="icon-trash-2"></i>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="product-item gap14">
                                                <div class="image no-bg">
                                                    <img src="images/demo_img.jpg" alt="">
                                                </div>
                                                <div class="flex items-center justify-between gap20 flex-grow">
                                                    <div class="name">
                                                        <a href="product-detail.php" class="body-title-2">Premium afghan
                                                            anjeer super jumbo (Dry Fig)</a>
                                                    </div>
                                                    <div class="body-text">#5123095</div>
                                                    <div class="body-text">₹999.00</div>
                                                    <div class="body-text">2</div>
                                                    <div class="body-text">Bulk</div>
                                                    <div class="body-text">COD</div>
                                                    <div class="body-text">R-Z 52-B, A3-block Dharampura Najafgarh, New
                                                        Delhi-110043</div>
                                                    <div class="body-text">26/12/2025</div>
                                                    <div>
                                                        <div class="block-tracking">Tracking</div>
                                                    </div>
                                                    <div class="list-icon-function">
                                                        <a href="#">
                                                            <div class="item eye">
                                                                <i class="icon-eye"></i>
                                                            </div>
                                                        </a>
                                                        <a href="#">
                                                            <div class="item edit">
                                                                <i class="icon-edit-3"></i>
                                                            </div>
                                                        </a>
                                                        <a href="#">
                                                            <div class="item trash">
                                                                <i class="icon-trash-2"></i>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10">
                                        <div class="text-tiny">Showing 10 entries</div>
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
                                <!-- /order-list -->
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

    <script>
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                // Create form to submit delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'order-delete.php';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_id';
                input.value = orderId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <style>
        .customer-info {
            min-width: 150px;
        }

        .order-items {
            min-width: 200px;
            max-width: 250px;
        }

        .shipping-address {
            min-width: 200px;
            max-width: 250px;
        }

        .text-small {
            font-size: 0.875rem;
            color: #666;
        }

        .text-muted {
            color: #999;
        }

        .block-processing {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
        }

        .block-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
        }

        .block-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
        }

        .block-tracking {
            background-color: #e2e3e5;
            color: #383d41;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
        }

        .product-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .flex.items-center.justify-between {
            align-items: flex-start;
        }

        .flex.items-center.justify-between > div {
            flex: 1;
            padding: 0 10px;
        }

        .flex.items-center.justify-between > div:first-child {
            flex: 0 0 100px;
        }

        .flex.items-center.justify-between > div:last-child {
            flex: 0 0 120px;
        }
    </style>

</body>

</html>