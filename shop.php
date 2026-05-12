<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function category_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'admin/' . ltrim($path, '/');
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
                <a href="#wishlist" data-bs-toggle="modal" class="box-icon wishlist btn-icon-action">
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
            <a href="<?php echo e($detailUrl); ?>" class="title link"><?php echo e($product['name']); ?></a>
            <div class="text-secondary"><?php echo e($product['category_name']); ?></div>
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

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$currentCategoryName = 'All Products';

$categories = [];
$categorySql = "
    SELECT c.id, c.name, c.slug, c.image, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id, c.name, c.slug, c.image
    ORDER BY c.name ASC
";
$categoryResult = $conn->query($categorySql);
if ($categoryResult) {
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
}

if ($selectedCategory !== '') {
    $validCategory = false;
    foreach ($categories as $category) {
        if ($category['slug'] === $selectedCategory) {
            $validCategory = true;
            $currentCategoryName = $category['name'];
            break;
        }
    }

    if (!$validCategory) {
        $selectedCategory = '';
        $currentCategoryName = 'All Products';
    }
}

$products = [];
if ($selectedCategory !== '') {
    $productStmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1 AND c.is_active = 1 AND c.slug = ?
        ORDER BY p.created_at DESC
    ");
    $productStmt->bind_param('s', $selectedCategory);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
} else {
    $productResult = $conn->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1 AND c.is_active = 1
        ORDER BY p.created_at DESC
    ");
}

if ($productResult) {
    $products = $productResult->fetch_all(MYSQLI_ASSOC);
}

$productsByCategory = [];
foreach ($products as $product) {
    $productsByCategory[(int) $product['category_id']][] = $product;
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
        .shop-category-strip {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 18px;
            margin-bottom: 38px;
        }

        .shop-category-card {
            display: block;
            text-align: center;
            color: inherit;
        }

        .shop-category-card .image {
            width: 112px;
            height: 112px;
            margin: 0 auto 10px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid transparent;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        .shop-category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-category-card:hover .image,
        .shop-category-card.active .image {
            border-color: #7cb342;
            transform: translateY(-2px);
        }

        .shop-category-card .name {
            font-weight: 600;
            line-height: 1.3;
        }

        .shop-category-card .count {
            font-size: 13px;
            color: #777;
            margin-top: 3px;
        }

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

        .shop-category-section {
            margin-top: 38px;
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
                <h3 class="heading text-center"><?php echo e($currentCategoryName); ?></h3>
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
                    <h4>Shop by Category</h4>
                    <?php if ($selectedCategory !== ''): ?>
                        <a href="shop.php" class="btn-line">View all products</a>
                    <?php endif; ?>
                </div>

                <div class="shop-category-strip">
                    <?php foreach ($categories as $category): ?>
                        <a
                            href="shop.php?category=<?php echo urlencode($category['slug']); ?>"
                            class="shop-category-card <?php echo $selectedCategory === $category['slug'] ? 'active' : ''; ?>"
                        >
                            <span class="image">
                                <img
                                    src="<?php echo e(category_image_path($category['image'])); ?>"
                                    alt="<?php echo e($category['name']); ?>"
                                />
                            </span>
                            <span class="name"><?php echo e($category['name']); ?></span>
                            <span class="count"><?php echo (int) $category['product_count']; ?> items</span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($products)): ?>
                    <div class="shop-empty-state">
                        <h5>No products found</h5>
                        <p class="text-secondary">Products added from the admin panel will appear here after they are active.</p>
                    </div>
                <?php elseif ($selectedCategory !== ''): ?>
                    <div class="shop-category-heading">
                        <h4><?php echo e($currentCategoryName); ?></h4>
                        <span><?php echo count($products); ?> products</span>
                    </div>

                    <div class="tf-grid-layout tf-col-2 md-col-3 xl-col-4">
                        <?php foreach ($products as $index => $product): ?>
                            <?php render_product_card($product, $index); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                            $categoryProducts = $productsByCategory[(int) $category['id']] ?? [];
                            if (empty($categoryProducts)) {
                                continue;
                            }
                        ?>
                        <div class="shop-category-section">
                            <div class="shop-category-heading">
                                <h4><?php echo e($category['name']); ?></h4>
                                <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="btn-line">
                                    View all <?php echo (int) count($categoryProducts); ?>
                                </a>
                            </div>

                            <div class="tf-grid-layout tf-col-2 md-col-3 xl-col-4">
                                <?php foreach ($categoryProducts as $index => $product): ?>
                                    <?php render_product_card($product, $index); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
