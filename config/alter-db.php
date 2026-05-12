<?php
/**
 * Database Alter Script
 * Removes country and state columns from the customers table
 */

require_once('db.php');

try {
    // Check if columns exist before dropping them
    $result = $conn->query("DESCRIBE customers");
    $columns = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    $dropColumns = [];
    
    if (in_array('state', $columns)) {
        $dropColumns[] = 'state';
    }
    
    if (in_array('country', $columns)) {
        $dropColumns[] = 'country';
    }

    if (!empty($dropColumns)) {
        foreach ($dropColumns as $column) {
            $alterSql = "ALTER TABLE `customers` DROP COLUMN `" . $conn->real_escape_string($column) . "`";
            if ($conn->query($alterSql) === TRUE) {
                echo "<p style='color: green;'>✓ Successfully removed column: <strong>" . htmlspecialchars($column) . "</strong></p>";
            } else {
                echo "<p style='color: red;'>✗ Error removing column <strong>" . htmlspecialchars($column) . "</strong>: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: blue;'>ℹ No columns to remove. The customers table is already updated.</p>";
    }

    // Show updated table structure
    echo "<h3>Updated Customers Table Structure:</h3>";
    $result = $conn->query("DESCRIBE customers");
    if ($result) {
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
    echo "<h3 style='color: green;'>✓ Database Update Complete!</h3>";
    echo "<p>The customers table has been updated. Country and State fields have been removed.</p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Update - Nutri Afghan</title>
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
