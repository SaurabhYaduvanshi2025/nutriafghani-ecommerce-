<?php
require_once('config/db.php');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

$categories = [];
$categoriesQuery = "
    SELECT c.id, c.name, c.slug, c.image, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id, c.name, c.slug, c.image
    ORDER BY c.name ASC
";

if ($result = $conn->query($categoriesQuery)) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Nutri Afghan</title>

  <meta name="author" content=""/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <meta name="description" content=""/>

  <?php include_once('includes/header-link.php'); ?>
</head>

<body class="preload-wrapper popup-loader">
  <?php include_once('includes/scroll-top.php'); ?>
  <?php include_once('includes/preloader.php'); ?>

  <div id="wrapper">
    <?php include_once('includes/header.php'); ?>

    <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
      <div class="container-full">
        <div class="row">
          <div class="col-12">
            <h3 class="heading text-center">Our Collection</h3>
            <ul class="breadcrumbs d-flex align-items-center justify-content-center">
              <li><a class="link" href="./">Home</a></li>
              <li><i class="icon-arrRight"></i></li>
              <li>Our Collection</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <section class="flat-spacing">
      <div class="container">
        <?php if (empty($categories)): ?>
          <div class="text-center" style="padding: 50px 20px; background: #f8f8f8; border-radius: 8px;">
            <h5>No collections found</h5>
            <p class="text-secondary">Active categories added from the admin panel will appear here.</p>
          </div>
        <?php else: ?>
          <div class="tf-grid-layout tf-col-2 lg-col-4">
            <?php foreach ($categories as $category): ?>
              <?php
                $shopUrl = 'shop.php?category=' . urlencode($category['slug']);
                $imagePath = category_image_path($category['image']);
                $productCount = (int) $category['product_count'];
              ?>
              <div class="collection-position-2 radius-lg style-3 hover-img">
                <a href="<?php echo e($shopUrl); ?>" class="img-style">
                  <img
                    class="lazyload"
                    data-src="<?php echo e($imagePath); ?>"
                    src="<?php echo e($imagePath); ?>"
                    alt="<?php echo e($category['name']); ?>"
                  />
                </a>
                <div class="content">
                  <a href="<?php echo e($shopUrl); ?>" class="cls-btn">
                    <h6 class="text"><?php echo e($category['name']); ?></h6>
                    <span class="count-item text-secondary">
                      <?php echo $productCount; ?> <?php echo $productCount === 1 ? 'item' : 'items'; ?>
                    </span>
                    <i class="icon icon-arrowUpRight"></i>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php include_once('includes/footer.php'); ?>
    <?php include_once('includes/bottom-toolbar.php'); ?>
  </div>

  <?php include_once('includes/auto-popup.php'); ?>
  <?php include_once('includes/search-bar.php'); ?>
  <?php include_once('includes/mobile-menu.php'); ?>
  <?php include_once('includes/quick-view.php'); ?>
  <?php include_once('includes/shopping-cart.php'); ?>
  <?php include_once('includes/wishlist-bar.php'); ?>
  <?php include_once('includes/footer-link.php'); ?>
</body>
</html>
