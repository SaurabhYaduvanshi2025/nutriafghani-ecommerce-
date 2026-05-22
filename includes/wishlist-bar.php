<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/db.php');

if (!function_exists('wishlist_modal_image_path')) {
    function wishlist_modal_image_path($path)
    {
        if (empty($path)) {
            return 'images/pista_demp.jpg';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return 'uploads/' . ltrim($path, '/');
    }
}

if (!function_exists('ensure_wishlist_modal_table_exists')) {
    function ensure_wishlist_modal_table_exists($conn)
    {
        $conn->query("
            CREATE TABLE IF NOT EXISTS `wishlist_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_wishlist_item` (`customer_id`, `product_id`),
                INDEX `idx_wishlist_customer_id` (`customer_id`),
                INDEX `idx_wishlist_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

$wishlistItems = [];
if (!empty($_SESSION['customer_id'])) {
    ensure_wishlist_modal_table_exists($conn);
    $customerId = (int) $_SESSION['customer_id'];
    $wishlistStmt = $conn->prepare("
        SELECT wi.id, p.id AS product_id, p.name, p.slug, p.main_image, p.gallery_image_1,
               p.original_price, p.discount_price
        FROM wishlist_items wi
        JOIN products p ON wi.product_id = p.id
        WHERE wi.customer_id = ? AND p.is_active = 1
        ORDER BY wi.created_at DESC
    ");
    $wishlistStmt->bind_param("i", $customerId);
    $wishlistStmt->execute();
    $wishlistItems = $wishlistStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $wishlistStmt->close();
}
?>
<div class="modal fullRight fade modal-wishlist" id="wishlist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="header">
                <h5 class="title">Wish List</h5>
                <span class="icon-close icon-close-popup" data-bs-dismiss="modal"></span>
            </div>
            <div class="wrap">
                <div class="tf-mini-cart-wrap">
                    <div class="tf-mini-cart-main">
                        <div class="tf-mini-cart-sroll">
                            <div class="tf-mini-cart-items" id="wishlist-items">
                                <?php if (!empty($wishlistItems)): ?>
                                    <?php foreach ($wishlistItems as $item): ?>
                                        <?php
                                        $wishlistImage = wishlist_modal_image_path($item['main_image'] ?: $item['gallery_image_1']);
                                        $wishlistPrice = (float) ($item['discount_price'] ?: $item['original_price']);
                                        ?>
                                        <div class="tf-mini-cart-item file-delete" data-wishlist-item-id="<?php echo (int) $item['id']; ?>" data-product-id="<?php echo (int) $item['product_id']; ?>">
                                            <div class="tf-mini-cart-image">
                                                <img
                                                    class="lazyload"
                                                    data-src="<?php echo htmlspecialchars($wishlistImage); ?>"
                                                    src="<?php echo htmlspecialchars($wishlistImage); ?>"
                                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                    style="width: 100%; height: 100%; object-fit: cover;" />
                                            </div>
                                            <div class="tf-mini-cart-info flex-grow-1">
                                                <div class="mb_12 d-flex align-items-center justify-content-between flex-wrap gap-12">
                                                    <div class="text-title">
                                                        <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>" class="link text-line-clamp-1"><?php echo htmlspecialchars($item['name']); ?></a>
                                                    </div>
                                                    <div class="text-button tf-btn-remove remove" onclick="removeFromWishlist(<?php echo (int) $item['id']; ?>)">Remove</div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-12">
                                                    <div class="text-secondary-2">Saved item</div>
                                                    <div class="text-button">₹<?php echo number_format($wishlistPrice, 2); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center wishlist-empty" style="padding: 40px 20px; color: #999;">
                                        <p>Your wishlist is empty</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="tf-mini-cart-bottom">
                        <a href="shop.php" class="btn-style-2 w-100 radius-4 view-all-wishlist"><span class="text-btn-uppercase">View All Products</span></a>
                        <a href="shop.php" class="text-btn-uppercase">Or continue shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
