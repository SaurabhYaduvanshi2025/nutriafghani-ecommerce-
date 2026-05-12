<?php
require_once('../config/db.php');
require_once('auth.php');

// Fetch categories from database
$categories = [];
$categoryQuery = "SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY name ASC";
$result = $conn->query($categoryQuery);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - Add Product | Nutri Afghan</title>
    <?php include_once('includes/header-link.php'); ?>
    <style>
        .weight-options {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .weight-btn {
            padding: 12px 20px;
            border: 2px solid #ddd;
            background-color: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .weight-btn:hover {
            border-color: #7cb342;
            background-color: #f5f5f5;
        }

        .weight-btn.active {
            background-color: #7cb342;
            color: #fff;
            border-color: #7cb342;
        }

        .price-display-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .price-label {
            font-weight: 600;
            color: #333;
        }

        .price-value {
            font-size: 18px;
            font-weight: 700;
            color: #7cb342;
        }

        .discount-badge {
            background: #ff5252;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background-color: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-size: 18px;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background-color: #7cb342;
            color: #fff;
            border-color: #7cb342;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .variant-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .variant-info.show {
            display: block;
        }

        .error-message {
            color: #d32f2f;
            font-size: 12px;
            margin-top: 5px;
        }

        .success-message {
            color: #388e3c;
            font-size: 12px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .category-info {
            background: #e3f2fd;
            padding: 12px;
            border-radius: 4px;
            margin-top: 10px;
            display: none;
            font-size: 13px;
        }

        .category-info.show {
            display: block;
        }
    </style>
</head>

<body class="body">
    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <!-- layout-wrap -->
            <div class="layout-wrap menu-style-icon">
                <!-- preload -->
                <?php include_once('includes/preloader.php'); ?>
                <!-- /preload -->

                <!-- section-menu-left -->
                <?php include_once('includes/sidebar.php'); ?>
                <!-- /section-menu-left -->

                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php include_once('includes/top-header.php'); ?>
                    <!-- /header-dashboard -->

                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>Add Product</h3>
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
                                            <a href="product-list.php">
                                                <div class="text-tiny">Products</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Add product</div>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Message Display -->
                                <div id="responseMessage" class="wg-box" style="display: none; margin-bottom: 20px;">
                                    <div id="messageContent"></div>
                                </div>

                                <!-- form-add-product -->
                                <form id="addProductForm" class="tf-section-2 form-add-product" enctype="multipart/form-data">
                                    <!-- BASIC INFORMATION -->
                                    <div class="wg-box">
                                        <h5 class="mb-20">Basic Information</h5>

                                        <!-- Product Name -->
                                        <fieldset class="name form-group">
                                            <div class="body-title mb-10">Product Name <span class="tf-color-1">*</span></div>
                                            <input 
                                                class="mb-10" 
                                                type="text" 
                                                id="productName"
                                                placeholder="Enter product name"
                                                name="product_name" 
                                                maxlength="255"
                                                required>
                                            <div class="text-tiny">Max 255 characters</div>
                                            <div id="nameError" class="error-message"></div>
                                        </fieldset>

                                        <!-- Category Selection -->
                                        <div class="gap22 cols">
                                            <fieldset class="category form-group">
                                                <div class="body-title mb-10">Category <span class="tf-color-1">*</span></div>
                                                <div class="select">
                                                    <select id="categorySelect" name="category_id" required>
                                                        <option value="">Choose category</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?php echo $cat['id']; ?>" data-slug="<?php echo htmlspecialchars($cat['slug']); ?>">
                                                                <?php echo htmlspecialchars($cat['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div id="categoryInfo" class="category-info">
                                                    <strong>Slug:</strong> <span id="categorySlugDisplay"></span>
                                                </div>
                                                <div id="categoryError" class="error-message"></div>
                                            </fieldset>
                                        </div>

                                        <!-- Product Description -->
                                        <fieldset class="description form-group">
                                            <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                                            <textarea 
                                                class="mb-10" 
                                                id="productDescription"
                                                name="description" 
                                                placeholder="Enter detailed product description"
                                                maxlength="1000"
                                                required></textarea>
                                            <div class="text-tiny">Max 1000 characters</div>
                                            <div id="descError" class="error-message"></div>
                                        </fieldset>

                                        <!-- Short Description -->
                                        <fieldset class="description form-group">
                                            <div class="body-title mb-10">Short Description</div>
                                            <input 
                                                type="text" 
                                                id="shortDescription"
                                                placeholder="Brief description for listings"
                                                name="short_description"
                                                maxlength="500">
                                            <div class="text-tiny">Max 500 characters</div>
                                        </fieldset>
                                    </div>

                                    <!-- PRICING & WEIGHT OPTIONS -->
                                    <div class="wg-box">
                                        <h5 class="mb-20">Pricing & Weight Options</h5>

                                        <!-- Base Pricing -->
                                        <div class="gap22 cols">
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Original Price <span class="tf-color-1">*</span></div>
                                                <input 
                                                    type="number" 
                                                    id="originalPrice"
                                                    placeholder="Enter original price"
                                                    name="original_price" 
                                                    min="0"
                                                    step="0.01"
                                                    required>
                                                <div id="originalPriceError" class="error-message"></div>
                                            </fieldset>

                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Discount Price <span class="tf-color-1">*</span></div>
                                                <input 
                                                    type="number" 
                                                    id="discountPrice"
                                                    placeholder="Enter discount price"
                                                    name="discount_price" 
                                                    min="0"
                                                    step="0.01"
                                                    required>
                                                <div id="discountPriceError" class="error-message"></div>
                                            </fieldset>
                                        </div>

                                        <!-- Weight Options Header -->
                                        <div class="form-group">
                                            <div class="body-title mb-15">Select Weight Options <span class="tf-color-1">*</span></div>
                                            <div class="weight-options">
                                                <button type="button" class="weight-btn" data-weight="250g" data-grams="250" data-multiplier="0.5">
                                                    250g
                                                </button>
                                                <button type="button" class="weight-btn" data-weight="500g" data-grams="500" data-multiplier="1">
                                                    500g
                                                </button>
                                                <button type="button" class="weight-btn" data-weight="1kg" data-grams="1000" data-multiplier="2">
                                                    1kg
                                                </button>
                                            </div>
                                            <div id="weightError" class="error-message"></div>
                                        </div>

                                        <!-- Price Display Section -->
                                        <div id="priceDisplaySection" class="price-display-section" style="display: none;">
                                            <h6 style="margin-bottom: 15px;">Selected Weight: <span id="selectedWeightDisplay"></span></h6>
                                            <div class="price-row">
                                                <span class="price-label">Original Price:</span>
                                                <span class="price-value">Rs. <span id="displayOriginalPrice">0</span></span>
                                            </div>
                                            <div class="price-row">
                                                <span class="price-label">Discount Price:</span>
                                                <div>
                                                    <span class="price-value">Rs. <span id="displayDiscountPrice">0</span></span>
                                                    <span id="discountBadge" class="discount-badge" style="display: none;"></span>
                                                </div>
                                            </div>
                                            <div class="price-row">
                                                <span class="price-label">You Save:</span>
                                                <span class="price-value" style="color: #ff5252;">Rs. <span id="displaySaveAmount">0</span></span>
                                            </div>

                                            <!-- Quantity Selector -->
                                            <div class="quantity-selector">
                                                <span class="price-label">Quantity:</span>
                                                <button type="button" class="qty-btn" id="decreaseQty">−</button>
                                                <input type="number" id="quantityInput" class="qty-input" value="1" min="1" max="999">
                                                <button type="button" class="qty-btn" id="increaseQty">+</button>
                                            </div>

                                            <div class="variant-info show">
                                                <strong>Weight Selected:</strong> <span id="variantWeightLabel"></span> | 
                                                <strong>Final Price:</strong> Rs. <span id="variantFinalPrice">0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IMAGES -->
                                    <div class="wg-box">
                                        <h5 class="mb-20">Upload Images</h5>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Main Image <span class="tf-color-1">*</span></div>
                                            <input 
                                                type="file" 
                                                id="mainImage"
                                                name="main_image" 
                                                accept="image/*"
                                                required>
                                            <div class="text-tiny">JPG, PNG. Max 5MB</div>
                                            <div id="mainImageError" class="error-message"></div>
                                        </fieldset>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Gallery Image 1</div>
                                            <input 
                                                type="file" 
                                                id="galleryImage1"
                                                name="gallery_image_1" 
                                                accept="image/*">
                                            <div class="text-tiny">JPG, PNG. Max 5MB</div>
                                        </fieldset>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Gallery Image 2</div>
                                            <input 
                                                type="file" 
                                                id="galleryImage2"
                                                name="gallery_image_2" 
                                                accept="image/*">
                                            <div class="text-tiny">JPG, PNG. Max 5MB</div>
                                        </fieldset>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Gallery Image 3</div>
                                            <input 
                                                type="file" 
                                                id="galleryImage3"
                                                name="gallery_image_3" 
                                                accept="image/*">
                                            <div class="text-tiny">JPG, PNG. Max 5MB</div>
                                        </fieldset>
                                    </div>

                                    <!-- ADDITIONAL INFO -->
                                    <div class="wg-box">
                                        <h5 class="mb-20">Additional Information</h5>

                                        <div class="gap22 cols">
                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">SKU (Stock Keeping Unit)</div>
                                                <input 
                                                    type="text" 
                                                    id="sku"
                                                    placeholder="e.g., SAF-001"
                                                    name="sku"
                                                    maxlength="100">
                                                <div class="text-tiny">Unique identifier for this product</div>
                                            </fieldset>

                                            <fieldset class="form-group">
                                                <div class="body-title mb-10">Stock Quantity</div>
                                                <input 
                                                    type="number" 
                                                    id="stockQuantity"
                                                    placeholder="Enter stock quantity"
                                                    name="stock_quantity" 
                                                    min="0"
                                                    value="0">
                                                <div id="stockError" class="error-message"></div>
                                            </fieldset>
                                        </div>

                                        <fieldset class="form-group">
                                            <div class="body-title mb-10">Featured Product</div>
                                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                                <input 
                                                    type="checkbox" 
                                                    id="isFeatured"
                                                    name="is_featured" 
                                                    value="1">
                                                <span>Mark as featured product</span>
                                            </label>
                                        </fieldset>
                                    </div>

                                    <!-- SUBMIT BUTTON -->
                                    <div class="wg-box">
                                        <button type="submit" class="btn btn-primary">
                                            <span>Add Product</span>
                                        </button>
                                    </div>
                                </form>
                                <!-- /form-add-product -->
                            </div>
                            <!-- /main-content-wrap -->
                        </div>
                        <!-- /main-content-inner -->
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

    <?php include_once('includes/footer-link.php'); ?>

    <script>
        // =====================================================
        // ADD PRODUCT FORM - JAVASCRIPT/AJAX LOGIC
        // =====================================================

        let selectedWeight = null;
        let selectedWeightData = null;

        // ===== CATEGORY SELECTION HANDLER =====
        document.getElementById('categorySelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const slug = selectedOption.getAttribute('data-slug');
            const categoryInfo = document.getElementById('categoryInfo');
            const categorySlugDisplay = document.getElementById('categorySlugDisplay');

            if (slug) {
                categorySlugDisplay.textContent = slug;
                categoryInfo.classList.add('show');
            } else {
                categoryInfo.classList.remove('show');
            }
        });

        // ===== WEIGHT OPTION HANDLER =====
        document.querySelectorAll('.weight-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all buttons
                document.querySelectorAll('.weight-btn').forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Store selected weight
                selectedWeight = this.getAttribute('data-weight');
                selectedWeightData = {
                    weight: this.getAttribute('data-weight'),
                    grams: parseInt(this.getAttribute('data-grams')),
                    multiplier: parseFloat(this.getAttribute('data-multiplier'))
                };
                
                // Update price display
                updatePriceDisplay();
            });
        });

        // ===== UPDATE PRICE DISPLAY =====
        function updatePriceDisplay() {
            const originalPrice = parseFloat(document.getElementById('originalPrice').value) || 0;
            const discountPrice = parseFloat(document.getElementById('discountPrice').value) || 0;
            
            if (!selectedWeightData) {
                document.getElementById('priceDisplaySection').style.display = 'none';
                return;
            }
            
            const multiplier = selectedWeightData.multiplier;
            const variantOriginalPrice = (originalPrice * multiplier).toFixed(2);
            const variantDiscountPrice = (discountPrice * multiplier).toFixed(2);
            const saveAmount = (variantOriginalPrice - variantDiscountPrice).toFixed(2);
            const discountPercentage = originalPrice > 0 ? Math.round(((originalPrice - discountPrice) / originalPrice) * 100) : 0;
            
            // Update display
            document.getElementById('selectedWeightDisplay').textContent = selectedWeight;
            document.getElementById('displayOriginalPrice').textContent = variantOriginalPrice;
            document.getElementById('displayDiscountPrice').textContent = variantDiscountPrice;
            document.getElementById('displaySaveAmount').textContent = saveAmount;
            document.getElementById('variantWeightLabel').textContent = selectedWeight;
            document.getElementById('variantFinalPrice').textContent = variantDiscountPrice;
            
            // Update discount badge
            const badgeElement = document.getElementById('discountBadge');
            if (discountPercentage > 0) {
                badgeElement.textContent = discountPercentage + '% OFF';
                badgeElement.style.display = 'inline-block';
            } else {
                badgeElement.style.display = 'none';
            }
            
            // Show price display section
            document.getElementById('priceDisplaySection').style.display = 'block';
            
            // Update quantity
            updateQuantityPrice();
        }

        // ===== QUANTITY SELECTOR LOGIC =====
        document.getElementById('increaseQty').addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.getElementById('quantityInput');
            input.value = Math.min(parseInt(input.value) + 1, 999);
            updateQuantityPrice();
        });

        document.getElementById('decreaseQty').addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.getElementById('quantityInput');
            input.value = Math.max(parseInt(input.value) - 1, 1);
            updateQuantityPrice();
        });

        document.getElementById('quantityInput').addEventListener('change', function() {
            this.value = Math.max(1, Math.min(parseInt(this.value) || 1, 999));
            updateQuantityPrice();
        });

        function updateQuantityPrice() {
            if (!selectedWeightData) return;
            
            const discountPrice = parseFloat(document.getElementById('discountPrice').value) || 0;
            const quantity = parseInt(document.getElementById('quantityInput').value) || 1;
            const variantPrice = (discountPrice * selectedWeightData.multiplier).toFixed(2);
            const totalPrice = (variantPrice * quantity).toFixed(2);
            
            document.getElementById('variantFinalPrice').textContent = totalPrice;
        }

        // ===== REAL-TIME PRICE UPDATE ON INPUT CHANGE =====
        document.getElementById('originalPrice').addEventListener('input', updatePriceDisplay);
        document.getElementById('discountPrice').addEventListener('input', updatePriceDisplay);

        // ===== FORM SUBMISSION (AJAX) =====
        document.getElementById('addProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validation
            if (!validateForm()) {
                return;
            }
            
            // Prepare form data
            const formData = new FormData(this);
            
            // Add selected weight and quantity
            if (selectedWeightData) {
                formData.append('selected_weight', selectedWeight);
                formData.append('weight_grams', selectedWeightData.grams);
                formData.append('weight_multiplier', selectedWeightData.multiplier);
                formData.append('quantity', document.getElementById('quantityInput').value);
            }
            
            // Add category slug
            const categorySelect = document.getElementById('categorySelect');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categorySlug = selectedOption.getAttribute('data-slug');
            formData.append('category_slug', categorySlug);
            
            try {
                // Log form data for debugging
                console.log('Form Data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
                
                const response = await fetch('process-add-product.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Log response status
                console.log('Response Status:', response.status);
                console.log('Response Headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Raw Response:', responseText);
                
                const result = JSON.parse(responseText);
                showMessage(result.message, result.success ? 'success' : 'error');
                
                if (result.success) {
                    // Reset form
                    document.getElementById('addProductForm').reset();
                    document.querySelectorAll('.weight-btn').forEach(b => b.classList.remove('active'));
                    document.getElementById('priceDisplaySection').style.display = 'none';
                    document.getElementById('categoryInfo').classList.remove('show');
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'product-list.php';
                    }, 2000);
                }
            } catch (error) {
                showMessage('Error submitting form: ' + error.message, 'error');
                console.error('Error:', error);
                console.error('Stack:', error.stack);
            }
        });

        // ===== FORM VALIDATION =====
        function validateForm() {
            let isValid = true;
            
            // Clear all error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Product name validation
            const productName = document.getElementById('productName').value.trim();
            if (!productName) {
                document.getElementById('nameError').textContent = 'Product name is required';
                isValid = false;
            } else if (productName.length > 255) {
                document.getElementById('nameError').textContent = 'Product name exceeds 255 characters';
                isValid = false;
            }
            
            // Category validation
            if (!document.getElementById('categorySelect').value) {
                document.getElementById('categoryError').textContent = 'Please select a category';
                isValid = false;
            }
            
            // Description validation
            const description = document.getElementById('productDescription').value.trim();
            if (!description) {
                document.getElementById('descError').textContent = 'Description is required';
                isValid = false;
            }
            
            // Price validation
            const originalPrice = parseFloat(document.getElementById('originalPrice').value);
            if (isNaN(originalPrice) || originalPrice <= 0) {
                document.getElementById('originalPriceError').textContent = 'Original price must be greater than 0';
                isValid = false;
            }
            
            const discountPrice = parseFloat(document.getElementById('discountPrice').value);
            if (isNaN(discountPrice) || discountPrice <= 0) {
                document.getElementById('discountPriceError').textContent = 'Discount price must be greater than 0';
                isValid = false;
            }
            
            if (discountPrice >= originalPrice) {
                document.getElementById('discountPriceError').textContent = 'Discount price must be less than original price';
                isValid = false;
            }
            
            // Weight selection validation
            if (!selectedWeight) {
                document.getElementById('weightError').textContent = 'Please select at least one weight option';
                isValid = false;
            }
            
            // Stock quantity validation
            const stockQuantity = parseInt(document.getElementById('stockQuantity').value);
            if (isNaN(stockQuantity) || stockQuantity < 0) {
                document.getElementById('stockError').textContent = 'Stock quantity must be 0 or more';
                isValid = false;
            }
            
            // Main image validation
            const mainImage = document.getElementById('mainImage').files;
            if (mainImage.length === 0) {
                document.getElementById('mainImageError').textContent = 'Main image is required';
                isValid = false;
            } else if (mainImage[0].size > 5 * 1024 * 1024) {
                document.getElementById('mainImageError').textContent = 'Main image size must be less than 5MB';
                isValid = false;
            }
            
            return isValid;
        }

        // ===== SHOW MESSAGE FUNCTION =====
        function showMessage(message, type) {
            const messageContainer = document.getElementById('responseMessage');
            const messageContent = document.getElementById('messageContent');
            
            messageContent.innerHTML = `
                <div style="padding: 15px; border-radius: 6px; background-color: ${type === 'success' ? '#c8e6c9' : '#ffcdd2'}; color: ${type === 'success' ? '#2e7d32' : '#d32f2f'}; border-left: 4px solid ${type === 'success' ? '#2e7d32' : '#d32f2f'};">
                    ${message}
                </div>
            `;
            messageContainer.style.display = 'block';
            
            // Auto-hide error messages after 5 seconds
            if (type === 'error') {
                setTimeout(() => {
                    messageContainer.style.display = 'none';
                }, 5000);
            }
        }
    </script>
</body>

</html>