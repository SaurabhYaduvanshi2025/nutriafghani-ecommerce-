<?php
require_once('config/db.php');
require_once('includes/blog-functions.php');

blog_ensure_table($conn);

$blogs = [];
$result = $conn->query('SELECT id, title, slug, image, content, created_at FROM blogs ORDER BY created_at DESC, id DESC');
if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Blog - Nutri Afghan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Read the latest Nutri Afghan stories, guides, and updates.">
    <?php include_once('includes/header-link.php'); ?>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <link rel="stylesheet" href="css/blog.css">
</head>
<body class="preload-wrapper blog-page">
    <?php include_once('includes/scroll-top.php'); ?>
    <?php include_once('includes/preloader.php'); ?>

    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>

        <main>
            <section class="blog-hero">
                <div class="blog-shell">
                    <h1>Nutri Afghan Blog</h1>
                    <p>Fresh notes, product stories, and practical guides from the world of premium dry fruits, saffron, nuts, and natural foods.</p>
                </div>
            </section>

            <section class="blog-shell blog-grid">
                <?php if (!$blogs): ?>
                    <div class="blog-empty">No blog posts have been published yet.</div>
                <?php endif; ?>

                <?php foreach ($blogs as $blog): ?>
                    <a class="blog-card" href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>">
                        <img src="<?php echo blog_e(blog_image_url($blog['image'])); ?>" alt="<?php echo blog_e($blog['title']); ?>">
                        <div class="blog-card-body">
                            <span class="blog-date"><?php echo blog_e(date('M d, Y', strtotime($blog['created_at']))); ?></span>
                            <h2><?php echo blog_e($blog['title']); ?></h2>
                            <p><?php echo blog_e(blog_excerpt($blog['content'])); ?></p>
                            <span class="blog-read">Read article</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        </main>

        <?php include_once('includes/footer.php'); ?>
    </div>

    <?php include_once('includes/footer-link.php'); ?>
</body>
</html>
