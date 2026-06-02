<?php
// Fetch categories from database
require_once(__DIR__ . '/../config/db.php');

$categoriesQuery = "
    SELECT c.id, c.name, c.image, c.slug,
           COUNT(DISTINCT p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON (p.category_id = c.id OR p.category_slug = c.slug) AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
";

$categories = [];
if ($result = $conn->query($categoriesQuery)) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

function category_image_path($path)
{
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return 'admin/' . ltrim($path, '/');
}
?>
<section class="flat-spacing-2 pb_0">
    <div class="container">
        <div class="heading-section text-center wow fadeInUp">
            <h3 class="heading">Categories you might like</h3>
            <a href="collection.php" class="btn-line">View All Collection</a>
        </div>
        <div class="flat-collection-circle wow fadeInUp" data-wow-delay="0.1s">
            <div
                dir="ltr"
                class="swiper tf-sw-collection"
                data-preview="4"
                data-tablet="3"
                data-mobile="2"
                data-space-lg="20"
                data-space-md="20"
                data-space="15"
                data-pagination="1"
                data-pagination-md="1"
                data-pagination-lg="1"
                data-loop="true"
                data-auto-play="true"
                data-delay="2500"
            >
                <div class="swiper-wrapper">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <!-- Category Item -->
                            <div class="swiper-slide">
                                <div class="card-product category-product-card">
                                    <div class="card-product-wrapper">
                                        <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="product-img">
                                            <img
                                                class="lazyload img-product"
                                                data-src="<?php echo htmlspecialchars(category_image_path($category['image'])); ?>"
                                                src="<?php echo htmlspecialchars(category_image_path($category['image'])); ?>"
                                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                            />
                                            <img
                                                class="lazyload img-hover"
                                                data-src="<?php echo htmlspecialchars(category_image_path($category['image'])); ?>"
                                                src="<?php echo htmlspecialchars(category_image_path($category['image'])); ?>"
                                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                            />
                                        </a>
                                        <div class="list-btn-main">
                                            <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="btn-main-product">View Products</a>
                                        </div>
                                    </div>
                                    <div class="card-product-info text-center">
                                        <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="title link"><?php echo htmlspecialchars($category['name']); ?></a>
                                        <span class="price text-danger"><?php echo intval($category['product_count']); ?> items</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <div class="text-center" style="padding: 40px; color: #999;">
                                <p>No categories available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex d-lg-none sw-pagination-collection sw-dots type-circle justify-content-center"></div>
            </div>
            <div class="nav-prev-collection d-flex nav-sw style-line nav-sw-left">
                <i class="icon icon-arrLeft"></i>
            </div>
            <div class="nav-next-collection d-flex nav-sw style-line nav-sw-right">
                <i class="icon icon-arrRight"></i>
            </div>
        </div>
    </div>
</section>
