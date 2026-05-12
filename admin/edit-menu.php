<?php
require_once('auth.php');
require_once('../config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
if ($id <= 0) {
    header('Location: menu-list.php?status=invalid');
    exit();
}

$message = '';
$messageType = '';

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
            $checkStmt = $conn->prepare("
                SELECT id
                FROM menu_items
                WHERE menu_type = ?
                AND LOWER(label) = LOWER(?)
                AND id != ?
                LIMIT 1
            ");
            $checkStmt->bind_param('ssi', $menuType, $menuName, $id);
            $checkStmt->execute();
            $menuExists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();

            if ($menuExists) {
                $message = 'This menu name already exists.';
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("
                    UPDATE menu_items
                    SET label = ?
                    WHERE id = ?
                    AND menu_type = 'homepage'
                    AND parent_id IS NULL
                ");
                $stmt->bind_param('si', $menuName, $id);
                $stmt->execute();
                $updatedRows = $stmt->affected_rows;
                $stmt->close();

                if ($updatedRows >= 0) {
                    header('Location: menu-list.php?status=updated');
                    exit();
                }

                $message = 'Menu could not be updated.';
                $messageType = 'danger';
            }
        } catch (Exception $e) {
            error_log('Update menu error: ' . $e->getMessage());
            $message = 'Menu could not be updated. Please try again.';
            $messageType = 'danger';
        }
    }
}

$stmt = $conn->prepare("
    SELECT id, label
    FROM menu_items
    WHERE id = ?
    AND menu_type = 'homepage'
    AND parent_id IS NULL
    LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$menuItem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$menuItem) {
    header('Location: menu-list.php?status=not_found');
    exit();
}

$menuName = $_SERVER['REQUEST_METHOD'] === 'POST' ? (isset($_POST['menu_name']) ? trim($_POST['menu_name']) : '') : $menuItem['label'];
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Edit Menu | Admin Panel</title>
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

        .button-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-submit {
            background: #2196F3;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #1976D2;
        }

        .btn-cancel {
            background: #999;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #777;
            color: white;
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
                                    <h3>Edit Menu</h3>
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
                                            <a href="menu-list.php">
                                                <div class="text-tiny">Menu List</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Edit Menu</div>
                                        </li>
                                    </ul>
                                </div>

                                <form method="post" action="edit-menu.php" class="wg-box simple-menu-form">
                                    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                                    <h5 class="mb-20">Update Navigation Menu</h5>

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
                                            maxlength="255"
                                            value="<?php echo e($menuName); ?>"
                                            required>
                                    </div>

                                    <div class="button-group">
                                        <button type="submit" class="btn-submit">Update Menu</button>
                                        <a href="menu-list.php" class="btn-cancel">Cancel</a>
                                    </div>
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
