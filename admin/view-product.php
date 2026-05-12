<?php
require_once('auth.php');
require_once('../config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function product_image_path($path)
{
    if (empty($path)) {
        return 'images/demo_img.jpg';
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

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: product-list.php');
    exit();
}

$productStmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
    LIMIT 1
");
$productStmt->bind_param('i', $productId);
$productStmt->execute();
$product = $productStmt->get_result()->fetch_assoc();
$productStmt->close();

if (!$product) {
    header('Location: product-list.php');
    exit();
}

$variantStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY weight_grams ASC");
$variantStmt->bind_param('i', $productId);
$variantStmt->execute();
$variants = $variantStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$variantStmt->close();

$galleryImages = [];
foreach (['main_image', 'gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $field) {
    if (!empty($product[$field])) {
        $galleryImages[] = product_image_path($product[$field]);
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title>View Product - Nutri Afghan</title>
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .product-view-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
        }

        .product-main-image {
            width: 100%;
            aspect-ratio: 1;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f8f8;
        }

        .product-main-image img,
        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-thumbs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .product-thumb {
            aspect-ratio: 1;
            border: 1px solid #eee;
            border-radius: 6px;
            overflow: hidden;
        }

        .product-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 14px;
        }

        .product-info-item {
            padding: 14px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .description-box {
            margin-top: 20px;
            padding: 16px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .product-view-grid,
            .product-info-grid {
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
                                    <h3>View product</h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li><a href="./"><div class="text-tiny">Dashboard</div></a></li>
                                        <li><i class="icon-chevron-right"></i></li>
                                        <li><a href="product-list.php"><div class="text-tiny">Products</div></a></li>
                                        <li><i class="icon-chevron-right"></i></li>
                                        <li><div class="text-tiny"><?php echo e($product['name']); ?></div></li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap mb-20">
                                        <h5>Product details</h5>
                                        <div class="flex gap10 flex-wrap">
                                            <a class="tf-button style-1 w208" href="edit-product.php?id=<?php echo (int) $product['id']; ?>"><i class="icon-edit-3"></i>Edit</a>
                                            <a class="tf-button style-1 w208" href="product-list.php">Back to list</a>
                                        </div>
                                    </div>

                                    <div class="product-view-grid">
                                        <div>
                                            <div class="product-main-image">
                                                <img src="<?php echo e(product_image_path($product['main_image'])); ?>" alt="<?php echo e($product['name']); ?>">
                                            </div>
                                            <?php if (!empty($galleryImages)): ?>
                                                <div class="product-thumbs">
                                                    <?php foreach ($galleryImages as $image): ?>
                                                        <div class="product-thumb">
                                                            <img src="<?php echo e($image); ?>" alt="<?php echo e($product['name']); ?>">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <div class="product-info-grid">
                                                <div class="product-info-item"><div class="body-title">Name</div><div class="body-text"><?php echo e($product['name']); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Category</div><div class="body-text"><?php echo e($product['category_name'] ?: 'Uncategorized'); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Slug</div><div class="body-text"><?php echo e($product['slug']); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">SKU</div><div class="body-text"><?php echo e($product['sku'] ?: 'N/A'); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Original price</div><div class="body-text"><?php echo format_price($product['original_price']); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Sale price</div><div class="body-text"><?php echo format_price($product['discount_price']); ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Stock</div><div class="body-text"><?php echo (int) $product['stock_quantity']; ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Status</div><div class="body-text"><?php echo (int) $product['is_active'] === 1 ? 'Active' : 'Inactive'; ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Featured</div><div class="body-text"><?php echo (int) $product['is_featured'] === 1 ? 'Yes' : 'No'; ?></div></div>
                                                <div class="product-info-item"><div class="body-title">Created</div><div class="body-text"><?php echo date('d M Y', strtotime($product['created_at'])); ?></div></div>
                                            </div>

                                            <div class="description-box">
                                                <div class="body-title mb-10">Description</div>
                                                <div class="body-text"><?php echo nl2br(e($product['description'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wg-box" style="margin-top: 24px;">
                                    <h5 class="mb-20">Variants</h5>
                                    <div class="wg-table table-product-list">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li><div class="body-title">Weight</div></li>
                                            <li><div class="body-title">Price</div></li>
                                            <li><div class="body-title">Sale Price</div></li>
                                            <li><div class="body-title">SKU</div></li>
                                            <li><div class="body-title">Stock</div></li>
                                        </ul>
                                        <ul class="flex flex-column">
                                            <?php if (empty($variants)): ?>
                                                <li class="product-item gap14"><div class="body-text" style="padding: 20px;">No variants found.</div></li>
                                            <?php endif; ?>
                                            <?php foreach ($variants as $variant): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="body-text"><?php echo e($variant['weight_value'] ?: $variant['weight_label']); ?></div>
                                                        <div class="body-text"><?php echo format_price($variant['variant_price']); ?></div>
                                                        <div class="body-text"><?php echo format_price($variant['variant_discount_price']); ?></div>
                                                        <div class="body-text"><?php echo e($variant['variant_sku'] ?: 'N/A'); ?></div>
                                                        <div class="body-text"><?php echo (int) $variant['stock_quantity']; ?></div>
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
