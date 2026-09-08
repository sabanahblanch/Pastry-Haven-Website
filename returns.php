<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="about-header">
                <h2>RETURNS</h2>
                <div class="small-divider"><span class="line"></span><i class="fas fa-heart"></i><span class="line"></span></div>
            </div>
            <p class="about-text">Because our pastries are freshly baked, we cannot accept returns of opened or consumed items.</p>
            <p class="about-text">If your order arrives damaged or incorrect, contact us the same day with your order number and we will make it right.</p>
            <p class="about-text">Custom cakes with dedications or uploaded designs are made to order and cannot be refunded once baking has started.</p>
            <a href="contact.php" class="cta-button">CONTACT US</a>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
