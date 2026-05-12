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

$categoryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($categoryId <= 0) {
    header('Location: category-list.php');
    exit();
}

$categoryStmt = $conn->prepare("SELECT id, menu_id, name, slug, image, is_active FROM categories WHERE id = ? LIMIT 1");
$categoryStmt->bind_param('i', $categoryId);
$categoryStmt->execute();
$category = $categoryStmt->get_result()->fetch_assoc();
$categoryStmt->close();

if (!$category) {
    header('Location: category-list.php');
    exit();
}

$menuItems = [];
$menuStmt = $conn->prepare("
    SELECT id, label
    FROM menu_items
    WHERE menu_type = 'homepage'
    AND parent_id IS NULL
    AND is_active = 1
    ORDER BY sort_order ASC, id ASC
");
$menuStmt->execute();
$menuResult = $menuStmt->get_result();
if ($menuResult) {
    $menuItems = $menuResult->fetch_all(MYSQLI_ASSOC);
}
$menuStmt->close();
?>

<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <title>Edit Category - Nutri Afghan</title>

    <?php include_once('includes/header-link.php'); ?>

    <style>
        .category-current-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #eee;
            margin-bottom: 12px;
        }

        .category-current-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-message {
            display: none;
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-message.success {
            display: block;
            color: #1b5e20;
            background: #e8f5e9;
        }

        .form-message.error {
            display: block;
            color: #b71c1c;
            background: #ffebee;
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
                                    <h3>Edit category</h3>
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
                                            <div class="text-tiny">Edit category</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div id="formMessage" class="form-message"></div>

                                    <form id="editCategoryForm" class="form-new-product form-style-1" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo (int) $category['id']; ?>">

                                        <fieldset class="name">
                                            <div class="body-title">
                                                Category name <span class="tf-color-1">*</span>
                                            </div>
                                            <input class="flex-grow" type="text" placeholder="Enter category name" name="categoryName" id="categoryName" value="<?php echo e($category['name']); ?>" required>
                                        </fieldset>

                                        <fieldset class="name">
                                            <div class="body-title">
                                                Select menu <span class="tf-color-1">*</span>
                                            </div>
                                            <select name="menu_id" class="flex-grow" required>
                                                <option value="">Choose menu</option>
                                                <?php foreach ($menuItems as $menuItem): ?>
                                                    <option value="<?php echo (int) $menuItem['id']; ?>" <?php echo (int) $category['menu_id'] === (int) $menuItem['id'] ? 'selected' : ''; ?>>
                                                        <?php echo e($menuItem['label']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small style="display: block; margin-top: 5px; color: #666;">This category will show under the selected menu in the navigation bar.</small>
                                        </fieldset>

                                        <fieldset class="name">
                                            <div class="body-title">
                                                Slug (SEO) <span class="tf-color-1">*</span>
                                            </div>
                                            <input class="flex-grow" type="text" placeholder="category-slug" name="slug" id="categorySlug" value="<?php echo e($category['slug']); ?>" pattern="[a-z0-9-]+" required>
                                            <small style="display: block; margin-top: 5px; color: #666;">Use lowercase letters, numbers, and hyphens only. It updates from the category name and can be edited manually.</small>
                                        </fieldset>

                                        <fieldset class="name">
                                            <div class="body-title">
                                                Status
                                            </div>
                                            <select name="is_active" class="flex-grow">
                                                <option value="1" <?php echo (int) $category['is_active'] === 1 ? 'selected' : ''; ?>>Active</option>
                                                <option value="0" <?php echo (int) $category['is_active'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </fieldset>

                                        <fieldset>
                                            <div class="body-title">
                                                Category image
                                            </div>
                                            <div class="flex-grow">
                                                <div class="category-current-image">
                                                    <img src="<?php echo e(category_image_path($category['image'])); ?>" alt="<?php echo e($category['name']); ?>">
                                                </div>
                                                <div class="upload-image">
                                                    <div class="item up-load">
                                                        <label class="uploadfile" for="myFile">
                                                            <span class="icon">
                                                                <i class="icon-upload-cloud"></i>
                                                            </span>
                                                            <span class="body-text">Drop your new image here or select
                                                                <span class="tf-color">click to browse</span></span>
                                                            <input type="file" id="myFile" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                                        </label>
                                                    </div>
                                                </div>
                                                <small style="display: block; margin-top: 5px; color: #666;">Leave empty to keep the current image.</small>
                                            </div>
                                        </fieldset>

                                        <div class="bot">
                                            <a class="tf-button style-1 w208" href="category-list.php">Cancel</a>
                                            <button class="tf-button w208" type="submit">
                                                Update
                                            </button>
                                        </div>
                                    </form>
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

    <script>
        const categoryNameInput = document.getElementById('categoryName');
        const categorySlugInput = document.getElementById('categorySlug');
        const formMessage = document.getElementById('formMessage');
        let slugEdited = false;

        function makeSlug(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        categorySlugInput.addEventListener('input', function () {
            slugEdited = true;
            this.value = makeSlug(this.value);
            this.setSelectionRange(this.value.length, this.value.length);
        });

        categorySlugInput.addEventListener('blur', function () {
            this.value = makeSlug(this.value || categoryNameInput.value);
        });

        categoryNameInput.addEventListener('input', function () {
            if (!slugEdited) {
                categorySlugInput.value = makeSlug(this.value);
            }
        });

        document.getElementById('editCategoryForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;
            categorySlugInput.value = makeSlug(categorySlugInput.value || categoryNameInput.value);

            if (!categorySlugInput.value) {
                formMessage.className = 'form-message error';
                formMessage.textContent = 'Please enter a valid SEO slug.';
                return;
            }

            const formData = new FormData(this);

            formMessage.className = 'form-message';
            formMessage.textContent = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            fetch('process-category.php?action=update', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        formMessage.className = 'form-message success';
                        formMessage.textContent = data.message || 'Category updated successfully!';
                        setTimeout(() => {
                            window.location.href = 'category-list.php';
                        }, 700);
                        return;
                    }

                    formMessage.className = 'form-message error';
                    formMessage.textContent = data.message || 'Failed to update category.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                })
                .catch(error => {
                    formMessage.className = 'form-message error';
                    formMessage.textContent = 'Error: ' + error.message;
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });
    </script>
</body>

</html>
