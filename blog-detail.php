<?php
require_once('config/db.php');
require_once('includes/blog-functions.php');

blog_ensure_table($conn);

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: blog.php');
    exit;
}

$stmt = $conn->prepare('SELECT id, title, slug, image, meta_title, meta_description, meta_keywords, content, created_at FROM blogs WHERE slug = ? LIMIT 1');
$stmt->bind_param('s', $slug);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$blog) {
    http_response_code(404);
}

$pageTitle = 'Blog Not Found - Nutri Afghan';
$pageDescription = 'Blog post not found.';
$pageKeywords = '';

if ($blog) {
    $pageTitle = trim($blog['meta_title'] ?? '') !== '' ? $blog['meta_title'] : $blog['title'] . ' - Nutri Afghan';
    $pageDescription = trim($blog['meta_description'] ?? '') !== '' ? $blog['meta_description'] : blog_excerpt($blog['content'], 150);
    $pageKeywords = trim($blog['meta_keywords'] ?? '');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo blog_e($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?php echo blog_e($pageDescription); ?>">
    <?php if ($pageKeywords !== ''): ?>
        <meta name="keywords" content="<?php echo blog_e($pageKeywords); ?>">
    <?php endif; ?>
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
            <?php if (!$blog): ?>
                <section class="blog-hero">
                    <div class="blog-shell">
                        <h1>Blog not found</h1>
                        <p>The article you are looking for may have been moved or deleted.</p>
                    </div>
                </section>
                <section class="blog-shell blog-article">
                    <a class="blog-back" href="blog.php">Back to blog</a>
                </section>
            <?php else: ?>
                <section class="blog-detail-hero">
                    <div class="blog-shell">
                        <a class="blog-back" href="blog.php">Back to blog</a>
                        <h1 class="blog-detail-title"><?php echo blog_e($blog['title']); ?></h1>
                        <p class="blog-detail-meta"><?php echo blog_e(date('M d, Y', strtotime($blog['created_at']))); ?></p>
                        <img class="blog-featured-image" src="<?php echo blog_e(blog_image_url($blog['image'])); ?>" alt="<?php echo blog_e($blog['title']); ?>">
                    </div>
                </section>

                <article class="blog-shell blog-article">
                    <div class="blog-content ql-editor">
                        <?php echo $blog['content']; ?>
                    </div>
                </article>
            <?php endif; ?>
        </main>

        <?php include_once('includes/footer.php'); ?>
    </div>

    <?php include_once('includes/footer-link.php'); ?>
</body>
</html>
