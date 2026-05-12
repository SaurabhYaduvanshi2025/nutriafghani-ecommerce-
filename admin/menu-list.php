<?php
require_once('auth.php');
require_once('../config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$statusMessages = [
    'added' => ['type' => 'success', 'text' => 'Menu added successfully.'],
    'updated' => ['type' => 'success', 'text' => 'Menu updated successfully.'],
    'not_found' => ['type' => 'danger', 'text' => 'Menu not found.'],
    'invalid' => ['type' => 'danger', 'text' => 'Invalid menu selected.'],
];
$statusMessage = isset($statusMessages[$status]) ? $statusMessages[$status] : null;

$menuItems = [];
$stmt = $conn->prepare("
    SELECT id, label, sort_order, created_at
    FROM menu_items
    WHERE menu_type = 'homepage'
    AND parent_id IS NULL
    ORDER BY sort_order ASC, id ASC
");
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $menuItems = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Menu List | Admin Panel</title>
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
                                    <h3>Menu List</h3>
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
                                            <div class="text-tiny">Menu List</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <?php if ($statusMessage): ?>
                                        <div class="alert alert-<?php echo e($statusMessage['type']); ?>" role="alert">
                                            <?php echo e($statusMessage['text']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex items-center justify-between gap10 flex-wrap mb-20">
                                        <h5>Homepage Navigation Menus</h5>
                                        <a class="tf-button style-1 w208" href="add-menu.php"><i class="icon-plus"></i>Add Menu</a>
                                    </div>

                                    <div class="wg-table table-all-category">
                                        <ul class="table-title flex gap20 mb-14">
                                            <li>
                                                <div class="body-title">Menu Name</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Sort</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Created</div>
                                            </li>
                                            <li>
                                                <div class="body-title">Action</div>
                                            </li>
                                        </ul>

                                        <ul class="flex flex-column">
                                            <?php if (empty($menuItems)): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-center gap20 flex-grow" style="padding: 24px;">
                                                        <div class="body-text">No menu added yet.</div>
                                                    </div>
                                                </li>
                                            <?php endif; ?>

                                            <?php foreach ($menuItems as $item): ?>
                                                <li class="product-item gap14">
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="name">
                                                            <div class="body-title-2"><?php echo e($item['label']); ?></div>
                                                            <div class="text-tiny">Navigation bar menu</div>
                                                        </div>
                                                        <div class="body-text"><?php echo (int) $item['sort_order']; ?></div>
                                                        <div class="body-text"><?php echo date('d M Y', strtotime($item['created_at'])); ?></div>
                                                        <div class="list-icon-function">
                                                            <a href="edit-menu.php?id=<?php echo (int) $item['id']; ?>">
                                                                <div class="item edit">
                                                                    <i class="icon-edit-3"></i>
                                                                </div>
                                                            </a>
                                                        </div>
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
