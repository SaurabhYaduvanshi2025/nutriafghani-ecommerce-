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

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: product-list.php');
    exit();
}

$productStmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$productStmt->bind_param('i', $productId);
$productStmt->execute();
$product = $productStmt->get_result()->fetch_assoc();
$productStmt->close();

if (!$product) {
    header('Location: product-list.php');
    exit();
}

$categories = [];
$categoryResult = $conn->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
if ($categoryResult) {
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
}

$variantStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY weight_grams ASC LIMIT 1");
$variantStmt->bind_param('i', $productId);
$variantStmt->execute();
$variant = $variantStmt->get_result()->fetch_assoc();
$variantStmt->close();

$selectedWeight = $variant['weight_value'] ?? '500g';
$selectedGrams = (int) ($variant['weight_grams'] ?? 500);
$selectedMultiplier = $selectedGrams <= 250 ? 0.5 : ($selectedGrams >= 1000 ? 2 : 1);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title>Edit Product - Nutri Afghan</title>
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .form-group { margin-bottom: 20px; }
        .error-message { color: #d32f2f; font-size: 12px; margin-top: 5px; }
        .form-message { display: none; padding: 12px 14px; border-radius: 6px; margin-bottom: 18px; font-size: 14px; }
        .form-message.success { display: block; color: #1b5e20; background: #e8f5e9; }
        .form-message.error { display: block; color: #b71c1c; background: #ffebee; }
        .current-images { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .current-images img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        .weight-options { display: flex; flex-wrap: wrap; gap: 12px; }
        .weight-btn { padding: 12px 20px; border: 2px solid #ddd; background: #fff; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .weight-btn.active { border-color: #7cb342; background: #7cb342; color: #fff; }
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
                                    <h3>Edit product</h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li><a href="./"><div class="text-tiny">Dashboard</div></a></li>
                                        <li><i class="icon-chevron-right"></i></li>
                                        <li><a href="product-list.php"><div class="text-tiny">Products</div></a></li>
                                        <li><i class="icon-chevron-right"></i></li>
                                        <li><div class="text-tiny">Edit product</div></li>
                                    </ul>
                                </div>

                                <div id="formMessage" class="form-message"></div>

                                <form id="editProductForm" class="tf-section-2 form-add-product" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                                    <input type="hidden" name="selected_weight" id="selectedWeight" value="<?php echo e($selectedWeight); ?>">
                                    <input type="hidden" name="weight_grams" id="weightGrams" value="<?php echo (int) $selectedGrams; ?>">
                                    <input type="hidden" name="weight_multiplier" id="weightMultiplier" value="<?php echo e($selectedMultiplier); ?>">

                                    <div class="wg-box">
                                        <h5 class="mb-20">Basic Information</h5>

                                        <fieldset class="name form-group">
                                            <div class="body-title mb-10">Product Name <span class="tf-color-1">*</span></div>
                                            <input class="mb-10" type="text" name="product_name" value="<?php echo e($product['name']); ?>" maxlength="255" required>
                                        </fieldset>

                                        <fieldset class="category form-group">
                                            <div class="body-title mb-10">Category <span class="tf-color-1">*</span></div>
                                            <div class="select">
                                                <select name="category_id" required>
                                                    <option value="">Choose category</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo (int) $category['id']; ?>" <?php echo (int) $product['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>>
                                                            <?php echo e($category['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </fieldset>

                                        <fieldset class="description form-group">
                                            <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                                            <textarea class="mb-10" name="description" maxlength="1000" required><?php echo e($product['description']); ?></textarea>
                                        </fieldset>

                                        <fieldset class="description form-group">
                                            <div class="body-title mb-10">Short Description</div>
                                            <input type="text" name="short_description" value="<?php echo e($product['short_description']); ?>" maxlength="500">
                                        </fieldset>
                                    </div>

                                    <div class="wg-box">
                                        <h5 class="mb-20">Pricing & Weight</h5>
                                        <div class="gap22 cols">
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Original Price <span class="tf-color-1">*</span></div>
                                                <input type="number" name="original_price" value="<?php echo e($product['original_price']); ?>" min="0" step="0.01" required>
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Discount Price <span class="tf-color-1">*</span></div>
                                                <input type="number" name="discount_price" value="<?php echo e($product['discount_price']); ?>" min="0" step="0.01" required>
                                            </fieldset>
                                        </div>

                                        <div class="body-title mb-15">Main Weight Variant</div>
                                        <div class="weight-options">
                                            <button type="button" class="weight-btn <?php echo $selectedGrams === 250 ? 'active' : ''; ?>" data-weight="250g" data-grams="250" data-multiplier="0.5">250g</button>
                                            <button type="button" class="weight-btn <?php echo $selectedGrams === 500 ? 'active' : ''; ?>" data-weight="500g" data-grams="500" data-multiplier="1">500g</button>
                                            <button type="button" class="weight-btn <?php echo $selectedGrams === 1000 ? 'active' : ''; ?>" data-weight="1kg" data-grams="1000" data-multiplier="2">1kg</button>
                                        </div>
                                    </div>

                                    <div class="wg-box">
                                        <h5 class="mb-20">Images</h5>
                                        <div class="current-images">
                                            <?php foreach (['main_image', 'gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $field): ?>
                                                <?php if (!empty($product[$field])): ?>
                                                    <img src="<?php echo e(product_image_path($product[$field])); ?>" alt="<?php echo e($product['name']); ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Replace Main Image</div>
                                            <input type="file" name="main_image" accept="image/*">
                                        </fieldset>
                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Replace Gallery Image 1</div>
                                            <input type="file" name="gallery_image_1" accept="image/*">
                                        </fieldset>
                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Replace Gallery Image 2</div>
                                            <input type="file" name="gallery_image_2" accept="image/*">
                                        </fieldset>
                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Replace Gallery Image 3</div>
                                            <input type="file" name="gallery_image_3" accept="image/*">
                                        </fieldset>
                                    </div>

                                    <div class="wg-box">
                                        <h5 class="mb-20">Additional Information</h5>
                                        <div class="gap22 cols">
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">SKU</div>
                                                <input type="text" name="sku" value="<?php echo e($product['sku']); ?>" maxlength="100">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Stock Quantity</div>
                                                <input type="number" name="stock_quantity" value="<?php echo (int) $product['stock_quantity']; ?>" min="0">
                                            </fieldset>
                                        </div>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Status</div>
                                            <select name="is_active">
                                                <option value="1" <?php echo (int) $product['is_active'] === 1 ? 'selected' : ''; ?>>Active</option>
                                                <option value="0" <?php echo (int) $product['is_active'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </fieldset>

                                        <fieldset class="form-group">
                                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                                <input type="checkbox" name="is_featured" value="1" <?php echo (int) $product['is_featured'] === 1 ? 'checked' : ''; ?>>
                                                <span>Mark as featured product</span>
                                            </label>
                                        </fieldset>
                                    </div>

                                    <div class="wg-box">
                                        <div class="flex gap10 flex-wrap">
                                            <button type="submit" class="tf-button w208">Update Product</button>
                                            <a href="product-list.php" class="tf-button style-1 w208">Cancel</a>
                                        </div>
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

    <script>
        document.querySelectorAll('.weight-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.weight-btn').forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('selectedWeight').value = this.dataset.weight;
                document.getElementById('weightGrams').value = this.dataset.grams;
                document.getElementById('weightMultiplier').value = this.dataset.multiplier;
            });
        });

        document.getElementById('editProductForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const message = document.getElementById('formMessage');
            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;

            message.className = 'form-message';
            message.textContent = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            fetch('process-product.php?action=update', {
                method: 'POST',
                body: new FormData(this)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        message.className = 'form-message success';
                        message.textContent = data.message || 'Product updated successfully!';
                        setTimeout(() => {
                            window.location.href = 'view-product.php?id=<?php echo (int) $product['id']; ?>';
                        }, 700);
                        return;
                    }

                    message.className = 'form-message error';
                    message.textContent = data.message || 'Failed to update product.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                })
                .catch(error => {
                    message.className = 'form-message error';
                    message.textContent = 'Error: ' + error.message;
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        });
    </script>
</body>
</html>
