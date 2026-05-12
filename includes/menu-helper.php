<?php
/**
 * Menu Helper for Frontend
 * Provides functions to get dynamic menu items from database
 */

/**
 * Get navigation menu items from database
 */
function getNavigationMenu($conn) {
    try {
        $query = "
            SELECT id, parent_id, label, url, menu_type, sort_order
            FROM menu_items
            WHERE is_active = 1
            AND parent_id IS NULL
            AND menu_type = 'homepage'
            ORDER BY parent_id ASC, sort_order ASC
        ";
        
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $menus = $result->fetch_all(MYSQLI_ASSOC);
            $menuIds = array_map('intval', array_column($menus, 'id'));

            if (!empty($menuIds)) {
                $menuIdList = implode(',', $menuIds);
                $categoryResult = $conn->query("
                    SELECT id, menu_id, name, slug
                    FROM categories
                    WHERE is_active = 1
                    AND menu_id IN ($menuIdList)
                    ORDER BY name ASC
                ");
                $categoriesByMenu = [];

                if ($categoryResult) {
                    while ($category = $categoryResult->fetch_assoc()) {
                        $categoriesByMenu[(int) $category['menu_id']][] = $category;
                    }
                }

                foreach ($menus as &$menu) {
                    $menu['categories'] = $categoriesByMenu[(int) $menu['id']] ?? [];
                }
                unset($menu);
            }

            return $menus;
        }
    } catch (Exception $e) {
        error_log("Error getting menu items: " . $e->getMessage());
    }
    
    return [];
}

/**
 * Build navigation HTML from menu items
 */
function buildNavigationHTML($menuItems) {
    $html = '';
    
    $html .= '<li class="menu-item"><a href="./" class="item-link">Home</a></li>';
    $html .= '<li class="menu-item"><a href="about-us.php" class="item-link">About Us</a></li>';
    
    foreach ($menuItems as $mainItem) {
        $label = trim((string) $mainItem['label']);
        if ($label === '' || in_array(strtolower($label), ['home', 'about us', 'contact us'], true)) {
            continue;
        }
        
        $categories = isset($mainItem['categories']) && is_array($mainItem['categories']) ? $mainItem['categories'] : [];
        $hasCategories = count($categories) > 0;
        $url = $hasCategories ? '#' : ($mainItem['url'] ? $mainItem['url'] : '#');
        $html .= '<li class="menu-item' . ($hasCategories ? ' position-relative' : '') . '">';
        $html .= '<a href="' . htmlspecialchars($url) . '" class="item-link">';
        $html .= htmlspecialchars($label);
        if ($hasCategories) {
            $html .= '<i class="icon icon-arrow-down"></i>';
        }
        $html .= '</a>';
        if ($hasCategories) {
            $html .= '<div class="sub-menu submenu-default">';
            $html .= '<ul class="menu-list">';
            foreach ($categories as $category) {
                $html .= '<li><a href="shop.php?category=' . urlencode($category['slug']) . '" class="menu-link-text">';
                $html .= htmlspecialchars($category['name']);
                $html .= '</a></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= '</li>';
    }
    
    $html .= '<li class="menu-item"><a href="contact.php" class="item-link">Contact Us</a></li>';
    
    return $html;
}
