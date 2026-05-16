<?php
/**
 * Payment Processing Script
 * Handles Razorpay payments and order creation
 */

require_once('config/db.php');
require_once('includes/customer-auth.php');

// Require customer login
require_customer_login();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'order_id' => null,
    'order_number' => null
];

function ensure_payment_tables_exist($conn)
{
    $queries = [
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending'",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `razorpay_payment_id` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `razorpay_order_id` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_name` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_email` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_phone` VARCHAR(20) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `state` VARCHAR(100) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `postal_code` VARCHAR(20) NULL",
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) NULL"
    ];

    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            error_log("Warning: " . $conn->error);
        }
    }
}

try {
    ensure_payment_tables_exist($conn);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $customerId = get_customer_id();
    if (!$customerId) {
        throw new Exception('Customer login required');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_order':
            // Create initial order and get cart details
            $paymentMethod = $_POST['payment_method'] ?? '';
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $postalCode = trim($_POST['postal_code'] ?? '');
            $country = trim($_POST['country'] ?? '');

            if (!$paymentMethod || !in_array($paymentMethod, ['cod', 'upi'])) {
                throw new Exception('Invalid payment method');
            }

            if (empty($firstName) || empty($email) || empty($phone) || empty($address) || empty($city)) {
                throw new Exception('Missing required customer information');
            }

            // Get cart items
            $cartQuery = "
                SELECT
                    ci.id,
                    ci.product_id,
                    ci.variant_id,
                    ci.quantity,
                    ci.price,
                    p.stock_quantity as product_stock,
                    pv.stock_quantity as variant_stock,
                    p.name as product_name
                FROM cart_items ci
                LEFT JOIN products p ON ci.product_id = p.id
                LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                WHERE ci.customer_id = ?
                ORDER BY ci.created_at ASC
            ";

            $cartStmt = $conn->prepare($cartQuery);
            $cartStmt->bind_param("i", $customerId);
            $cartStmt->execute();
            $cartResult = $cartStmt->get_result();

            if ($cartResult->num_rows === 0) {
                throw new Exception('Your cart is empty');
            }

            $cartItems = [];
            $totalAmount = 0;

            while ($item = $cartResult->fetch_assoc()) {
                $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['product_stock'];

                if ($availableStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$item['product_name']}. Available: {$availableStock}, Requested: {$item['quantity']}");
                }

                $itemTotal = $item['quantity'] * $item['price'];
                $totalAmount += $itemTotal;
                $cartItems[] = $item;
            }

            // Create order
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . $customerId;
            $shippingAddress = "$address, $city, $state $postalCode, $country";

            $orderQuery = "
                INSERT INTO orders (
                    customer_id, order_number, total_amount, status, shipping_address,
                    payment_method, payment_status, customer_name, customer_email,
                    customer_phone, city, state, postal_code, country, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $orderStmt = $conn->prepare($orderQuery);
            $status = 'pending';
            $paymentStatus = 'pending';
            
            $orderStmt->bind_param(
                "isdsssssssssss",
                $customerId,
                $orderNumber,
                $totalAmount,
                $status,
                $shippingAddress,
                $paymentMethod,
                $paymentStatus,
                $firstName,
                $email,
                $phone,
                $city,
                $state,
                $postalCode,
                $country
            );

            if (!$orderStmt->execute()) {
                throw new Exception('Failed to create order: ' . $orderStmt->error);
            }

            $orderId = $orderStmt->insert_id;

            // Add order items
            $insertItemQuery = "INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)";
            $insertItemStmt = $conn->prepare($insertItemQuery);

            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $variantId = $item['variant_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                $insertItemStmt->bind_param("iiiid", $orderId, $productId, $variantId, $quantity, $price);
                if (!$insertItemStmt->execute()) {
                    throw new Exception('Failed to add order items: ' . $insertItemStmt->error);
                }
            }

            // Reduce stock
            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                if ($item['variant_id']) {
                    $updateVariantQuery = "UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?";
                    $updateVariantStmt = $conn->prepare($updateVariantQuery);
                    $updateVariantStmt->bind_param("ii", $quantity, $item['variant_id']);
                    $updateVariantStmt->execute();
                }

                $updateProductQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $updateProductStmt = $conn->prepare($updateProductQuery);
                $updateProductStmt->bind_param("ii", $quantity, $productId);
                $updateProductStmt->execute();
            }

            // Clear cart
            $clearCartQuery = "DELETE FROM cart_items WHERE customer_id = ?";
            $clearCartStmt = $conn->prepare($clearCartQuery);
            $clearCartStmt->bind_param("i", $customerId);
            $clearCartStmt->execute();

            $response['success'] = true;
            $response['order_id'] = $orderId;
            $response['order_number'] = $orderNumber;
            $response['total_amount'] = $totalAmount;
            $response['razorpay_amount'] = (int) round($totalAmount * 100);
            $response['customer_email'] = $email;
            $response['payment_method'] = $paymentMethod;
            $response['message'] = 'Order created successfully';

            break;

        case 'verify_payment':
            // Verify Razorpay payment
            $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
            $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
            $razorpaySignature = $_POST['razorpay_signature'] ?? '';
            $orderId = intval($_POST['order_id'] ?? 0);

            if (!$razorpayPaymentId || $orderId <= 0) {
                throw new Exception('Missing payment verification details');
            }

            if ($razorpayOrderId && $razorpaySignature && defined('RAZORPAY_KEY_SECRET') && RAZORPAY_KEY_SECRET !== '') {
                $generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, RAZORPAY_KEY_SECRET);

                if (!hash_equals($generatedSignature, $razorpaySignature)) {
                    error_log("Signature mismatch for order $orderId");
                    throw new Exception('Payment verification failed');
                }
            }

            // Update order
            $updateQuery = "
                UPDATE orders SET
                    payment_status = ?,
                    status = ?,
                    razorpay_payment_id = ?,
                    razorpay_order_id = ?
                WHERE id = ? AND customer_id = ?
            ";

            $paymentStatus = 'completed';
            $status = 'processing';

            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssssii", $paymentStatus, $status, $razorpayPaymentId, $razorpayOrderId, $orderId, $customerId);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update payment status: ' . $updateStmt->error);
            }

            $response['success'] = true;
            $response['message'] = 'Payment verified successfully';

            break;

        case 'cod_payment':
            // Handle Cash on Delivery
            $orderId = intval($_POST['order_id'] ?? 0);

            if ($orderId <= 0) {
                throw new Exception('Invalid order ID');
            }

            // Get order to verify it belongs to customer
            $orderCheckQuery = "SELECT id FROM orders WHERE id = ? AND customer_id = ?";
            $orderCheckStmt = $conn->prepare($orderCheckQuery);
            $orderCheckStmt->bind_param("ii", $orderId, $customerId);
            $orderCheckStmt->execute();
            $orderCheckResult = $orderCheckStmt->get_result();

            if ($orderCheckResult->num_rows === 0) {
                throw new Exception('Order not found');
            }

            // Update order status
            $updateQuery = "
                UPDATE orders SET
                    payment_status = ?,
                    status = ?
                WHERE id = ? AND customer_id = ?
            ";

            $paymentStatus = 'completed';
            $status = 'processing';

            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssii", $paymentStatus, $status, $orderId, $customerId);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to complete order: ' . $updateStmt->error);
            }

            $response['success'] = true;
            $response['message'] = 'Order confirmed. Payment will be collected on delivery';

            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("Payment process error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
