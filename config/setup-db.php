<?php
/**
 * Database Setup Script
 * Creates all necessary tables for the Nutri Afghan e-commerce system
 */

require_once('db.php');

try {
    echo "<h1>🛒 Nutri Afghan Database Setup</h1>";
    echo "<p>Creating tables for cart and order functionality...</p>";

    // Read the schema file
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip comments and empty statements
        }

        try {
            if ($conn->query($statement) === TRUE) {
                $successCount++;
                echo "<p style='color: green;'>✓ Statement executed successfully</p>";
            } else {
                $errorCount++;
                echo "<p style='color: orange;'>⚠ Statement failed: " . $conn->error . "</p>";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }

    echo "<h2>Setup Complete</h2>";
    echo "<p>Successfully executed: <strong>$successCount</strong> statements</p>";
    if ($errorCount > 0) {
        echo "<p>Errors: <strong>$errorCount</strong> statements</p>";
    }

    // Verify tables exist
    echo "<h3>Verifying Tables:</h3>";
    $tables = ['categories', 'products', 'product_variants', 'customers', 'cart_items', 'orders', 'order_items'];

    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='../admin/'>← Back to Admin Panel</a></p>";
?>