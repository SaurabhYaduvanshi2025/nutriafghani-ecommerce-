<?php
require_once('auth.php');
require_once('../config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function category_image_path($path)
{
    if (empty($path)) {
        return 'images/walnut.webp';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return ltrim($path, '/');
}

function product_image_path($path)
{
    if (empty($path)) {
        return '../images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return '../uploads/' . ltrim($path, '/');
}

function format_price($value)
{
    return 'Rs. ' . number_format((float) $value, 2);
}

$categoryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($categoryId <= 0) {
    header('Location: category-list.php');
    exit();
}

$categoryStmt = $conn->prepare("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.id = ?
    GROUP BY c.id
    LIMIT 1
");
$categoryStmt->bind_param('i', $categoryId);
$categoryStmt->execute();
$category = $categoryStmt->get_result()->fetch_assoc();
$categoryStmt->close();

if (!$category) {
    header('Location: category-list.php');
    exit();
}

$productsStmt = $conn->prepare("
    SELECT id, name, slug, main_image, original_price, discount_price, stock_quantity, is_active, created_at
    FROM products
    WHERE category_id = ?
    ORDER BY created_at DESC
    LIMIT 12
");
$productsStmt->bind_param('i', $categoryId);
$productsStmt->execute();
$products = $productsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$productsStmt->close();
?>

<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <title>View Category - Nutri Afghan</title>

    <?php include_once('includes/header-link.php'); ?>

    <style>
        .category-detail-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 24px;
            align-items: start;
        }

        .category-image-large {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #eee;
            background: #f8f8f8;
        }

        .category-image-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-info-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 16px;
        }

        .category-info-item {
            padding: 14px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .category-products {
            margin-top: 24px;
        }

        @media (max-width: 768px) {
            .category-detail-grid,
            .category-info-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap menu-style-icon">
                <?php include_once('includes/preloader.php'); ?>
                <?php include_once('includes/sidebar.php'); ?>

                <div class="section-content-right">
                    <?php include_once('includes/top-header.php'); ?>

                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>View category</h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li>
                                            <a href="./">
                                                <div class="text-tiny">Dashboard</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <a href="category-list.php">
                                                <div class="text-tiny">Category</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny"><?php echo e($category['name']); ?></div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap mb-20">
                                        <h5>Category details</h5>
                                        <div class="flex gap10 flex-wrap">
                                            <a class="tf-button style-1 w208" href="edit-category.php?id=<?php echo (int) $category['id']; ?>">
                                                <i class="icon-edit-3"></i>Edit
                                            </a>
                                            <a class="tf-button style-1 w208" href="category-list.php">Back to list</a>
                                        </div>
                                    </div>

                                    <div class="category-detail-grid">
                                        <div class="category-image-large">
                                            <img src="<?php echo e(category_image_path($category['image'])); ?>" alt="<?php echo e($category['name']); ?>">
                                        </div>

                                        <div class="category-info-list">
                                            <div class="category-info-item">
                                                <div class="body-title">Category name</div>
                                                <div class="body-text"><?php echo e($category['name']); ?></div>
                                            </div>
                                            <div class="category-info-item">
                                                <div class="body-title">SEO slug</div>
                                                <div class="body-text"><?php echo e($category['slug']); ?></div>
                                            </div>
                                            <div class="category-info-item">
                                                <div class="body-title">Products</div>
                                                <div class="body-text"><?php echo (int) $category['product_count']; ?></div>
                                            </div>
                                            <div class="category-info-item">
                                                <div class="body-title">Status</div>
                                                <div class="body-text"><?php echo (int) $category['is_active'] === 1 ? 'Active' : 'Inactive'; ?></div>
                                            </div>
                                            <div class="category-info-item">
                                                <div class="body-title">Created</div>
                                                <div class="body-text"><?php echo date('d M Y', strtotime($category['created_at'])); ?></div>
                                            </div>
                                            <div class="category-info-item">
                                                <div class="body-title">Updated</div>
                                                <div class="body-text"><?php echo date('d M Y', strtotime($category['updated_at'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wg-box category-products">
                                    <div class="flex items-center justify-between gap10 flex-wrap mb-20">
                                        <h5>Products in this category</h5>
                                        <a class="tf-button style-1 w208" href="add-product.php"><i class="icon-plus"></i>Add product</a>
                                    </div>

                                    <div class="wg-table table-product-list">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Product</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Price</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Sale Price</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Stock</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Status</div>
                                            </li>
                                        </ul>

                                        <ul class="flex flex-column">
                                            <?php if (empty($products)): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-center gap20 flex-grow" style="padding: 24px;">
                                                        <div class="body-text">No products added in this category yet.</div>
                                                    </div>
                                                </li>
                                            <?php endif; ?>

                                            <?php foreach ($products as $product): ?>
                                                <li class="product-item gap14">
                                                    <div class="image no-bg">
                                                        <img src="<?php echo e(product_image_path($product['main_image'])); ?>" alt="<?php echo e($product['name']); ?>">
                                                    </div>
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="name">
                                                            <a href="../product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" class="body-title-2" target="_blank">
                                                                <?php echo e($product['name']); ?>
                                                            </a>
                                                            <div class="text-tiny">#<?php echo (int) $product['id']; ?></div>
                                                        </div>
                                                        <div class="body-text"><?php echo format_price($product['original_price']); ?></div>
                                                        <div class="body-text"><?php echo format_price($product['discount_price']); ?></div>
                                                        <div class="body-text"><?php echo (int) $product['stock_quantity']; ?></div>
                                                        <div class="body-text"><?php echo (int) $product['is_active'] === 1 ? 'Active' : 'Inactive'; ?></div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php include_once('includes/footer.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('includes/footer-link.php'); ?>
</body>

</html>
