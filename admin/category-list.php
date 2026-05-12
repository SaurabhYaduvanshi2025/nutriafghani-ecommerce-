<?php
require_once('auth.php');
require_once('../config/db.php');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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

function category_list_url($limit, $search, $status = '')
{
    $params = ['limit' => (int) $limit];

    if ($search !== '') {
        $params['search'] = $search;
    }

    if ($status !== '') {
        $params['status'] = $status;
    }

    return 'category-list.php?' . http_build_query($params);
}

$allowedLimits = [10, 20, 30];
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
if (!in_array($limit, $allowedLimits, true)) {
    $limit = 10;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$categories = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $deleteId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $redirectLimit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;
    $transactionStarted = false;
    if (!in_array($redirectLimit, $allowedLimits, true)) {
        $redirectLimit = 10;
    }
    $redirectSearch = isset($_POST['search']) ? trim($_POST['search']) : '';

    if ($deleteId <= 0) {
        header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'invalid'));
        exit();
    }

    try {
        $existingStmt = $conn->prepare("SELECT image FROM categories WHERE id = ? LIMIT 1");
        $existingStmt->bind_param('i', $deleteId);
        $existingStmt->execute();
        $existingCategory = $existingStmt->get_result()->fetch_assoc();
        $existingStmt->close();

        if (!$existingCategory) {
            header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'not_found'));
            exit();
        }

        $conn->begin_transaction();
        $transactionStarted = true;

        $menuStmt = $conn->prepare("DELETE FROM menu_items WHERE category_id = ?");
        $menuStmt->bind_param('i', $deleteId);
        $menuStmt->execute();
        $menuStmt->close();

        $variantStmt = $conn->prepare("
            DELETE pv
            FROM product_variants pv
            INNER JOIN products p ON p.id = pv.product_id
            WHERE p.category_id = ?
        ");
        $variantStmt->bind_param('i', $deleteId);
        $variantStmt->execute();
        $variantStmt->close();

        $productStmt = $conn->prepare("DELETE FROM products WHERE category_id = ?");
        $productStmt->bind_param('i', $deleteId);
        $productStmt->execute();
        $productStmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $deleteStmt->bind_param('i', $deleteId);
        $deleteStmt->execute();
        $deletedRows = $deleteStmt->affected_rows;
        $deleteStmt->close();

        $verifyStmt = $conn->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
        $verifyStmt->bind_param('i', $deleteId);
        $verifyStmt->execute();
        $categoryStillExists = $verifyStmt->get_result()->num_rows > 0;
        $verifyStmt->close();

        if ($deletedRows <= 0 || $categoryStillExists) {
            $conn->rollback();
            $transactionStarted = false;
            header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'delete_error'));
            exit();
        }

        $conn->commit();
        $transactionStarted = false;

        if ($deletedRows > 0 && !empty($existingCategory['image']) && !preg_match('/^https?:\/\//i', $existingCategory['image'])) {
            $imageFile = __DIR__ . '/' . ltrim($existingCategory['image'], '/');
            if (is_file($imageFile)) {
                unlink($imageFile);
            }
        }

        header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'deleted'));
        exit();
    } catch (Exception $e) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        error_log('Category delete error: ' . $e->getMessage());
        header('Location: ' . category_list_url($redirectLimit, $redirectSearch, 'delete_error'));
        exit();
    }
}

$statusMessages = [
    'deleted' => ['type' => 'success', 'text' => 'Category deleted successfully. Related products were also removed.'],
    'not_found' => ['type' => 'danger', 'text' => 'Category not found. It may have already been deleted.'],
    'delete_error' => ['type' => 'danger', 'text' => 'Could not delete the category. Please try again.'],
    'invalid' => ['type' => 'danger', 'text' => 'Invalid category selected.'],
];
$statusMessage = isset($statusMessages[$status]) ? $statusMessages[$status] : null;

if ($search !== '') {
    $likeSearch = '%' . $search . '%';
    $categoryStmt = $conn->prepare("
        SELECT c.id, c.name, c.slug, c.image, c.is_active, c.created_at, m.label AS menu_label, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN menu_items m ON m.id = c.menu_id
        LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
        WHERE c.name LIKE ? OR c.slug LIKE ?
        GROUP BY c.id, c.name, c.slug, c.image, c.is_active, c.created_at, m.label
        ORDER BY c.created_at DESC
        LIMIT ?
    ");
    $categoryStmt->bind_param('ssi', $likeSearch, $likeSearch, $limit);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
} else {
    $categoryStmt = $conn->prepare("
        SELECT c.id, c.name, c.slug, c.image, c.is_active, c.created_at, m.label AS menu_label, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN menu_items m ON m.id = c.menu_id
        LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
        GROUP BY c.id, c.name, c.slug, c.image, c.is_active, c.created_at, m.label
        ORDER BY c.created_at DESC
        LIMIT ?
    ");
    $categoryStmt->bind_param('i', $limit);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
}

if ($categoryResult) {
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<!--<![endif]-->

<head>
    <!-- Basic Page Needs -->
    <meta charset="utf-8">
    <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
    <title>Admin Panel - Nutri Afghan</title>

    <?php
    include_once('includes/header-link.php');
    ?>
</head>

<body class="body">

    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <!-- layout-wrap -->
            <div class="layout-wrap menu-style-icon">
                <!-- preload -->
                <?php
                include_once('includes/preloader.php');
                ?>
                <!-- /preload -->

                <!-- section-menu-left -->
                <?php
                include_once('includes/sidebar.php');
                ?>
                <!-- /section-menu-left -->

                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php
                    include_once('includes/top-header.php');
                    ?>
                    <!-- /header-dashboard -->

                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>All category</h3>
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
                                                <div class="text-tiny">Category</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">All category</div>
                                        </li>
                                    </ul>
                                </div>
                                <!-- all-category -->
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
                                                    <select class="" name="limit" onchange="window.location.href='category-list.php?limit=' + this.value + '<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>'">
                                                        <?php foreach ($allowedLimits as $allowedLimit): ?>
                                                            <option value="<?php echo $allowedLimit; ?>" <?php echo $limit === $allowedLimit ? 'selected' : ''; ?>>
                                                                <?php echo $allowedLimit; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="text-tiny">entries</div>
                                            </div>
                                            <form class="form-search" method="get" action="category-list.php">
                                                <input type="hidden" name="limit" value="<?php echo (int) $limit; ?>">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Search here..." class="" name="search" tabindex="2" value="<?php echo e($search); ?>" aria-required="true">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <a class="tf-button style-1 w208" href="new-category.php"><i class="icon-plus"></i>Add new</a>
                                    </div>
                                    <div class="wg-table table-all-category">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Category</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Quantity</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Menu</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Status</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Start date</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Action</div>
                                            </li>
                                        </ul>
                                        <ul class="flex flex-column">
                                            <?php if (empty($categories)): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-center gap20 flex-grow" style="padding: 24px;">
                                                        <div class="body-text">
                                                            <?php echo $search !== '' ? 'No categories found for "' . e($search) . '".' : 'No categories found. Add your first category.'; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endif; ?>

                                            <?php foreach ($categories as $category): ?>
                                                <li class="product-item gap14">
                                                    <div class="image no-bg">
                                                        <img src="<?php echo e(category_image_path($category['image'])); ?>" class="rounded-3" alt="<?php echo e($category['name']); ?>">
                                                    </div>
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="name">
                                                            <a href="view-category.php?id=<?php echo (int) $category['id']; ?>" class="body-title-2">
                                                                <?php echo e($category['name']); ?>
                                                            </a>
                                                            <div class="text-tiny"><?php echo e($category['slug']); ?></div>
                                                        </div>
                                                        <div class="body-text"><?php echo (int) $category['product_count']; ?></div>
                                                        <div class="body-text"><?php echo e($category['menu_label'] ?: 'No menu'); ?></div>
                                                        <div class="body-text"><?php echo (int) $category['is_active'] === 1 ? 'Active' : 'Inactive'; ?></div>
                                                        <div class="body-text"><?php echo date('d M Y', strtotime($category['created_at'])); ?></div>
                                                        <div class="list-icon-function">
                                                            <a href="view-category.php?id=<?php echo (int) $category['id']; ?>">
                                                                <div class="item eye">
                                                                    <i class="icon-eye"></i>
                                                                </div>
                                                            </a>
                                                            <a href="edit-category.php?id=<?php echo (int) $category['id']; ?>">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                            <form method="post" action="delete-category.php" onsubmit="return confirm('Delete this category permanently? This will also delete all products in this category.');" style="margin: 0;">
                                                                <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                                                                <input type="hidden" name="limit" value="<?php echo (int) $limit; ?>">
                                                                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                                                                <button type="submit" class="item trash" title="Delete category" style="background: none; border: 0; padding: 0;">
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
                                        <div class="text-tiny">Showing <?php echo count($categories); ?> entries</div>
                                        <?php if ($search !== ''): ?>
                                            <a class="tf-button style-1 w208" href="category-list.php">Clear search</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- /all-category -->
                            </div>
                            <!-- /main-content-wrap -->
                        </div>
                        <!-- /main-content-wrap -->

                        <!-- bottom-page -->
                        <?php
                        include_once('includes/footer.php');
                        ?>
                        <!-- /bottom-page -->
                    </div>
                    <!-- /main-content -->
                </div>
                <!-- /section-content-right -->

            </div>
            <!-- /layout-wrap -->
        </div>
        <!-- /#page -->
    </div>
    <!-- /#wrapper -->

    <!-- Javascript -->
    <?php
    include_once('includes/footer-link.php');
    ?>

</body>

</html>
