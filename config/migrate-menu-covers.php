<?php
require_once(__DIR__ . '/db.php');

$column = $conn->query("SHOW COLUMNS FROM menu_items LIKE 'cover_image'");
if (!$column || $column->num_rows === 0) {
    if (!$conn->query("ALTER TABLE menu_items ADD COLUMN cover_image VARCHAR(255) NULL AFTER url")) {
        fwrite(STDERR, "Migration failed: {$conn->error}\n");
        exit(1);
    }
}

echo "Navigation menu cover migration completed.\n";
