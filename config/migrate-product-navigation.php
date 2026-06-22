<?php
/**
 * One-time, idempotent migration from category-based products to direct menu placement.
 * Existing products inherit the navigation menu previously assigned to their category.
 */
require_once(__DIR__ . '/db.php');

function column_exists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function drop_column_foreign_keys(mysqli $conn, string $table, string $column): void
{
    $stmt = $conn->prepare("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $constraints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($constraints as $constraint) {
        $name = str_replace('`', '``', $constraint['CONSTRAINT_NAME']);
        $conn->query("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
    }
}

try {
    if (!column_exists($conn, 'products', 'menu_id')) {
        $conn->query("ALTER TABLE products ADD COLUMN menu_id INT NULL AFTER id, ADD INDEX idx_menu_id (menu_id)");
    }

    if (column_exists($conn, 'products', 'category_id') && column_exists($conn, 'categories', 'menu_id')) {
        $conn->query("UPDATE products p JOIN categories c ON c.id = p.category_id SET p.menu_id = c.menu_id WHERE p.menu_id IS NULL");
    }

    foreach ([['products', 'category_id'], ['menu_items', 'category_id']] as [$table, $column]) {
        if (column_exists($conn, $table, $column)) {
            drop_column_foreign_keys($conn, $table, $column);
            $conn->query("ALTER TABLE `$table` DROP COLUMN `$column`");
        }
    }

    if (column_exists($conn, 'products', 'category_slug')) {
        $conn->query("ALTER TABLE products DROP COLUMN category_slug");
    }

    $conn->query("DROP TABLE IF EXISTS categories");
    echo "Product navigation migration completed.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: " . $exception->getMessage() . "\n");
    exit(1);
}
