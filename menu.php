<?php
require_once __DIR__ . '/includes/config.php';

$active_nav = 'menu';
$category = $_GET['category'] ?? 'all';
$query = trim($_GET['q'] ?? '');
$flash = get_flash();

if ($query !== '') {
    $items = search_products($query);
    $heading = 'Search results';
} else {
    $items = products_by_category($category);
    $heading = ($category === 'all' || $category === '') ? 'Our Menu' : strtoupper($category);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="cravings inner-page">
        <div class="cravings-header">
            <h2>What Are You Craving?</h2>
            <div class="heart-divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <div class="cravings-grid">
            <a href="menu.php?category=cakes" class="craving-item <?php echo $category === 'cakes' ? 'active' : ''; ?>">
                <div class="craving-circle">
                    <img src="images/cake.jfif" alt="Cakes">
                </div>
                <h3>CAKES</h3>
            </a>
            <a href="menu.php?category=breads" class="craving-item <?php echo $category === 'breads' ? 'active' : ''; ?>">
                <div class="craving-circle">
                    <img src="images/bread.jfif" alt="Breads">
                </div>
                <h3>BREADS</h3>
            </a>
            <a href="menu.php?category=cookies" class="craving-item <?php echo $category === 'cookies' ? 'active' : ''; ?>">
                <div class="craving-circle">
                    <img src="images/cookies.jfif" alt="Cookies">
                </div>
                <h3>COOKIES</h3>
            </a>
            <a href="menu.php?category=cupcakes" class="craving-item <?php echo $category === 'cupcakes' ? 'active' : ''; ?>">
                <div class="craving-circle">
                    <img src="images/cupcake.jfif" alt="Cupcakes">
                </div>
                <h3>CUPCAKES</h3>
            </a>
        </div>
        <?php if ($flash): ?>
            <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
        <?php endif; ?>
        <?php if ($query !== ''): ?>
            <p class="about-text">Showing results for "<?php echo e($query); ?>"</p>
            <a href="menu.php" class="view-all">VIEW FULL MENU &rarr;</a>
        <?php elseif ($category !== 'all' && $category !== ''): ?>
            <div class="section-title-row">
                <h2><?php echo e($heading); ?></h2>
                <a href="menu.php" class="view-all">VIEW ALL &rarr;</a>
            </div>
        <?php endif; ?>
        <?php if (!$items): ?>
            <p class="about-text">No pastries found. Try another search or category.</p>
            <a href="menu.php" class="cta-button">VIEW FULL MENU</a>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($items as $product) {
                    echo render_product_card($product);
                } ?>
            </div>
        <?php endif; ?>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
