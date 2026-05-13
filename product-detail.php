<?php
require_once('config/db.php');
require_once('includes/customer-auth.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function product_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'uploads/' . ltrim($path, '/');
}

function format_price($value)
{
    return 'Rs. ' . number_format((float) $value, 2);
}

function variant_display_label($variant)
{
    return trim((string) ($variant['weight_value'] ?: $variant['weight_label']));
}

function build_default_variant($product, $weightValue, $weightGrams, $multiplier)
{
    $originalPrice = round((float) $product['original_price'] * $multiplier, 2);
    $discountPrice = round((float) $product['discount_price'] * $multiplier, 2);

    return [
        'id' => 0,
        'product_id' => (int) $product['id'],
        'weight_label' => $weightValue,
        'weight_value' => $weightValue,
        'weight_grams' => $weightGrams,
        'variant_price' => $originalPrice,
        'variant_discount_price' => $discountPrice > 0 ? $discountPrice : $originalPrice,
        'variant_sku' => '',
        'stock_quantity' => (int) ($product['stock_quantity'] ?? 0),
        'is_active' => 1,
    ];
}

// Get product slug or ID from URL
$productSlug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$product = null;
$variants = [];

if (!empty($productSlug)) {
    // Fetch product by slug
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_active = 1
        LIMIT 1
    ");
    $stmt->bind_param("s", $productSlug);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
} elseif ($productId > 0) {
    // Fetch product by ID
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// If product not found, redirect to collection
if (!$product) {
    header('Location: collection.php');
    exit;
}

// Fetch product variants
$variantStmt = $conn->prepare("
    SELECT * FROM product_variants
    WHERE product_id = ? AND is_active = 1
    ORDER BY weight_grams ASC
");
$variantStmt->bind_param("i", $product['id']);
$variantStmt->execute();
$variantResult = $variantStmt->get_result();
$variants = $variantResult->fetch_all(MYSQLI_ASSOC);

$standardWeights = [
    ['value' => '250g', 'grams' => 250, 'multiplier' => 0.5],
    ['value' => '500g', 'grams' => 500, 'multiplier' => 1],
    ['value' => '1kg', 'grams' => 1000, 'multiplier' => 2],
];
$variantsByWeight = [];

foreach ($variants as $variant) {
    $key = (int) ($variant['weight_grams'] ?? 0);
    if ($key <= 0) {
        $key = strtolower(variant_display_label($variant));
    }
    $variantsByWeight[$key] = $variant;
}

foreach ($standardWeights as $weight) {
    if (!isset($variantsByWeight[$weight['grams']])) {
        $variantsByWeight[$weight['grams']] = build_default_variant(
            $product,
            $weight['value'],
            $weight['grams'],
            $weight['multiplier']
        );
    }
}

ksort($variantsByWeight, SORT_NUMERIC);
$variants = array_values($variantsByWeight);

$galleryImages = [];
foreach (['main_image', 'gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $imageField) {
    if (!empty($product[$imageField])) {
        $galleryImages[] = product_image_path($product[$imageField]);
    }
}

if (empty($galleryImages)) {
    $galleryImages[] = product_image_path(null);
}

$firstVariant = $variants[0] ?? null;
$currentOriginalPrice = $firstVariant ? $firstVariant['variant_price'] : $product['original_price'];
$currentDiscountPrice = $firstVariant && $firstVariant['variant_discount_price'] !== null
    ? $firstVariant['variant_discount_price']
    : $product['discount_price'];
$currentDiscountPrice = $currentDiscountPrice ?: $currentOriginalPrice;
$hasDiscount = (float) $currentOriginalPrice > (float) $currentDiscountPrice;
$description = trim((string) ($product['description'] ?? ''));
$shortDescription = trim((string) ($product['short_description'] ?? ''));
?>

<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo e($product['name']); ?> | Nutri Afghan</title>

    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="<?php echo e($shortDescription ?: $description); ?>" />

    <?php
    include_once('includes/header-link.php');
    ?>
</head>

<body class="preload-wrapper popup-loader">
    <!-- Scroll Top -->
    <?php
    include_once('includes/scroll-top.php');
    ?>
    <!-- /Scroll Top -->

    <!-- preload -->
    <?php
    include_once('includes/preloader.php');
    ?>
    <!-- /preload -->

    <div id="wrapper">
        <!-- Header -->
        <?php
        include_once('includes/header.php');
        ?>
        <!-- /Header -->

        <!-- breadcrumb -->
        <div class="tf-breadcrumb">
            <div class="container">
                <div class="tf-breadcrumb-wrap">
                    <div class="tf-breadcrumb-list">
                        <a href="./" class="text text-caption-1">Homepage</a>
                        <i class="icon icon-arrRight"></i>
                        <a href="shop.php<?php echo !empty($product['category_slug']) ? '?category=' . urlencode($product['category_slug']) : ''; ?>" class="text text-caption-1"><?php echo e($product['category_name'] ?: 'Products'); ?></a>
                        <i class="icon icon-arrRight"></i>
                        <span class="text text-caption-1"><?php echo e($product['name']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- Product_Main -->
        <section class="flat-spacing">
            <div class="tf-main-product section-image-zoom">
                <div class="container">
                    <div class="row">
                        <!-- Product default -->
                        <div class="col-md-5">
                            <div class="tf-product-media-wrap sticky-top">
                                <div class="thumbs-slider">
                                    <div dir="ltr" class="swiper tf-product-media-thumbs other-image-zoom"
                                        data-direction="vertical">
                                        <div class="swiper-wrapper stagger-wrap">
                                            <?php foreach ($galleryImages as $imagePath): ?>
                                                <div class="swiper-slide stagger-item" data-color="product">
                                                    <div class="item">
                                                        <img class="lazyload" data-src="<?php echo e($imagePath); ?>"
                                                            src="<?php echo e($imagePath); ?>" alt="<?php echo e($product['name']); ?>">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div dir="ltr" class="swiper tf-product-media-main" id="gallery-swiper-started">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($galleryImages as $imagePath): ?>
                                                <div class="swiper-slide" data-color="product">
                                                    <a href="<?php echo e($imagePath); ?>" target="_blank"
                                                        class="item" data-pswp-width="600px" data-pswp-height="800px">
                                                        <img class="tf-image-zoom lazyload"
                                                            data-zoom="<?php echo e($imagePath); ?>"
                                                            data-src="<?php echo e($imagePath); ?>"
                                                            src="<?php echo e($imagePath); ?>" alt="<?php echo e($product['name']); ?>">
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Product default -->
                        <!-- tf-product-info-list -->
                        <div class="col-md-7">
                            <div class="tf-product-info-wrap position-relative">
                                <div class="tf-zoom-main"></div>
                                <div class="tf-product-info-list other-image-zoom ms-0">
                                    <div class="tf-product-info-heading">
                                        <div class="tf-product-info-name">
                                            <div class="text text-btn-uppercase"><?php echo e($product['category_name'] ?: 'Product'); ?></div>
                                            <h3 class="name"><?php echo e($product['name']); ?></h3>
                                        </div>
                                        <div class="tf-product-info-desc">
                                            <div class="tf-product-info-price">
                                                <h5 class="price-on-sale font-2 text-danger" id="product-price"><?php echo format_price($currentDiscountPrice); ?></h5>
                                                <?php if ($hasDiscount): ?>
                                                    <div class="compare-at-price font-2" id="product-compare-price"><?php echo format_price($currentOriginalPrice); ?></div>
                                                <?php endif; ?>
                                                <?php if ((int) $product['discount_percentage'] > 0): ?>
                                                    <div class="badges-on-sale text-btn-uppercase">
                                                        -<?php echo (int) $product['discount_percentage']; ?>%
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($shortDescription !== ''): ?>
                                                <p><?php echo e($shortDescription); ?></p>
                                            <?php else: ?>
                                                <p>Taxes included. Shipping calculated at checkout.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="tf-product-info-choose-option">
                                        <?php if (!empty($variants)): ?>
                                            <div class="variant-picker-item">
                                                <div class="d-flex justify-content-between mb_12">
                                                    <div class="variant-picker-label">
                                                        Weight:<span class="text-title variant-picker-label-value" id="selected-variant-label"><?php echo e(variant_display_label($firstVariant)); ?></span>
                                                    </div>
                                                </div>
                                                <div class="variant-picker-values gap12">
                                                    <?php foreach ($variants as $index => $variant): ?>
                                                        <?php
                                                            $variantId = 'variant-' . (int) $variant['id'] . '-' . (int) $variant['weight_grams'];
                                                            $variantLabel = variant_display_label($variant);
                                                            $variantOriginal = $variant['variant_price'];
                                                            $variantDiscount = $variant['variant_discount_price'] ?: $variantOriginal;
                                                        ?>
                                                        <input type="radio" name="product_variant" id="<?php echo e($variantId); ?>" value="<?php echo (int) $variant['id']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                                        <label class="style-text size-btn" for="<?php echo e($variantId); ?>"
                                                            data-value="<?php echo e($variantLabel); ?>"
                                                            data-price="<?php echo e($variantDiscount); ?>"
                                                            data-original-price="<?php echo e($variantOriginal); ?>"
                                                            data-weight-grams="<?php echo (int) $variant['weight_grams']; ?>">
                                                            <span class="text-title"><?php echo e($variantLabel); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="tf-product-info-quantity">
                                            <div class="title mb_12">Quantity:</div>
                                            <div class="wg-quantity">
                                                <span class="btn-quantity btn-decrease">-</span>
                                                <input class="quantity-product" type="text" name="number" value="1">
                                                <span class="btn-quantity btn-increase">+</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="tf-product-info-by-btn mb_10">
                                                <button 
                                                    onclick="checkLoginAndAddToCart(event)" 
                                                    class="btn-style-2 flex-grow-1 text-btn-uppercase fw-6 show-shopping-cart"
                                                >
                                                    <span>Add to cart -&nbsp;</span><span class="tf-qty-price total-price" id="product-total-price"><?php echo format_price($currentDiscountPrice); ?></span>
                                                </button>
                                                <a
                                                    href="javascript:void(0);"
                                                    class="box-icon hover-tooltip text-caption-2 wishlist btn-icon-action">
                                                    <span class="icon icon-heart"></span>
                                                    <span class="tooltip text-caption-2">Wishlist</span>
                                                </a>
                                            </div>
                                            <button 
                                                onclick="checkLoginAndBuyNow(event)" 
                                                class="btn-style-3 text-btn-uppercase"
                                            >
                                                Buy it now
                                            </button>
                                        </div>
                                        <div class="tf-product-info-help">
                                            <div class="tf-product-info-extra-link">
                                                <a href="#" data-bs-toggle="modal"
                                                    class="tf-product-extra-icon">
                                                    <div class="icon">
                                                        <i class="icon-share"></i>
                                                    </div>
                                                    <p class="text-caption-1">Share</p>
                                                </a>
                                                <a href="#" data-bs-toggle="modal"
                                                    class="tf-product-extra-icon">
                                                    <div class="icon">
                                                        <i class="icon-shipping"></i>
                                                    </div>
                                                    <p class="text-caption-1">Delivery & Return</p>
                                                </a>
                                                <a href="#" data-bs-toggle="modal"
                                                    class="tf-product-extra-icon">
                                                    <div class="icon">
                                                        <i class="icon-question"></i>
                                                    </div>
                                                    <p class="text-caption-1">Ask A Question</p>
                                                </a>
                                            </div>
                                            <div class="tf-product-info-time">
                                                <div class="icon">
                                                    <i class="icon-timer"></i>
                                                </div>
                                                <p class="text-caption-1">Estimated Delivery:&nbsp;&nbsp;<span>05-07
                                                        days</span> (Domestic), <span>12-15 days</span> (International)</p>
                                            </div>
                                            <div class="tf-product-info-return">
                                                <div class="icon">
                                                    <i class="icon-arrowClockwise"></i>
                                                </div>
                                                <p class="text-caption-1">Return within <span>05 days</span> of
                                                    purchase. Duties & taxes are non-refundable.</p>
                                            </div>
                                            <div class="dropdown dropdown-store-location">
                                                <div class="dropdown-title dropdown-backdrop" data-bs-toggle="dropdown"
                                                    aria-haspopup="true">
                                                    <div class="tf-product-info-view link">
                                                        <div class="icon">
                                                            <i class="icon-map-pin"></i>
                                                        </div>
                                                        <span>View Store Information</span>
                                                    </div>
                                                </div>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <div class="dropdown-content">
                                                        <div class="dropdown-content-heading">
                                                            <h5>Store Location</h5>
                                                            <i class="icon icon-close"></i>
                                                        </div>
                                                        <div class="line-bt"></div>
                                                        <div>
                                                            <h6>Nutri Afghan</h6>
                                                            <p>Pickup available. Usually ready in 24 hours</p>
                                                        </div>
                                                        <div>
                                                            <p>Fresh dry fruits, nuts, spices, and Afghan specialties.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tf-product-info-guranteed">
                                            <div class="text-title">
                                                Guranteed safe checkout:
                                            </div>
                                            <div class="tf-payment">
                                                <a href="#">
                                                    <img src="images/payment/img-1.png" alt="">
                                                </a>
                                                <a href="#">
                                                    <img src="images/payment/img-2.png" alt="">
                                                </a>
                                                <a href="#">
                                                    <img src="images/payment/img-3.png" alt="">
                                                </a>
                                                <a href="#">
                                                    <img src="images/payment/img-4.png" alt="">
                                                </a>
                                                <a href="#">
                                                    <img src="images/payment/img-5.png" alt="">
                                                </a>
                                                <a href="#">
                                                    <img src="images/payment/img-6.png" alt="">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <ul class="accordion-product-wrap" id="accordion-product">
                                    <li class="accordion-product-item">
                                        <a href="#accordion-1" class="accordion-title collapsed current" data-bs-toggle="collapse" aria-expanded="false" aria-controls="accordion-1">
                                            <h6>Description</h6>
                                            <span class="btn-open-sub"></span>
                                        </a>
                                        <div id="accordion-1" class="collapse" data-bs-parent="#accordion-product">
                                            <div class="accordion-content tab-description">
                                                <div class="right">
                                                    <div class="letter-1 text-btn-uppercase mb_12"><?php echo e($product['name']); ?></div>
                                                    <?php if ($description !== ''): ?>
                                                        <?php foreach (preg_split('/\r\n|\r|\n/', $description) as $paragraph): ?>
                                                            <?php if (trim($paragraph) !== ''): ?>
                                                                <p class="mb_12 text-secondary"><?php echo e($paragraph); ?></p>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <p class="mb_12 text-secondary">Product details will be updated soon.</p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="left">
                                                    <div class="letter-1 text-btn-uppercase mb_12">Product Information</div>
                                                    <ul class="list-text type-disc mb_12 gap-6">
                                                        <?php if (!empty($product['sku'])): ?>
                                                            <li>SKU: <?php echo e($product['sku']); ?></li>
                                                        <?php endif; ?>
                                                        <li>Category: <?php echo e($product['category_name'] ?: 'Product'); ?></li>
                                                        <li>Stock: <?php echo (int) $product['stock_quantity']; ?> available</li>
                                                        <?php if (!empty($variants)): ?>
                                                            <li>Available weights: <?php echo e(implode(', ', array_map('variant_display_label', $variants))); ?></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <div class="text-caption-2">Store in a cool, dry place after opening.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="accordion-product-item">
                                        <a href="#accordion-4" class="accordion-title collapsed current" data-bs-toggle="collapse" aria-expanded="false" aria-controls="accordion-4">
                                            <h6>Return Policies</h6>
                                            <span class="btn-open-sub"></span>
                                        </a>
                                        <div id="accordion-4" class="collapse" data-bs-parent="#accordion-product">
                                            <div class="accordion-content tab-policies">
                                                <div class="text-btn-uppercase mb_12">Return Policies</div>
                                                <p class="mb_12 text-secondary">At Nutri Afghan, we take care to deliver fresh products in good condition. If your order arrives damaged, incorrect, or spoiled, please contact us within 24-48 hours with photos or videos.</p>
                                                <div class="text-btn-uppercase mb_12">Easy Exchanges or Refunds</div>
                                                <ul class="list-text type-disc mb_12 gap-6">
                                                    <li class="text-secondary">Eligible orders can be replaced or refunded after verification.</li>
                                                    <li class="text-secondary">Items should remain in their original packaging where possible.</li>
                                                </ul>
                                                <div class="text-btn-uppercase mb_12">Simple Process</div>
                                                <ul class="list-text type-number mb_12 gap-6">
                                                    <li class="text-secondary">Contact our team with your order details.</li>
                                                    <li class="text-secondary">Share clear photos or videos of the issue.</li>
                                                    <li class="text-secondary">Wait for verification and replacement or refund confirmation.</li>
                                                </ul>
                                                <p class="text-secondary">For questions about your product or order, contact our customer support team.</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- /tf-product-info-list -->
                    </div>
                </div>
            </div>
        </section>
        <!-- /Product_Main -->

        <!-- Footer -->
        <?php
        include_once('includes/footer.php');
        ?>
        <!-- /Footer -->

        <!-- toolbar-bottom -->
        <?php
        include_once('includes/bottom-toolbar.php');
        ?>
        <!-- /toolbar-bottom -->
    </div>

    <!-- auto popup  -->
    <?php
    include_once('includes/auto-popup.php');
    ?>
    <!-- /auto popup  -->

    <!-- search -->
    <?php
    include_once('includes/search-bar.php');
    ?>
    <!-- /search -->

    <!-- mobile menu -->
    <?php
    include_once('includes/mobile-menu.php');
    ?>
    <!-- /mobile menu -->

    <!-- quickView -->
    <?php
    include_once('includes/quick-view.php');
    ?>
    <!-- quickView -->

    <!-- Shopping Cart -->
    <?php
    include_once('includes/shopping-cart.php')
    ?>
    <!-- /Shopping Cart -->

    <!-- wishlist -->
    <?php
    include_once('includes/wishlist-bar.php');
    ?>
    <!-- /wishlist -->

    <?php
    include_once('includes/footer-link.php');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const galleryThumbs = document.querySelector('.tf-main-product .tf-product-media-thumbs');
            const galleryMain = document.querySelector('.tf-main-product .tf-product-media-main');

            if (galleryThumbs && galleryMain && typeof Swiper !== 'undefined') {
                const thumbsSwiper = new Swiper(galleryThumbs, {
                    spaceBetween: 10,
                    slidesPerView: 'auto',
                    freeMode: true,
                    watchSlidesProgress: true,
                    observer: true,
                    observeParents: true,
                    direction: 'horizontal',
                    breakpoints: {
                        1200: {
                            direction: galleryThumbs.dataset.direction || 'vertical',
                        },
                    },
                });

                new Swiper(galleryMain, {
                    spaceBetween: 0,
                    observer: true,
                    observeParents: true,
                    thumbs: {
                        swiper: thumbsSwiper,
                    },
                });
            }

            const productOptions = document.querySelector('.tf-product-info-choose-option');
            const variantLabels = productOptions ? productOptions.querySelectorAll('.variant-picker-values .size-btn') : [];
            const selectedVariantLabel = document.getElementById('selected-variant-label');
            const productPrice = document.getElementById('product-price');
            const comparePrice = document.getElementById('product-compare-price');
            const totalPrice = document.getElementById('product-total-price');
            const quantityInput = productOptions ? productOptions.querySelector('.quantity-product') : null;

            function formatPrice(value) {
                return 'Rs. ' + Number(value || 0).toFixed(2);
            }

            function currentPrice() {
                const activeVariant = productOptions ? productOptions.querySelector('.variant-picker-values input:checked + label') : null;
                return activeVariant ? Number(activeVariant.dataset.price || 0) : <?php echo json_encode((float) $currentDiscountPrice); ?>;
            }

            function updateTotal() {
                const quantity = Math.max(1, parseInt(quantityInput?.value || '1', 10));
                if (quantityInput) {
                    quantityInput.value = quantity;
                }
                if (totalPrice) {
                    totalPrice.textContent = formatPrice(currentPrice() * quantity);
                }
            }

            variantLabels.forEach(function (label) {
                label.addEventListener('click', function () {
                    const price = Number(label.dataset.price || 0);
                    const originalPrice = Number(label.dataset.originalPrice || price);

                    if (selectedVariantLabel) {
                        selectedVariantLabel.textContent = label.dataset.value || '';
                    }
                    if (productPrice) {
                        productPrice.textContent = formatPrice(price);
                    }
                    if (comparePrice) {
                        comparePrice.textContent = originalPrice > price ? formatPrice(originalPrice) : '';
                    }

                    setTimeout(updateTotal, 0);
                });
            });

            productOptions?.querySelectorAll('.btn-quantity').forEach(function (button) {
                button.addEventListener('click', function () {
                    setTimeout(updateTotal, 0);
                });
            });

            quantityInput?.addEventListener('input', updateTotal);
            updateTotal();
        });

        // Customer login check functions
        function checkLoginAndAddToCart(event) {
            event.preventDefault();
            const isLoggedIn = <?php echo json_encode(is_customer_logged_in()); ?>;
            
            if (!isLoggedIn) {
                if (confirm('You need to be logged in to add items to your cart. Would you like to login now?')) {
                    window.location.href = 'customer-login.php?redirect=product-detail.php?slug=<?php echo isset($_GET['slug']) ? urlencode($_GET['slug']) : ''; ?>';
                }
                return;
            }

            // Get selected variant
            const selectedVariant = document.querySelector('input[name="product_variant"]:checked');
            const variantId = selectedVariant ? selectedVariant.value : null;

            // Get quantity
            const quantityInput = document.querySelector('.quantity-product');
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

            // Show loading
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<span>Adding...</span>';
            button.disabled = true;

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', '<?php echo $product['id']; ?>');
            if (variantId) {
                formData.append('variant_id', variantId);
            }
            formData.append('quantity', quantity);

            // Send request
            fetch('cart-process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Product added to cart successfully!');
                    if (data.cart_count !== undefined) {
                        const cartCountBox = document.getElementById('cart-count-box') || document.querySelector('.nav-cart .count-box');
                        if (cartCountBox) {
                            cartCountBox.textContent = data.cart_count;
                        }
                    }
                    window.location.href = 'cart.php';
                } else {
                    alert('Error: ' + (data.message || 'Failed to add product to cart'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to cart');
            })
            .finally(() => {
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function checkLoginAndBuyNow(event) {
            event.preventDefault();
            const isLoggedIn = <?php echo json_encode(is_customer_logged_in()); ?>;
            
            if (!isLoggedIn) {
                if (confirm('You need to be logged in to make a purchase. Would you like to login now?')) {
                    window.location.href = 'customer-login.php?redirect=checkout.php';
                }
            } else {
                // Redirect to checkout page
                window.location.href = 'checkout.php';
            }
        }
    </script>
</body>
</html>
