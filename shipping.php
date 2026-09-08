<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping & Delivery | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="about-header">
                <h2>SHIPPING &amp; DELIVERY</h2>
                <div class="small-divider"><span class="line"></span><i class="fas fa-heart"></i><span class="line"></span></div>
            </div>
            <p class="about-text">Pastry Haven currently serves Dumaguete City. Choose pickup or delivery at checkout.</p>
            <p class="about-text">Same-day orders placed before 11:00 AM are prepared for afternoon pickup or delivery, depending on volume.</p>
            <p class="about-text">Custom cakes may need extra lead time. We will confirm your schedule by phone.</p>
            <a href="checkout.php" class="cta-button">GO TO CHECKOUT</a>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
