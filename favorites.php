<?php
require_once __DIR__ . '/includes/config.php';

$active_nav = '';
$flash = get_flash();
$ids = get_favorites();
$items = [];
foreach ($ids as $id) {
    $product = get_product($id);
    if ($product && $product['id'] !== 'custom-cake') {
        $items[] = $product;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="menu-browse inner-page">
        <div class="menu-browse-shell">
            <div class="cravings-header">
                <h2>My Favorites</h2>
                <div class="heart-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <?php if (!$items): ?>
                <p class="about-text">No favorites yet. Tap the heart on a pastry to save it here.</p>
                <a href="menu.php" class="cta-button">BROWSE MENU</a>
            <?php else: ?>
                <p class="about-text">Pastries you saved with the heart. Tap the heart again to remove one.</p>
                <div class="menu-grid">
                    <?php foreach ($items as $product) {
                        echo render_menu_card($product);
                    } ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
