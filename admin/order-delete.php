<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']);

    if ($orderId > 0) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Delete order items first (due to foreign key constraint)
            $deleteItemsQuery = "DELETE FROM order_items WHERE order_id = ?";
            $deleteItemsStmt = $conn->prepare($deleteItemsQuery);
            $deleteItemsStmt->bind_param("i", $orderId);
            $deleteItemsStmt->execute();

            // Delete the order
            $deleteOrderQuery = "DELETE FROM orders WHERE id = ?";
            $deleteOrderStmt = $conn->prepare($deleteOrderQuery);
            $deleteOrderStmt->bind_param("i", $orderId);
            $deleteOrderStmt->execute();

            // Commit transaction
            $conn->commit();

            // Redirect back with success message
            header('Location: order-list.php?message=Order deleted successfully');
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();

            // Redirect back with error message
            header('Location: order-list.php?error=Failed to delete order: ' . $e->getMessage());
            exit();
        }
    }
}

// If we get here, something went wrong
header('Location: order-list.php?error=Invalid request');
exit();
?>