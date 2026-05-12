<?php
// Fetch categories from database
require_once(__DIR__ . '/../config/db.php');

$categoriesQuery = "
    SELECT c.id, c.name, c.image, c.slug,
           COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
";

$categories = [];
if ($result = $conn->query($categoriesQuery)) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<section class="flat-spacing-2 pb_0">
    <div class="container">
        <div class="heading-section-2 wow fadeInUp">
            <h3>Categories you might like</h3>
            <a href="collection.php" class="btn-line">View All Collection</a>
        </div>
        <div class="flat-collection-circle wow fadeInUp" data-wow-delay="0.1s">
            <div
                dir="ltr"
                class="swiper tf-sw-collection"
                data-preview="5"
                data-tablet="3"
                data-mobile="2"
                data-space-lg="20"
                data-space-md="20"
                data-space="15"
                data-pagination="1"
                data-pagination-md="1"
                data-pagination-lg="1"
            >
                <div class="swiper-wrapper">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <!-- Category Item -->
                            <div class="swiper-slide">
                                <div class="collection-circle hover-img">
                                    <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="img-style">
                                        <img
                                            class="lazyload"
                                            data-src="images/<?php echo htmlspecialchars($category['image']); ?>"
                                            src="images/<?php echo htmlspecialchars($category['image']); ?>"
                                            alt="<?php echo htmlspecialchars($category['name']); ?>"
                                        />
                                    </a>
                                    <div class="collection-content text-center">
                                        <div>
                                            <a href="shop.php?category=<?php echo urlencode($category['slug']); ?>" class="cls-title">
                                                <h6 class="text"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                <i class="icon icon-arrowUpRight"></i>
                                            </a>
                                        </div>
                                        <div class="count text-secondary"><?php echo intval($category['product_count']); ?> items</div>
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
            <div class="nav-prev-collection d-none d-lg-flex nav-sw style-line nav-sw-left">
                <i class="icon icon-arrLeft"></i>
            </div>
            <div class="nav-next-collection d-none d-lg-flex nav-sw style-line nav-sw-right">
                <i class="icon icon-arrRight"></i>
            </div>
        </div>
    </div>
</section>
