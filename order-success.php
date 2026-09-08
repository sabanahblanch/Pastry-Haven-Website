<?php
require_once __DIR__ . '/includes/config.php';

$order = $_SESSION['last_order'] ?? null;
if (!$order) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Received | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="cravings inner-page">
        <div class="cravings-header">
            <h2>Thank You</h2>
            <div class="heart-divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <p class="about-text">Your order <strong><?php echo e($order['id']); ?></strong> has been received.</p>
        <p class="about-text">Total <?php echo format_price($order['total']); ?> · <?php echo e($order['fulfillment']); ?> · <?php echo e($order['payment']); ?></p>
        <p class="about-text">We'll contact you at <?php echo e($order['customer']['phone']); ?> to confirm your freshly baked order.</p>
        <a href="menu.php" class="cta-button">ORDER MORE</a>
        <a href="index.php" class="btn secondary-btn">BACK TO HOME</a>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
