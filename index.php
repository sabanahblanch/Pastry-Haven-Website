<?php
require_once __DIR__ . '/includes/config.php';

$active_nav = 'home';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <?php if ($flash): ?>
        <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?> home-flash"><?php echo e($flash['message']); ?></p>
    <?php endif; ?>

    <section class="hero" id="home">
        <div class="hero-content">
            <p class="small-title">FRESHLY BAKED, EVERYDAY</p>
            <h1>Made With Love,<br>Baked For You.</h1>
            <p class="hero-description">Fresh pastries made with care, from our kitchen to your table.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn primary-btn">SHOP NOW</a>
                <a href="menu.php" class="btn secondary-btn">EXPLORE MENU</a>
            </div>
        </div>
    </section>

    <section class="cravings" id="menu">
        <div class="cravings-header">
            <h2>What Are You Craving?</h2>
            <div class="heart-divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <div class="cravings-grid">
            <a href="menu.php?category=cakes" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cake.jfif" alt="Cakes">
                </div>
                <h3>CAKES</h3>
            </a>
            <a href="menu.php?category=breads" class="craving-item">
                <div class="craving-circle">
                    <img src="images/bread.jfif" alt="Breads">
                </div>
                <h3>BREADS</h3>
            </a>
            <a href="menu.php?category=cookies" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cookies.jfif" alt="Cookies">
                </div>
                <h3>COOKIES</h3>
            </a>
            <a href="menu.php?category=cupcakes" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cupcake.jfif" alt="Cupcakes">
                </div>
                <h3>CUPCAKES</h3>
            </a>
        </div>
    </section>

    <section class="best-sellers">
        <div class="section-title-row">
            <h2>BEST SELLERS</h2>
            <a href="menu.php" class="view-all">VIEW ALL &rarr;</a>
        </div>
        <div class="product-grid">
            <?php foreach (featured_products() as $product) {
                echo render_product_card($product);
            } ?>
        </div>
    </section>

    <section class="build-section-wrapper" id="customize-cake">
        <?php
        $customize_return = 'index.php#customize-cake';
        $customize_form_class = 'build-card';
        require __DIR__ . '/includes/customize-cake-form.php';
        ?>
    </section>

    <?php
    $customise_href = '#customize-cake';
    require __DIR__ . '/includes/about-section.php';
    require __DIR__ . '/includes/how-to-order-section.php';
    require __DIR__ . '/includes/site-footer.php';
    ?>
</body>
</html>
