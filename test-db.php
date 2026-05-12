<?php
require_once('config/db.php');

echo "<h1>Database Tables Check</h1>";

// Check if tables exist
$tables = ['cart_items', 'orders', 'order_items'];
$allExist = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        $allExist = false;
    }
}

if (!$allExist) {
    echo "<p><a href='config/setup-db.php'>Click here to create missing tables</a></p>";
} else {
    echo "<p style='color: green;'>All tables are ready!</p>";
}
?>