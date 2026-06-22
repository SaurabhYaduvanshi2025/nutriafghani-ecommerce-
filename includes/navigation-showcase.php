<?php
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once(__DIR__ . '/../config/db.php');
}

$showcaseMenus = [];
$showcaseQuery = "
    SELECT m.id, m.label, m.cover_image, COUNT(p.id) AS product_count,
           (SELECT fp.main_image
            FROM products fp
            WHERE fp.menu_id = m.id AND fp.is_active = 1 AND fp.main_image IS NOT NULL
            ORDER BY fp.is_featured DESC, fp.created_at DESC
            LIMIT 1) AS fallback_image
    FROM menu_items m
    LEFT JOIN products p ON p.menu_id = m.id AND p.is_active = 1
    WHERE m.is_active = 1 AND m.parent_id IS NULL AND m.menu_type = 'homepage'
    GROUP BY m.id, m.label, m.cover_image
    ORDER BY m.sort_order ASC, m.id ASC
";

if ($showcaseResult = $conn->query($showcaseQuery)) {
    $showcaseMenus = $showcaseResult->fetch_all(MYSQLI_ASSOC);
}

function navigation_cover_path(array $menu): string
{
    $path = $menu['cover_image'] ?: $menu['fallback_image'];
    if (empty($path)) {
        return 'images/pista_demp.jpg';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    return 'uploads/' . ltrim($path, '/');
}
?>

<?php if (!empty($showcaseMenus)): ?>
<?php $desktopMenuPreview = max(1, min(4, count($showcaseMenus))); ?>
<section class="navigation-showcase flat-spacing flat-collection-circle">
    <div class="container">
        <div class="navigation-showcase-heading">
            <div>
                <span class="navigation-showcase-kicker">Explore our pantry</span>
                <h3>Shop Your Way</h3>
            </div>
        </div>

        <div class="swiper tf-sw-collection navigation-showcase-slider"
             data-preview="<?php echo $desktopMenuPreview; ?>" data-tablet="2.3" data-mobile-sm="1.55" data-mobile="1.12"
             data-space-lg="20" data-space-md="18" data-space="12"
             data-loop="false" data-auto-play="true" data-delay="3200" data-speed="850">
            <div class="swiper-wrapper">
                <?php foreach ($showcaseMenus as $menu): ?>
                    <?php
                    $count = (int) $menu['product_count'];
                    ?>
                    <div class="swiper-slide">
                        <a class="navigation-showcase-card" href="shop.php?menu=<?php echo (int) $menu['id']; ?>">
                            <img src="<?php echo htmlspecialchars(navigation_cover_path($menu), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($menu['label'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="navigation-showcase-shade"></span>
                            <span class="navigation-showcase-content">
                                <span class="navigation-showcase-count"><?php echo $count; ?> <?php echo $count === 1 ? 'product' : 'products'; ?></span>
                                <strong><?php echo htmlspecialchars($menu['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span class="navigation-showcase-link">Discover <i class="icon icon-arrowUpRight"></i></span>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="sw-pagination-collection sw-dots type-circle justify-content-center"></div>
    </div>
</section>

<style>
.navigation-showcase { overflow: hidden; background: linear-gradient(180deg, #fbf8f1 0%, #fff 100%); }
.navigation-showcase-heading { display:flex; align-items:end; justify-content:space-between; gap:24px; margin-bottom:26px; }
.navigation-showcase-heading h3 { margin:5px 0 0; font-size:clamp(32px,4vw,52px); letter-spacing:-.04em; }
.navigation-showcase-kicker { color:#967044; font-size:12px; font-weight:700; letter-spacing:.18em; text-transform:uppercase; }
.navigation-showcase-card { position:relative; display:block; width:100%; height:390px; overflow:hidden; border-radius:24px; isolation:isolate; background:#d7d0c4; }
.navigation-showcase-card img { width:100%; height:100%; object-fit:cover; transition:transform .8s cubic-bezier(.2,.7,.2,1); }
.navigation-showcase-card:hover img { transform:scale(1.07); }
.navigation-showcase-shade { position:absolute; inset:0; background:linear-gradient(180deg,rgba(12,16,10,.04) 28%,rgba(12,16,10,.88) 100%); z-index:1; }
.navigation-showcase-content { position:absolute; left:22px; right:22px; bottom:22px; z-index:2; color:#fff; }
.navigation-showcase-count { display:inline-flex; padding:7px 12px; margin-bottom:14px; border-radius:999px; background:rgba(255,255,255,.16); font-size:11px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; backdrop-filter:blur(8px); }
.navigation-showcase-content strong { display:block; max-width:95%; color:#fff; font-size:clamp(25px,2.3vw,34px); line-height:1.05; letter-spacing:-.035em; }
.navigation-showcase-link { display:flex; align-items:center; gap:8px; margin-top:18px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; }
.navigation-showcase .sw-pagination-collection { margin-top:28px; }
@media (max-width:767px) {
  .navigation-showcase-heading { align-items:center; margin-bottom:22px; }
  .navigation-showcase-card { height:360px; border-radius:20px; }
  .navigation-showcase-content { left:22px; right:22px; bottom:22px; }
}
</style>
<?php endif; ?>
