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
                $productResult = $conn->query("
                    SELECT id, menu_id, name, slug
                    FROM products
                    WHERE is_active = 1
                    AND menu_id IN ($menuIdList)
                    ORDER BY name ASC
                ");
                $productsByMenu = [];

                if ($productResult) {
                    while ($product = $productResult->fetch_assoc()) {
                        $productsByMenu[(int) $product['menu_id']][] = $product;
                    }
                }

                foreach ($menus as &$menu) {
                    $menu['products'] = $productsByMenu[(int) $menu['id']] ?? [];
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
        
        $products = isset($mainItem['products']) && is_array($mainItem['products']) ? $mainItem['products'] : [];
        $hasProducts = count($products) > 0;
        $url = $hasProducts ? '#' : ($mainItem['url'] ? $mainItem['url'] : '#');
        $html .= '<li class="menu-item' . ($hasProducts ? ' position-relative' : '') . '">';
        $html .= '<a href="' . htmlspecialchars($url) . '" class="item-link">';
        $html .= htmlspecialchars($label);
        if ($hasProducts) {
            $html .= '<i class="icon icon-arrow-down"></i>';
        }
        $html .= '</a>';
        if ($hasProducts) {
            $html .= '<div class="sub-menu submenu-default">';
            $html .= '<ul class="menu-list">';
            foreach ($products as $product) {
                $html .= '<li><a href="product-detail.php?slug=' . urlencode($product['slug']) . '" class="menu-link-text">';
                $html .= htmlspecialchars($product['name']);
                $html .= '</a></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= '</li>';
    }
    
    $html .= '<li class="menu-item"><a href="blog.php" class="item-link">Blog</a></li>';
    $html .= '<li class="menu-item"><a href="contact.php" class="item-link">Contact Us</a></li>';
    
    return $html;
}
