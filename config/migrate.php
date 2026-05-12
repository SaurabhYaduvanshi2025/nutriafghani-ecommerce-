<?php
/**
 * Database Migration Script
 * Creates the customers table for user authentication
 */

require_once('db.php');

try {
    // Create customers table
    $sql = "CREATE TABLE IF NOT EXISTS `customers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `phone` VARCHAR(20),
        `password_hash` VARCHAR(255) NOT NULL,
        `address` TEXT,
        `city` VARCHAR(100),
        `postal_code` VARCHAR(20),
        `is_active` TINYINT(1) DEFAULT 1,
        `last_login` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `idx_email` (`email`),
        INDEX `idx_active` (`is_active`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql) === TRUE) {
        echo "<h2 style='color: green;'>✓ Success: Customers table created successfully!</h2>";
        echo "<p>The database has been updated with the customers table for user authentication.</p>";
    } else {
        echo "<h2 style='color: orange;'>⚠ Notice: Table already exists or already created</h2>";
        echo "<p>The customers table was already present in the database.</p>";
    }

    // Check if table exists and show info
    $result = $conn->query("DESCRIBE customers");
    if ($result) {
        echo "<h3>Customers Table Structure:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin-top: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr style='margin: 30px 0;'>";
    echo "<h3>Database Migration Complete!</h3>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>Register a new customer account at <a href='customer-register.php'>Register</a></li>";
    echo "<li>Login with your account at <a href='customer-login.php'>Login</a></li>";
    echo "<li>Add products to cart (login required)</li>";
    echo "<li>Proceed to checkout (login required)</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Please ensure your database connection is working properly.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - Nutri Afghan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h2, h3 {
            color: #333;
        }
        table {
            background-color: white;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        hr {
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
</body>
</html>
