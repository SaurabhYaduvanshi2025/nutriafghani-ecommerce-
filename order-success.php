<?php
require_once('includes/customer-auth.php');
require_customer_login('order-success.php');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Order Success - Nutri Afghan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <?php include_once('includes/header-link.php'); ?>
</head>
<body class="preload-wrapper popup-loader">
    <?php include_once('includes/scroll-top.php'); ?>
    <?php include_once('includes/preloader.php'); ?>

    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>

        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container-full">
                <div class="row">
                    <div class="col-12">
                        <h3 class="heading text-center">Order Successful</h3>
                    </div>
                </div>
            </div>
        </div>

        <section>
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="flat-spacing tf-page-checkout" style="text-align: center; padding: 60px 0;">
                            <div class="success-icon" style="font-size: 60px; margin-bottom: 20px;">
                                ✓
                            </div>
                            <h2>Thank You for Your Order!</h2>
                            <p class="text-secondary" style="margin: 20px 0;">Your order has been successfully placed and confirmed.</p>
                            
                            <?php
                            if (isset($_GET['order_id'])) {
                                $orderId = intval($_GET['order_id']);
                                $customerId = get_customer_id();
                                
                                require_once('config/db.php');
                                
                                $query = "
                                    SELECT
                                        o.order_number,
                                        o.total_amount,
                                        o.payment_method,
                                        o.payment_status,
                                        o.status,
                                        o.shipping_address,
                                        o.created_at
                                    FROM orders o
                                    WHERE o.id = ? AND o.customer_id = ?
                                ";
                                
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $orderId, $customerId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    $order = $result->fetch_assoc();
                                    ?>
                                    <div style="background: #f5f5f5; padding: 30px; border-radius: 8px; margin: 30px 0; text-align: left;">
                                        <div style="margin-bottom: 15px;">
                                            <strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Order Total:</strong> Rs. <?php echo number_format($order['total_amount'], 2); ?>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Payment Method:</strong> <?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'UPI/Card'; ?>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Payment Status:</strong> <span style="color: green;"><?php echo ucfirst($order['payment_status']); ?></span>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Order Status:</strong> <?php echo ucfirst($order['status']); ?>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <strong>Order Date:</strong> <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <div style="margin-top: 40px;">
                                <p class="text-secondary">You will receive an email confirmation shortly.</p>
                                <p class="text-secondary">Your order will be processed and shipped soon.</p>
                            </div>

                            <div style="margin-top: 40px;">
                                <a href="shop.php" class="tf-btn btn-fill" style="margin-right: 10px;">Continue Shopping</a>
                                <a href="./" class="tf-btn btn-white">Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include_once('includes/footer.php'); ?>
        <?php include_once('includes/bottom-toolbar.php'); ?>
    </div>

    <?php include_once('includes/shopping-cart.php'); ?>
    <?php include_once('includes/footer-link.php'); ?>
</body>
</html>
