<?php
// Fetch featured/bestseller products from database
require_once(__DIR__ . '/../config/db.php');

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

$productsQuery = "
    SELECT p.id, p.name, p.slug, p.short_description, p.original_price, p.discount_price,
           p.main_image, p.gallery_image_1, p.gallery_image_2, p.gallery_image_3, p.stock_quantity, p.is_featured,
           c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 AND p.is_featured = 1
    ORDER BY p.created_at DESC
    LIMIT 8
";

$products = [];
if ($result = $conn->query($productsQuery)) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<section class="flat-spacing">
    <div class="container">
        <div class="heading-section text-center wow fadeInUp">
            <h3 class="heading">Our Bestseller Products</h3>
        </div>
        <div
            dir="ltr"
            data-preview="4"
            data-tablet="3"
            data-mobile="2"
            data-space-lg="30"
            data-space-md="30"
            data-space="15"
            data-pagination="1"
            data-pagination-md="1"
            data-pagination-lg="1">
            <div class="tf-grid-layout tf-col-2 lg-col-3 xl-col-4">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $index => $product): ?>
                        <!-- card product <?php echo $index + 1; ?> -->
                        <div class="card-product wow fadeInUp" data-wow-delay="<?php echo ($index % 4) * 0.1; ?>s" style="visibility: visible; animation-name: fadeInUp">
                            <div class="card-product-wrapper">
                                <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" class="product-img">
                                    <img
                                        class="lazyload img-product"
                                        data-src="<?php echo htmlspecialchars(product_image_path($product['main_image'])); ?>"
                                        src="<?php echo htmlspecialchars(product_image_path($product['main_image'])); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                    <img
                                        class="lazyload img-hover"
                                        data-src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_1'] ?: $product['main_image'])); ?>"
                                        src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_1'] ?: $product['main_image'])); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                    <?php if ($product['gallery_image_2']): ?>
                                        <img
                                            class="lazyload img-hover"
                                            data-src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_2'])); ?>"
                                            src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_2'])); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                    <?php endif; ?>
                                    <?php if ($product['gallery_image_3']): ?>
                                        <img
                                            class="lazyload img-hover"
                                            data-src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_3'])); ?>"
                                            src="<?php echo htmlspecialchars(product_image_path($product['gallery_image_3'])); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                    <?php endif; ?>
                                </a>
                                <div class="list-product-btn">
                                    <a href="#wishlist" data-bs-toggle="modal" class="box-icon wishlist btn-icon-action">
                                        <span class="icon icon-heart"></span>
                                        <span class="tooltip">Wishlist</span>
                                    </a>
                                    <a href="#quickView" data-bs-toggle="modal" class="box-icon quickview tf-btn-loading">
                                        <span class="icon icon-eye"></span>
                                        <span class="tooltip">Quick View</span>
                                    </a>
                                </div>
                                <div class="list-btn-main">
                                    <button onclick="checkLoginAndAddToCart(event)" class="btn-main-product" style="background: none; border: none; padding: 10px 30px; cursor: pointer; width: 100%;">Add To cart</button>
                                </div>
                            </div>
                            <div class="card-product-info">
                                <a href="product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" class="title link"><?php echo htmlspecialchars($product['name']); ?></a>
                                <span class="price text-danger">
                                    <?php if ($product['discount_price'] && $product['discount_price'] < $product['original_price']): ?>
                                        <span class="old-price">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                        ₹<?php echo number_format($product['discount_price'], 2); ?>
                                    <?php else: ?>
                                        ₹<?php echo number_format($product['original_price'], 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center" style="padding: 40px; color: #999;">
                        <p>No featured products available</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sec-btn text-center mt-5">
                <a href="shop.php" class="btn-line">View All Products</a>
            </div>
        </div>
    </div>
</section>