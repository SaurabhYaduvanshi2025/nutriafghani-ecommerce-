<?php
/**
 * Menu Helper Functions
 * Provides functions for menu management
 */

/**
 * Get all menu items organized by parent
 */
function getMenuItems($conn, $onlyActive = true) {
    $query = "SELECT * FROM menu_items";
    if ($onlyActive) {
        $query .= " WHERE is_active = 1";
    }
    $query .= " ORDER BY parent_id, sort_order ASC";
    
    $result = $conn->query($query);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * Get menu item by ID
 */
function getMenuItemById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get parent menu items (for dropdown)
 */
function getParentMenuItems($conn, $excludeId = null) {
    $query = "SELECT id, label FROM menu_items WHERE parent_id IS NULL AND menu_type = 'main' ORDER BY sort_order ASC";
    $result = $conn->query($query);
    
    if ($result) {
        $items = $result->fetch_all(MYSQLI_ASSOC);
        if ($excludeId) {
            $items = array_filter($items, function($item) use ($excludeId) {
                return $item['id'] !== $excludeId;
            });
        }
        return $items;
    }
    return [];
}

/**
 * Get child menu items for a parent
 */
function getChildMenuItems($conn, $parentId) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE parent_id = ? ORDER BY sort_order ASC");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Delete menu item and its children
 */
function deleteMenuItem($conn, $id) {
    try {
        // First delete all children
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE parent_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Then delete the item itself
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return true;
    } catch (Exception $e) {
        error_log("Error deleting menu item: " . $e->getMessage());
        return false;
    }
}

/**
 * Update menu item sort order
 */
function updateMenuItemSortOrder($conn, $id, $sortOrder) {
    $stmt = $conn->prepare("UPDATE menu_items SET sort_order = ? WHERE id = ?");
    $stmt->bind_param("ii", $sortOrder, $id);
    return $stmt->execute();
}
