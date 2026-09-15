<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('login.php');
    }
    logout_user();
    set_flash('success', 'You have been logged out.');
    redirect('index.php');
}

if (!current_user()) {
    redirect('login.php');
}

$user = current_user();
$active_nav = 'logout';
$initials = '';
foreach (preg_split('/\s+/', trim($user['name'])) as $part) {
    if ($part !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'PH';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Out | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <section class="cravings auth-page">
        <div class="auth-split">
            <div class="auth-split-left">
                <div class="cravings-header">
                    <h2>Log Out</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="about-text">Are you sure you want to log out of Pastry Haven?</p>
            </div>
            <div class="auth-split-right">
                <div class="icon-circle"><?php echo e($initials); ?></div>
                <h3 class="about-subtitle"><?php echo e($user['name']); ?></h3>
                <p class="about-text"><?php echo e($user['email']); ?></p>
                <form method="post" action="logout.php">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="cta-button">LOG OUT</button>
                </form>
                <a href="account.php" class="btn secondary-btn">STAY SIGNED IN</a>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
