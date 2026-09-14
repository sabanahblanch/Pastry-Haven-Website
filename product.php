<?php
require_once __DIR__ . '/includes/config.php';

$id = $_GET['id'] ?? '';
$product = get_product($id);
if (!$product || $product['id'] === 'custom-cake') {
    set_flash('error', 'That pastry could not be found.');
    redirect('menu.php');
}

$active_nav = 'menu';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($product['name']); ?> | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="about-container">
            <div class="about-image-wrapper product-photo">
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>"<?php echo $product['id'] === 'classic-chocolate-cake' ? ' class="img-zoom-fill"' : ''; ?>>
            </div>
            <div class="about-content">
                <?php if ($flash): ?>
                    <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
                <?php endif; ?>
                <div class="about-header">
                    <h2><?php echo e($product['name']); ?></h2>
                    <div class="small-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="price"><?php echo format_price($product['price']); ?></p>
                <div class="reviews">
                    <span class="hearts"><?php echo rating_hearts($product['rating']); ?></span>
                    <span class="count">(<?php echo (int) $product['reviews']; ?>)</span>
                </div>
                <p class="about-text"><?php echo e($product['description']); ?></p>
                <p class="about-text"><?php echo e($product['details']); ?></p>
                <form method="post" action="cart.php" class="build-right product-buy-row">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo e($product['id']); ?>">
                    <input type="hidden" name="return" value="product.php?id=<?php echo e($product['id']); ?>">
                    <div class="step-row">
                        <span class="step-title">Quantity</span>
                        <div class="step-input">
                            <input type="number" name="qty" value="1" min="1" max="20" required>
                        </div>
                    </div>
                    <div class="menu-card-cart">
                        <button type="submit" name="action" value="add" class="cta-button">ADD TO CART</button>
                        <button type="submit" name="action" value="buy_now" class="btn secondary-btn">BUY NOW</button>
                    </div>
                </form>
                <a href="favorite.php?id=<?php echo e($product['id']); ?>" class="btn secondary-btn">
                    <?php echo is_favorite($product['id']) ? 'SAVED' : 'ADD TO FAVORITES'; ?>
                </a>
                <a href="menu.php?category=<?php echo e($product['category']); ?>" class="view-all">BACK TO MENU &rarr;</a>
            </div>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
