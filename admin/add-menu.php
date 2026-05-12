<?php
require_once('auth.php');
require_once('../config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = '';
$menuName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuName = isset($_POST['menu_name']) ? trim($_POST['menu_name']) : '';

    if ($menuName === '') {
        $message = 'Please enter a menu name.';
        $messageType = 'danger';
    } elseif (strlen($menuName) > 255) {
        $message = 'Menu name must be 255 characters or less.';
        $messageType = 'danger';
    } elseif (in_array(strtolower($menuName), ['home', 'about us', 'contact us'], true)) {
        $message = 'Home, About Us, and Contact Us already exist in the navigation bar.';
        $messageType = 'danger';
    } else {
        try {
            $menuType = 'homepage';
            $checkStmt = $conn->prepare("SELECT id FROM menu_items WHERE menu_type = ? AND LOWER(label) = LOWER(?) LIMIT 1");
            $checkStmt->bind_param('ss', $menuType, $menuName);
            $checkStmt->execute();
            $menuExists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();

            if ($menuExists) {
                $message = 'This menu is already added.';
                $messageType = 'danger';
            } else {
                $orderResult = $conn->query("SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM menu_items WHERE menu_type = 'homepage'");
                $nextOrderRow = $orderResult ? $orderResult->fetch_assoc() : ['next_order' => 1];
                $sortOrder = (int) $nextOrderRow['next_order'];

                $parentId = null;
                $categoryId = null;
                $url = '#';
                $isActive = 1;

                $stmt = $conn->prepare("
                    INSERT INTO menu_items (parent_id, category_id, label, url, menu_type, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('iisssii', $parentId, $categoryId, $menuName, $url, $menuType, $sortOrder, $isActive);
                $stmt->execute();
                $stmt->close();

                header('Location: menu-list.php?status=added');
                exit();
            }
        } catch (Exception $e) {
            error_log('Add menu error: ' . $e->getMessage());
            $message = 'Menu could not be added. Please try again.';
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Add Menu | Admin Panel</title>
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .simple-menu-form {
            max-width: 620px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
        }

        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #45a049;
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
                                    <h3>Add Menu</h3>
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
                                            <div class="text-tiny">Add Menu</div>
                                        </li>
                                    </ul>
                                </div>

                                <form method="post" action="add-menu.php" class="wg-box simple-menu-form">
                                    <h5 class="mb-20">Add Navigation Menu</h5>

                                    <?php if ($message !== ''): ?>
                                        <div class="alert alert-<?php echo e($messageType); ?>" role="alert">
                                            <?php echo e($message); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="menuName">Menu Name <span style="color: #d32f2f;">*</span></label>
                                        <input
                                            type="text"
                                            id="menuName"
                                            name="menu_name"
                                            placeholder="Example: Shop"
                                            maxlength="255"
                                            value="<?php echo e($menuName); ?>"
                                            required>
                                    </div>

                                    <button type="submit" class="btn-submit">Add Menu</button>
                                    <a href="menu-list.php" style="margin-left: 12px;">View / Edit Menus</a>
                                </form>
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
