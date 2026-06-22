<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function product_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function format_money($value)
{
    return 'Rs. ' . number_format((float) $value, 2);
}

function render_product_card($product, $index = 0)
{
    $imagePath = product_image_path($product['main_image']);
    $detailUrl = 'product-detail.php?slug=' . urlencode($product['slug']);
    $delay = ($index % 4) * 0.1;
    $hasDiscount = (float) $product['original_price'] > (float) $product['discount_price'];
    ?>
    <div
        class="card-product wow fadeInUp"
        <?php if ($delay > 0): ?>data-wow-delay="<?php echo e($delay); ?>s"<?php endif; ?>
        style="visibility: visible; <?php if ($delay > 0): ?>animation-delay: <?php echo e($delay); ?>s; <?php endif; ?>animation-name: fadeInUp"
    >
        <div class="card-product-wrapper">
            <a href="<?php echo e($detailUrl); ?>" class="product-img">
                <img
                    class="lazyload img-product"
                    data-src="<?php echo e($imagePath); ?>"
                    src="<?php echo e($imagePath); ?>"
                    alt="<?php echo e($product['name']); ?>"
                />
                <img
                    class="lazyload img-hover"
                    data-src="<?php echo e($imagePath); ?>"
                    src="<?php echo e($imagePath); ?>"
                    alt="<?php echo e($product['name']); ?>"
                />
            </a>
            <div class="list-product-btn">
                <a href="#"
                   class="box-icon wishlist btn-icon-action"
                   data-product-id="<?php echo (int) $product['id']; ?>"
                   data-product-name="<?php echo e($product['name']); ?>"
                   data-product-slug="<?php echo e($product['slug']); ?>"
                   data-product-image="<?php echo e($imagePath); ?>"
                   data-product-price="<?php echo e($product['discount_price'] ?: $product['original_price']); ?>"
                   aria-label="Add <?php echo e($product['name']); ?> to wishlist">
                    <span class="icon icon-heart"></span>
                    <span class="tooltip">Wishlist</span>
                </a>
                <a href="<?php echo e($detailUrl); ?>" class="box-icon quickview">
                    <span class="icon icon-eye"></span>
                    <span class="tooltip">View Details</span>
                </a>
            </div>
            <div class="list-btn-main">
                <a href="<?php echo e($detailUrl); ?>" class="btn-main-product">View Details</a>
            </div>
        </div>
        <div class="card-product-info">
            <a href="<?php echo e($detailUrl); ?>" class="title link">
                <?php echo e($product['name']); ?>
            </a>
            <div class="product-price-line">
                <span class="sale-price"><?php echo format_money($product['discount_price']); ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="real-price"><?php echo format_money($product['original_price']); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$pageTitle = 'Products';
$products = [];
$productResult = null;

if ($searchTerm !== '') {
    $pageTitle = 'Search: ' . $searchTerm;
    $likeSearch = '%' . $searchTerm . '%';
    $productStmt = $conn->prepare("
        SELECT p.*
        FROM products p
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)
        ORDER BY p.created_at DESC
    ");
    $productStmt->bind_param('ss', $likeSearch, $likeSearch);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
} else {
    $productResult = $conn->query("
        SELECT p.*
        FROM products p
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ");
}

if (!$productResult) {
    $productResult = $conn->query("
        SELECT p.*
        FROM products p
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ");
}

if ($productResult) {
    $products = $productResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Nutri Afghan</title>

    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />

    <?php include_once('includes/header-link.php'); ?>

    <style>
        .shop-empty-state {
            grid-column: 1 / -1;
            padding: 50px 20px;
            text-align: center;
            background: #f8f8f8;
            border-radius: 8px;
        }

        .shop-category-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .product-price-line {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .product-price-line .sale-price {
            color: #d32f2f;
            font-size: 18px;
            font-weight: 700;
        }

        .product-price-line .real-price {
            color: #888;
            font-size: 14px;
            text-decoration: line-through;
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
                <h3 class="heading text-center"><?php echo e($pageTitle); ?></h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>Shop</li>
                </ul>
            </div>
        </div>

        <section class="flat-spacing">
            <div class="container">
                <div class="shop-category-heading">
                    <h4><?php echo e($pageTitle); ?></h4>
                    <span><?php echo count($products); ?> products</span>
                </div>

                <?php if (empty($products)): ?>
                    <div class="shop-empty-state">
                        <h5>No products found</h5>
                        <p class="text-secondary">Products added from the admin panel will appear here after they are active.</p>
                    </div>
                <?php else: ?>
                    <div class="tf-grid-layout tf-col-2 md-col-3 xl-col-4">
                        <?php foreach ($products as $index => $product): ?>
                            <?php render_product_card($product, $index); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
</body>

</html>
