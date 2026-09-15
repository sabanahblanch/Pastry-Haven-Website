<?php
require_once __DIR__ . '/includes/config.php';

if (!current_user()) {
    $mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : '';
    redirect(account_url($mode, $_GET['next'] ?? peek_login_next('')));
}

if (is_admin()) {
    redirect('admin/index.php');
}

require_login('account.php');

$user = current_user();
$requested = safe_next_path($_GET['next'] ?? '', '');
if ($requested !== '' && $requested !== 'account.php') {
    unset($_SESSION['after_login']);
    redirect($requested);
}

$flash = get_flash();
$orders = user_orders();
$show_history = isset($_GET['history']);
$active_nav = 'account';
$page_title = 'My Account';
$inbox_unread = inbox_unread_count($user);
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
    <title><?php echo e($page_title . ' | ' . $site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <section class="cravings auth-page">
        <div class="account-stack">
        <?php if (!$show_history): ?>
        <div class="auth-split">
            <div class="auth-split-left">
                <div class="cravings-header">
                    <h2>My Account</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="about-text">You are signed in. Open Inbox to message the bakery, or log out when you are done.</p>
            </div>
            <div class="auth-split-right">
                <?php if ($flash): ?>
                    <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
                <?php endif; ?>
                <div class="icon-circle"><?php echo e($initials); ?></div>
                <h3 class="about-subtitle"><?php echo e($user['name']); ?></h3>
                <p class="about-text"><?php echo e($user['email']); ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p class="about-text"><?php echo e($user['phone']); ?></p>
                <?php endif; ?>
                <a href="inbox.php" class="cta-button">INBOX<?php echo $inbox_unread > 0 ? ' (' . (int) $inbox_unread . ')' : ''; ?></a>
                <a href="account.php?history=1#history" class="cta-button">VIEW HISTORY</a>
                <a href="logout.php" class="cta-button">LOG OUT</a>
                <a href="<?php echo e(home_url()); ?>" class="btn secondary-btn">BACK TO HOME</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($show_history): ?>
        <div class="account-inbox" id="history">
            <div class="section-title-row">
                <h2>Order History</h2>
                <a href="account.php" class="view-all">BACK TO ACCOUNT &rarr;</a>
            </div>
            <?php if (!$orders): ?>
                <p class="about-text">No orders yet. Treats you check out will show up here.</p>
            <?php else: ?>
                <div class="inbox-list order-history-list">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $item_names = [];
                        foreach ($order['items'] ?? [] as $item) {
                            $item_names[] = ((int) ($item['qty'] ?? 1)) . ' × ' . ($item['name'] ?? 'Item');
                        }
                        ?>
                        <div class="inbox-list-item">
                            <strong><?php echo e($order['id']); ?></strong>
                            <p><?php echo e($item_names ? implode(', ', $item_names) : 'Order'); ?></p>
                            <small><?php echo e($order['created_at'] ?? ''); ?> · <?php echo format_price($order['total']); ?> · <?php echo e($order['status']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        </div>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
    <script src="js/auth.js"></script>
</body>
</html>
