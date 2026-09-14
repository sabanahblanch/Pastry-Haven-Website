<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="about-header">
                <h2>FAQ</h2>
                <div class="small-divider"><span class="line"></span><i class="fas fa-heart"></i><span class="line"></span></div>
            </div>
            <h3 class="about-subtitle">Do you deliver?</h3>
            <p class="about-text">Yes. We deliver within Dumaguete City. Pickup is also available.</p>
            <h3 class="about-subtitle">How far in advance should I order a cake?</h3>
            <p class="about-text">Custom cakes are best ordered at least one day ahead. Ready items can be ordered the same day before 11:00 AM.</p>
            <h3 class="about-subtitle">Can I add a dedication?</h3>
            <p class="about-text">Yes. Use Build Your Own or the notes field at checkout.</p>
            <h3 class="about-subtitle">What payment methods do you accept?</h3>
            <p class="about-text">Cash on delivery or pickup, and card at checkout.</p>
            <a href="contact.php" class="cta-button">CONTACT US</a>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
