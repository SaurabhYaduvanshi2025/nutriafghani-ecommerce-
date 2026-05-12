<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Nutri Afghan</title>

    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />

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

        <!-- page-title -->
        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container-full">
                <div class="row">
                    <div class="col-12">
                        <h3 class="heading text-center">About Us</h3>
                        <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                            <li>
                                <a class="link" href="./">Home</a>
                            </li>
                            <li>
                                <i class="icon-arrRight"></i>
                            </li>
                            <li>
                                About Us
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page-title -->

        <!-- about-us -->
        <section class="flat-spacing about-us-main">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="about-us-features wow fadeInLeft">
                            <img class="lazyload" data-src="images/store.jpg" src="images/store.jpg" alt="image-team">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-us-content">
                            <h3 class="title wow fadeInUp">Nutri Afghan – Offering rare and beautiful items worldwide</h3>
                            <div class="widget-tabs style-3">
                                <ul class="widget-menu-tab wow fadeInUp">
                                    <li class="item-title active">
                                        <span class="inner text-button">Introduction</span>
                                    </li>
                                    <li class="item-title">
                                        <span class="inner text-button">Our Vision</span>
                                    </li>
                                    <li class="item-title">
                                        <span class="inner text-button">Our Values</span>
                                    </li>
                                    <li class="item-title">
                                        <span class="inner text-button">Our Quality</span>
                                    </li>
                                </ul>
                                <div class="widget-content-tab wow fadeInUp">
                                    <div class="widget-content-inner active">
                                        <p>Nutri Afghan was created with a simple mission — to bring the world-famous taste and purity of authentic Afghan dry fruits directly to your home. Sourced from the fertile valleys of Afghanistan, our products are handpicked, naturally sun-dried, and packed with nutrition that supports a healthier lifestyle.</p>
                                        <p></p>
                                    </div>
                                    <div class="widget-content-inner">
                                        <p>Our vision is to make premium Afghan dry fruits accessible to every household by delivering products that are pure, healthy, and naturally rich in nutrition. We aim to become India's most trusted brand for authentic Afghan dry fruits by maintaining the highest standards of quality, transparency, and customer satisfaction.</p>
                                    </div>
                                    <div class="widget-content-inner">
                                        <p>At Nutri Afghan, our values guide every step — from sourcing to delivery. We believe in purity, honesty, and freshness. We are committed to ethical sourcing, long-term farmer partnerships, sustainable practices, and providing customers with dry fruits they can trust without compromise.</p>
                                    </div>
                                    <div class="widget-content-inner">
                                        <p>At Nutri Afghan, quality is our promise. Every product we offer is handpicked from trusted Afghan farms known for their rich soil and natural cultivation methods. We ensure that each nut and dry fruit is carefully sorted, naturally sun-dried, and packed under strict hygiene standards.</p>
                                    </div>
                                </div>
                            </div>
                            <a href="contact.php" class="tf-btn btn-fill wow fadeInUp"><span class="text text-button">Contact Us</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /about-us -->

        <!-- Iconbox -->
        <?php
        include_once('includes/iconbox.php');
        ?>
        <!-- /Iconbox -->

        <!-- Testimonial -->
        <?php
        include_once('includes/testimonial.php');
        ?>
        <!-- /Testimonial -->

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
</body>
</html>