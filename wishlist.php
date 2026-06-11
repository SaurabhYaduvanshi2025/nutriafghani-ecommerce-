<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

require_customer_login('wishlist.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wishlist_page_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function ensure_wishlist_page_table_exists($conn)
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

$wishlistItems = [];
$isLoggedIn = is_customer_logged_in();

if ($isLoggedIn) {
    ensure_wishlist_page_table_exists($conn);
    $customerId = get_customer_id();
    $stmt = $conn->prepare("
        SELECT wi.id, p.id AS product_id, p.name, p.slug, p.main_image, p.gallery_image_1,
               p.original_price, p.discount_price
        FROM wishlist_items wi
        JOIN products p ON wi.product_id = p.id
        WHERE wi.customer_id = ? AND p.is_active = 1
        ORDER BY wi.created_at DESC
    ");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $wishlistItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Wishlist - Nutri Afghan</title>
    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
        }
        .wishlist-card {
            border: 1px solid #eeeeee;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
        }
        .wishlist-card-image {
            aspect-ratio: 1 / 1;
            background: #f7f7f7;
        }
        .wishlist-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .wishlist-card-body {
            padding: 16px;
        }
        .wishlist-card-title {
            display: block;
            min-height: 48px;
            color: #181818;
            font-weight: 600;
            line-height: 1.45;
        }
        .wishlist-card-price {
            margin-top: 10px;
            color: #d32f2f;
            font-weight: 700;
        }
        .wishlist-card-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .wishlist-card-actions .btn-line,
        .wishlist-card-actions button {
            flex: 1;
            min-height: 42px;
        }
        .wishlist-remove {
            border: 1px solid #ddd;
            background: #fff;
            color: #181818;
            border-radius: 4px;
            cursor: pointer;
        }
        .wishlist-empty-page {
            padding: 60px 20px;
            text-align: center;
            background: #f8f8f8;
            border-radius: 8px;
        }
        @media (max-width: 991px) {
            .wishlist-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 575px) {
            .wishlist-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="preload-wrapper popup-loader">
    <?php include_once('includes/scroll-top.php'); ?>
    <?php include_once('includes/preloader.php'); ?>

    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>

        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container">
                <h3 class="heading text-center">Wishlist</h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>Wishlist</li>
                </ul>
            </div>
        </div>

        <section class="flat-spacing">
            <div class="container">
                <div id="wishlist-page-items" class="<?php echo !empty($wishlistItems) ? 'wishlist-grid' : ''; ?>">
                    <?php if (!empty($wishlistItems)): ?>
                        <?php foreach ($wishlistItems as $item): ?>
                            <?php
                            $image = wishlist_page_image_path($item['main_image'] ?: $item['gallery_image_1']);
                            $price = (float) ($item['discount_price'] ?: $item['original_price']);
                            ?>
                            <div class="wishlist-card" data-wishlist-item-id="<?php echo (int) $item['id']; ?>">
                                <a class="wishlist-card-image" href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>">
                                    <img src="<?php echo e($image); ?>" alt="<?php echo e($item['name']); ?>">
                                </a>
                                <div class="wishlist-card-body">
                                    <a class="wishlist-card-title" href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                                    <div class="wishlist-card-price">₹<?php echo number_format($price, 2); ?></div>
                                    <div class="wishlist-card-actions">
                                        <a class="btn-line" href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>">View</a>
                                        <button class="wishlist-remove" type="button" data-wishlist-item-id="<?php echo (int) $item['id']; ?>">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="wishlist-empty-page">
                            <h5>Your wishlist is empty</h5>
                            <p class="text-secondary">Add products with the heart icon and they will appear here.</p>
                            <a href="shop.php" class="tf-btn btn-fill mt-3">Shop Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php include_once('includes/footer.php'); ?>
        <?php include_once('includes/bottom-toolbar.php'); ?>
    </div>

    <?php include_once('includes/auto-popup.php'); ?>
    <?php include_once('includes/search-bar.php'); ?>
    <?php include_once('includes/mobile-menu.php'); ?>
    <?php include_once('includes/quick-view.php'); ?>
    <?php include_once('includes/shopping-cart.php'); ?>
    <?php include_once('includes/wishlist-bar.php'); ?>
    <?php include_once('includes/footer-link.php'); ?>
    <script>
        (function () {
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            const storageKey = 'nutriafghan_wishlist_items';
            const container = document.getElementById('wishlist-page-items');

            function money(value) {
                return Number(value || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                const span = document.createElement('span');
                span.textContent = value == null ? '' : String(value);
                return span.innerHTML;
            }

            function getLocalItems() {
                try {
                    return JSON.parse(localStorage.getItem(storageKey) || '[]');
                } catch (error) {
                    return [];
                }
            }

            function saveLocalItems(items) {
                localStorage.setItem(storageKey, JSON.stringify(items));
            }

            function updateHeaderWishlistCount(count) {
                const countBox = document.getElementById('wishlist-count-box');
                if (countBox) {
                    countBox.textContent = Number(count || 0);
                }
            }

            function renderEmpty() {
                container.className = '';
                container.innerHTML = `
                    <div class="wishlist-empty-page">
                        <h5>Your wishlist is empty</h5>
                        <p class="text-secondary">Add products with the heart icon and they will appear here.</p>
                        <a href="shop.php" class="tf-btn btn-fill mt-3">Shop Products</a>
                    </div>
                `;
            }

            function renderLocalItems() {
                const items = getLocalItems();
                updateHeaderWishlistCount(items.length);
                if (!items.length) {
                    renderEmpty();
                    return;
                }

                container.className = 'wishlist-grid';
                container.innerHTML = items.map(function (item) {
                    const slug = encodeURIComponent(item.slug || '');
                    const name = escapeHtml(item.name || 'Product');
                    const image = escapeHtml(item.image || 'images/pista_demp.jpg');
                    const id = escapeHtml(item.id);

                    return `
                        <div class="wishlist-card" data-wishlist-item-id="${id}">
                            <a class="wishlist-card-image" href="product-detail.php?slug=${slug}">
                                <img src="${image}" alt="${name}">
                            </a>
                            <div class="wishlist-card-body">
                                <a class="wishlist-card-title" href="product-detail.php?slug=${slug}">${name}</a>
                                <div class="wishlist-card-price">₹${money(item.price)}</div>
                                <div class="wishlist-card-actions">
                                    <a class="btn-line" href="product-detail.php?slug=${slug}">View</a>
                                    <button class="wishlist-remove" type="button" data-wishlist-item-id="${id}">Remove</button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            if (!isLoggedIn) {
                renderLocalItems();
            }

            container.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.wishlist-remove');
                if (!removeButton) {
                    return;
                }

                const wishlistItemId = removeButton.getAttribute('data-wishlist-item-id');
                if (!wishlistItemId) {
                    return;
                }

                if (!isLoggedIn || wishlistItemId.indexOf('local-') === 0) {
                    saveLocalItems(getLocalItems().filter(function (item) {
                        return String(item.id) !== String(wishlistItemId);
                    }));
                    renderLocalItems();
                    return;
                }

                const params = new URLSearchParams();
                params.set('action', 'remove');
                params.set('wishlist_item_id', wishlistItemId);

                fetch('wishlist-process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        removeButton.closest('.wishlist-card').remove();
                        updateHeaderWishlistCount(data.wishlist_count || 0);
                        if (!container.querySelector('.wishlist-card')) {
                            renderEmpty();
                        }
                    }
                });
            });
        })();
    </script>
</body>
</html>
