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

function product_list_url($limit, $search, $status = '')
{
    $params = ['limit' => (int) $limit];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($status !== '') {
        $params['status'] = $status;
    }

    return 'product-list.php?' . http_build_query($params);
}

$allowedLimits = [10, 20, 30];
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
if (!in_array($limit, $allowedLimits, true)) {
    $limit = 10;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$products = [];

$statusMessages = [
    'deleted' => ['type' => 'success', 'text' => 'Product deleted successfully.'],
    'not_found' => ['type' => 'danger', 'text' => 'Product not found. It may have already been deleted.'],
    'delete_error' => ['type' => 'danger', 'text' => 'Could not delete the product. Please try again.'],
    'invalid' => ['type' => 'danger', 'text' => 'Invalid product selected.'],
];
$statusMessage = isset($statusMessages[$status]) ? $statusMessages[$status] : null;

if ($search !== '') {
    $likeSearch = '%' . $search . '%';
    $productStmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.name LIKE ? OR p.slug LIKE ? OR p.sku LIKE ? OR c.name LIKE ?
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $productStmt->bind_param('ssssi', $likeSearch, $likeSearch, $likeSearch, $likeSearch, $limit);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
} else {
    $productStmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $productStmt->bind_param('i', $limit);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
}

if ($productResult) {
    $products = $productResult->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <title>Admin Panel - Nutri Afghan</title>

    <?php include_once('includes/header-link.php'); ?>
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
                                    <h3>Product List</h3>
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
                                            <a href="#">
                                                <div class="text-tiny">Products</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Product List</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <?php if ($statusMessage): ?>
                                        <div class="alert alert-<?php echo e($statusMessage['type']); ?>" role="alert">
                                            <?php echo e($statusMessage['text']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <div class="show">
                                                <div class="text-tiny">Showing</div>
                                                <div class="select">
                                                    <select class="" name="limit" onchange="window.location.href='product-list.php?limit=' + this.value + '<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>'">
                                                        <?php foreach ($allowedLimits as $allowedLimit): ?>
                                                            <option value="<?php echo $allowedLimit; ?>" <?php echo $limit === $allowedLimit ? 'selected' : ''; ?>>
                                                                <?php echo $allowedLimit; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="text-tiny">entries</div>
                                            </div>
                                            <form class="form-search" method="get" action="product-list.php">
                                                <input type="hidden" name="limit" value="<?php echo (int) $limit; ?>">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Search here..." class="" name="search" tabindex="2" value="<?php echo e($search); ?>" aria-required="true">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <a class="tf-button style-1 w208" href="add-product.php"><i class="icon-plus"></i>Add new</a>
                                    </div>

                                    <div class="wg-table table-product-list">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Product</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Product ID</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Price</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Sale Price</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Quantity</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Stock</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Action</div>
                                            </li>
                                        </ul>

                                        <ul class="flex flex-column">
                                            <?php if (empty($products)): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-center gap20 flex-grow" style="padding: 24px;">
                                                        <div class="body-text">
                                                            <?php echo $search !== '' ? 'No products found for "' . e($search) . '".' : 'No products found. Add your first product.'; ?>
                                                        </div>
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
                                                            <a href="view-product.php?id=<?php echo (int) $product['id']; ?>" class="body-title-2">
                                                                <?php echo e($product['name']); ?>
                                                            </a>
                                                            <div class="text-tiny"><?php echo e($product['category_name'] ?: 'Uncategorized'); ?></div>
                                                        </div>
                                                        <div class="body-text">#<?php echo (int) $product['id']; ?></div>
                                                        <div class="body-text"><?php echo format_price($product['original_price']); ?></div>
                                                        <div class="body-text"><?php echo format_price($product['discount_price']); ?></div>
                                                        <div class="body-text"><?php echo (int) $product['stock_quantity']; ?></div>
                                                        <div>
                                                            <?php if ((int) $product['stock_quantity'] > 0): ?>
                                                                <div class="block-available">In stock</div>
                                                            <?php else: ?>
                                                                <div class="block-not-available">Out of stock</div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="list-icon-function">
                                                            <a href="view-product.php?id=<?php echo (int) $product['id']; ?>">
                                                                <div class="item eye">
                                                                    <i class="icon-eye"></i>
                                                                </div>
                                                            </a>
                                                            <a href="edit-product.php?id=<?php echo (int) $product['id']; ?>">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                            <form method="post" action="delete-product.php" onsubmit="return confirm('Delete this product permanently? This will also delete its variants and uploaded images.');" style="margin: 0;">
                                                                <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                                                <input type="hidden" name="limit" value="<?php echo (int) $limit; ?>">
                                                                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                                                                <button type="submit" class="item trash" title="Delete product" style="background: none; border: 0; padding: 0;">
                                                                    <i class="icon-trash-2"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10">
                                        <div class="text-tiny">Showing <?php echo count($products); ?> entries</div>
                                        <?php if ($search !== ''): ?>
                                            <a class="tf-button style-1 w208" href="product-list.php">Clear search</a>
                                        <?php endif; ?>
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
