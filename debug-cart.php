<?php
require_once('includes/customer-auth.php');

if (!is_customer_logged_in()) {
    die('Not logged in!');
}

require_once('config/db.php');

$customerId = get_customer_id();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Cart</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Cart Debug Information</h2>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5>Session Information</h5>
                <p><strong>Customer ID:</strong> <?php echo $customerId; ?></p>
                <p><strong>Customer Email:</strong> <?php echo get_customer_email(); ?></p>
                <p><strong>Customer Name:</strong> <?php echo get_customer_name(); ?></p>
                <p><strong>Logged In:</strong> <?php echo is_customer_logged_in() ? 'YES' : 'NO'; ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5>Database Cart Items</h5>
                <?php
                $query = "
                    SELECT
                        ci.id,
                        ci.quantity,
                        ci.price,
                        p.id as product_id,
                        p.name as product_name,
                        p.slug as product_slug,
                        pv.id as variant_id,
                        pv.weight_label
                    FROM cart_items ci
                    LEFT JOIN products p ON ci.product_id = p.id
                    LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                    WHERE ci.customer_id = ?
                    ORDER BY ci.created_at DESC
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $customerId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    echo '<p class="alert alert-info">No items in cart</p>';
                } else {
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>';
                    
                    $totalAmount = 0;
                    while ($item = $result->fetch_assoc()) {
                        $itemTotal = $item['quantity'] * $item['price'];
                        $totalAmount += $itemTotal;
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
                        echo '<td>' . $item['quantity'] . '</td>';
                        echo '<td>Rs. ' . number_format($item['price'], 2) . '</td>';
                        echo '<td>Rs. ' . number_format($itemTotal, 2) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                    echo '<p><strong>Total Amount: Rs. ' . number_format($totalAmount, 2) . '</strong></p>';
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>API Response Test</h5>
                <button class="btn btn-primary" onclick="testCartAPI()">Test Cart API</button>
                <div id="response" class="mt-3" style="white-space: pre-wrap; background: #f5f5f5; padding: 10px; border-radius: 4px;"></div>
            </div>
        </div>

        <div class="mt-4">
            <a href="checkout.php" class="btn btn-warning">Go to Checkout</a>
            <a href="cart.php" class="btn btn-info">Go to Cart</a>
        </div>
    </div>

    <script>
        function testCartAPI() {
            fetch('cart-process.php', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.text())
            .then(text => {
                document.getElementById('response').textContent = text;
            })
            .catch(error => {
                document.getElementById('response').textContent = 'Error: ' + error;
            });
        }
    </script>
</body>
</html>
