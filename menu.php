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

global $categories;
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
    <section class="best-sellers inner-page">
        <div class="cravings-header">
            <h2><?php echo e($heading); ?></h2>
            <div class="heart-divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <?php if ($flash): ?>
            <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
        <?php endif; ?>
        <?php if ($query !== ''): ?>
            <p class="about-text">Showing results for "<?php echo e($query); ?>"</p>
        <?php endif; ?>
        <div class="step-options filter-pills">
            <a href="menu.php" class="pill-btn <?php echo ($category === 'all' && $query === '') ? 'active' : ''; ?>">All</a>
            <?php foreach ($categories as $key => $label): ?>
                <a href="menu.php?category=<?php echo e($key); ?>" class="pill-btn <?php echo $category === $key ? 'active' : ''; ?>"><?php echo e($label); ?></a>
            <?php endforeach; ?>
        </div>
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
