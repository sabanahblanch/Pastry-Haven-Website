<?php
require_once __DIR__ . '/includes/config.php';
$active_nav = 'order';
$order_section_class = 'how-to-order inner-page';
$customise_href = 'menu.php?category=cakes&customize=1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How to Order | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <?php require __DIR__ . '/includes/how-to-order-section.php'; ?>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
