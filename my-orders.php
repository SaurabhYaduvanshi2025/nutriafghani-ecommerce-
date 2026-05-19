<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

require_customer_login('my-orders.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_price($value)
{
    return 'Rs. ' . number_format((float) $value, 2);
}

function format_date_time($value)
{
    return empty($value) ? 'N/A' : date('d M Y, h:i A', strtotime($value));
}

function product_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function payment_method_label($method)
{
    $method = strtolower((string) $method);

    if ($method === 'cod') {
        return 'Cash on Delivery';
    }

    if ($method === 'upi') {
        return 'UPI / Online';
    }

    return $method !== '' ? strtoupper($method) : 'N/A';
}

function ensure_customer_order_columns($conn)
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS `orders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_id` INT NOT NULL,
            `order_number` VARCHAR(50) NOT NULL UNIQUE,
            `total_amount` DECIMAL(10, 2) NOT NULL,
            `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            `shipping_address` TEXT,
            `payment_method` VARCHAR(50),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
            INDEX `idx_customer_id` (`customer_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS `order_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `product_id` INT NOT NULL,
            `variant_id` INT NULL,
            `quantity` INT NOT NULL,
            `price` DECIMAL(10, 2) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_product_id` (`product_id`),
            INDEX `idx_variant_id` (`variant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending'",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_name` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_email` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_phone` VARCHAR(20) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `state` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `postal_code` VARCHAR(20) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) NULL"
    ];

    foreach ($queries as $query) {
        $conn->query($query);
    }
}

ensure_customer_order_columns($conn);

$customerId = get_customer_id();
$selectedOrderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$orders = [];
$selectedOrder = null;
$selectedItems = [];

$ordersStmt = $conn->prepare("
    SELECT
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        COALESCE(o.payment_status, 'pending') AS payment_status,
        o.created_at,
        COALESCE(item_summary.total_quantity, 0) AS total_quantity,
        COALESCE(item_summary.item_count, 0) AS item_count
    FROM orders o
    LEFT JOIN (
        SELECT order_id, SUM(quantity) AS total_quantity, COUNT(*) AS item_count
        FROM order_items
        GROUP BY order_id
    ) item_summary ON item_summary.order_id = o.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
");
$ordersStmt->bind_param('i', $customerId);
$ordersStmt->execute();
$orders = $ordersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ordersStmt->close();

if ($selectedOrderId > 0) {
    $orderStmt = $conn->prepare("
        SELECT
            o.*,
            COALESCE(o.payment_status, 'pending') AS payment_status
        FROM orders o
        WHERE o.id = ? AND o.customer_id = ?
        LIMIT 1
    ");
    $orderStmt->bind_param('ii', $selectedOrderId, $customerId);
    $orderStmt->execute();
    $selectedOrder = $orderStmt->get_result()->fetch_assoc();
    $orderStmt->close();

    if ($selectedOrder) {
        $itemsStmt = $conn->prepare("
            SELECT
                oi.*,
                p.name AS product_name,
                p.slug AS product_slug,
                p.main_image,
                pv.weight_label,
                pv.weight_value
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        $itemsStmt->bind_param('i', $selectedOrderId);
        $itemsStmt->execute();
        $selectedItems = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $itemsStmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>My Orders - Nutri Afghan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .orders-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
        }

        .order-card,
        .order-detail-panel {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 22px;
            background: #fff;
        }

        .order-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 16px;
        }

        .order-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            color: #666;
            margin-top: 8px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            background: #f3f5ee;
            color: #4f7a1f;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .order-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin: 18px 0 24px;
        }

        .detail-box {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 14px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 76px minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 16px 0;
            border-top: 1px solid #eee;
        }

        .order-item img {
            width: 76px;
            height: 76px;
            object-fit: cover;
            border-radius: 6px;
        }

        @media (max-width: 575px) {
            .order-card,
            .order-item {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="preload-wrapper popup-loader">
    <?php include_once('includes/scroll-top.php'); ?>
    <?php include_once('includes/preloader.php'); ?>

    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>

        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container">
                <h3 class="heading text-center">My Orders</h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>My Orders</li>
                </ul>
            </div>
        </div>

        <section class="flat-spacing">
            <div class="container">
                <div class="orders-layout">
                    <?php if ($selectedOrder): ?>
                        <div class="order-detail-panel">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <h4>Order #<?php echo e($selectedOrder['order_number']); ?></h4>
                                    <p class="text-secondary mb-0"><?php echo format_date_time($selectedOrder['created_at']); ?></p>
                                </div>
                                <a href="my-orders.php" class="btn-line">Back to all orders</a>
                            </div>

                            <div class="order-detail-grid">
                                <div class="detail-box">
                                    <div class="text-caption-1 text-secondary">Total</div>
                                    <div class="text-title"><?php echo format_price($selectedOrder['total_amount']); ?></div>
                                </div>
                                <div class="detail-box">
                                    <div class="text-caption-1 text-secondary">Order Status</div>
                                    <div class="status-pill"><?php echo e($selectedOrder['status']); ?></div>
                                </div>
                                <div class="detail-box">
                                    <div class="text-caption-1 text-secondary">Payment</div>
                                    <div class="text-title"><?php echo e(payment_method_label($selectedOrder['payment_method'])); ?></div>
                                    <div class="text-caption-1 text-secondary"><?php echo e(ucfirst($selectedOrder['payment_status'])); ?></div>
                                </div>
                                <div class="detail-box">
                                    <div class="text-caption-1 text-secondary">Shipping Address</div>
                                    <div class="text-caption-1"><?php echo e($selectedOrder['shipping_address']); ?></div>
                                </div>
                            </div>

                            <h5>Order Items</h5>
                            <?php foreach ($selectedItems as $item): ?>
                                <?php
                                    $itemTotal = (float) $item['price'] * (int) $item['quantity'];
                                    $productName = $item['product_name'] ?: 'Deleted product';
                                    $weightLabel = $item['weight_label'] ?: $item['weight_value'];
                                ?>
                                <div class="order-item">
                                    <img src="<?php echo e(product_image_path($item['main_image'])); ?>" alt="<?php echo e($productName); ?>">
                                    <div>
                                        <?php if (!empty($item['product_slug'])): ?>
                                            <a href="product-detail.php?slug=<?php echo urlencode($item['product_slug']); ?>" class="text-title link"><?php echo e($productName); ?></a>
                                        <?php else: ?>
                                            <div class="text-title"><?php echo e($productName); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($weightLabel)): ?>
                                            <div class="text-caption-1 text-secondary"><?php echo e($weightLabel); ?></div>
                                        <?php endif; ?>
                                        <div class="text-caption-1 text-secondary">Qty: <?php echo (int) $item['quantity']; ?> x <?php echo format_price($item['price']); ?></div>
                                    </div>
                                    <div class="text-title"><?php echo format_price($itemTotal); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($selectedOrderId > 0): ?>
                        <div class="order-detail-panel text-center">
                            <h4>Order not found</h4>
                            <p class="text-secondary">This order is unavailable or does not belong to your account.</p>
                            <a href="my-orders.php" class="tf-btn btn-fill">Back to My Orders</a>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h4 class="mb_24">Order History</h4>
                        <?php if (empty($orders)): ?>
                            <div class="order-detail-panel text-center">
                                <h5>No orders yet</h5>
                                <p class="text-secondary">Your orders will appear here after checkout.</p>
                                <a href="shop.php" class="tf-btn btn-fill">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h5 class="mb-0">#<?php echo e($order['order_number']); ?></h5>
                                            <span class="status-pill"><?php echo e($order['status']); ?></span>
                                        </div>
                                        <div class="order-meta">
                                            <span><?php echo format_date_time($order['created_at']); ?></span>
                                            <span><?php echo (int) $order['total_quantity']; ?> item(s)</span>
                                            <span><?php echo e(payment_method_label($order['payment_method'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="text-md-end">
                                        <div class="text-title mb_8"><?php echo format_price($order['total_amount']); ?></div>
                                        <a href="my-orders.php?order_id=<?php echo (int) $order['id']; ?>" class="btn-line">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php include_once('includes/footer.php'); ?>
        <?php include_once('includes/bottom-toolbar.php'); ?>
    </div>

    <?php include_once('includes/auto-popup.php'); ?>
    <?php include_once('includes/search-bar.php'); ?>
    <?php include_once('includes/mobile-menu.php'); ?>
    <?php include_once('includes/shopping-cart.php'); ?>
    <?php include_once('includes/wishlist-bar.php'); ?>
    <?php include_once('includes/footer-link.php'); ?>
</body>
</html>
