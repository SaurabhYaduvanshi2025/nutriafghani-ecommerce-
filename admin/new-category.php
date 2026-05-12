<?php
require_once('auth.php');
require_once('../config/db.php');

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

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
                                    <h3>Category infomation</h3>
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
                                            <div class="text-tiny">New category</div>
                                        </li>
                                    </ul>
                                </div>
                                <!-- new-category -->
                                <div class="wg-box">
                                    <form id="newCategoryForm" class="form-new-product form-style-1" method="post" action="process-category.php" enctype="multipart/form-data">
                                        <fieldset class="name">
                                            <div class="body-title">
                                                Category name <span class="tf-color-1">*</span>
                                            </div>
                                            <input class="flex-grow" type="text" placeholder="Enter category name" name="categoryName" id="categoryName" tabindex="0" value="" aria-required="true" required="" />
                                        </fieldset>
                                        <fieldset class="name">
                                            <div class="body-title">
                                                Select menu <span class="tf-color-1">*</span>
                                            </div>
                                            <select class="flex-grow" name="menu_id" id="menuSelect" required>
                                                <option value="">Choose menu</option>
                                                <?php foreach ($menuItems as $menuItem): ?>
                                                    <option value="<?php echo (int) $menuItem['id']; ?>">
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
                                            <input class="flex-grow" type="text" placeholder="category-slug" name="slug" id="categorySlug" tabindex="0" value="" aria-required="true" required="" />
                                            <small style="display: block; margin-top: 5px; color: #666;">Auto-generated from category name (can be edited manually)</small>
                                        </fieldset>
                                        <fieldset>
                                            <div class="body-title">
                                                Upload images <span class="tf-color-1">*</span>
                                            </div>
                                            <div class="upload-image flex-grow">
                                                <div class="item up-load">
                                                    <label class="uploadfile" for="myFile">
                                                        <span class="icon">
                                                            <i class="icon-upload-cloud"></i>
                                                        </span>
                                                        <span class="body-text">Drop your images here or select
                                                            <span class="tf-color">click to browse</span></span>
                                                        <input type="file" id="myFile" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required />
                                                    </label>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <div class="bot">
                                            <div></div>
                                            <button class="tf-button w208" type="submit">
                                                Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /new-category -->
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
    
    <script>
        // Auto-generate slug from category name
        const categoryNameInput = document.getElementById('categoryName');
        const categorySlugInput = document.getElementById('categorySlug');

        if (categoryNameInput && categorySlugInput) {
            categoryNameInput.addEventListener('input', function() {
                const slug = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/[\s_]+/g, '-')   // Replace spaces and underscores with hyphens
                    .replace(/-+/g, '-')       // Replace multiple hyphens with single hyphen
                    .replace(/^-+|-+$/g, '');  // Remove leading/trailing hyphens
                
                categorySlugInput.value = slug;
            });

            // Handle form submission
            const form = document.getElementById('newCategoryForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const categoryName = categoryNameInput.value.trim();
                    const categorySlug = categorySlugInput.value.trim();
                    const fileInput = document.getElementById('myFile');
                    const menuSelect = document.getElementById('menuSelect');

                    if (!categoryName) {
                        alert('Please enter a category name');
                        return;
                    }

                    if (!menuSelect.value) {
                        alert('Please select a menu');
                        return;
                    }

                    if (!categorySlug) {
                        alert('Please enter or auto-generate a slug');
                        return;
                    }

                    if (!fileInput.files.length) {
                        alert('Please upload an image');
                        return;
                    }

                    // Create FormData for file upload
                    const formData = new FormData(this);
                    formData.set('categoryName', categoryName);
                    formData.set('menu_id', menuSelect.value);
                    formData.set('slug', categorySlug);

                    // Send to backend
                    const submitBtn = this.querySelector('[type="submit"]');
                    const originalText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';

                    try {
                        const response = await fetch('process-category.php', {
                            method: 'POST',
                            body: formData
                        });

                        const responseText = await response.text();
                        let data;

                        try {
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            throw new Error(responseText || `Request failed with status ${response.status}`);
                        }

                        if (data.success) {
                            alert('Category added successfully!');
                            window.location.href = 'category-list.php';
                            return;
                        }

                        alert('Error: ' + (data.message || 'Failed to add category'));
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error: ' + error.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
            }
        }
    </script>

</body>

</html>
