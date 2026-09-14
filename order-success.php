<?php
require_once __DIR__ . '/includes/config.php';

require_login('menu.php');

$last_id = $_SESSION['last_order']['id'] ?? '';
$order = get_saved_order($last_id);
$user = current_user();
if (
    !$order
    || !$user
    || (
        (int) ($order['user_id'] ?? 0) !== (int) $user['id']
        && ($order['customer']['email'] ?? '') !== ($user['email'] ?? '')
    )
) {
    redirect(home_url());
}

$items = $order['items'] ?? [];
$customer = $order['customer'] ?? [];
$created = strtotime((string) ($order['created_at'] ?? '')) ?: time();
$when = date('d M Y', $created);
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
}
$delivery_fee = 0;
$is_delivery = ($order['fulfillment'] ?? '') === 'Delivery';
$address_label = $is_delivery ? 'Delivery address' : 'Pickup address';
$address_value = $is_delivery
    ? trim((string) ($customer['address'] ?? ''))
    : ($location . ' bakery');
if ($address_value === '') {
    $address_value = $location;
}
$total = (float) ($order['total'] ?? $subtotal);
$active_nav = 'cart';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="confirm-page inner-page">
        <div class="confirm-shell">
            <div class="confirm-left">
                <h2>Thank you for your order!</h2>
                <p class="about-text">Your order will be prepared during bakery hours. We’ll contact you to confirm your freshly baked treats.</p>

                <h3 class="confirm-label">Order details</h3>
                <dl class="confirm-details">
                    <div>
                        <dt>Name</dt>
                        <dd><?php echo e($customer['name'] ?? ''); ?></dd>
                    </div>
                    <div>
                        <dt><?php echo e($address_label); ?></dt>
                        <dd><?php echo e($address_value); ?></dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd><?php echo e($customer['phone'] ?? ''); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?php echo e($customer['email'] ?? ''); ?></dd>
                    </div>
                </dl>

                <a href="menu.php" class="cta-button">BACK TO MENU</a>
            </div>

            <aside class="confirm-ticket">
                <div class="confirm-ticket-hang" aria-hidden="true"></div>
                <div class="confirm-summary">
                    <h3>Order Summary</h3>
                    <div class="confirm-summary-meta">
                        <div>
                            <span>Date</span>
                            <strong><?php echo e($when); ?></strong>
                        </div>
                        <div>
                            <span>Order Number</span>
                            <strong><?php echo e($order['id']); ?></strong>
                        </div>
                        <div>
                            <span>Payment Method</span>
                            <strong><?php echo e($order['payment']); ?></strong>
                        </div>
                        <?php if (!empty($order['ready_date'])): ?>
                            <div>
                                <span>Needed by</span>
                                <strong><?php echo e(date('d M Y', strtotime($order['ready_date']))); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="confirm-perforation" aria-hidden="true">
                        <span class="confirm-notch confirm-notch-left"></span>
                        <span class="confirm-notch confirm-notch-right"></span>
                    </div>

                    <ul class="confirm-items">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $options = is_array($item['options'] ?? null) ? $item['options'] : [];
                            $is_custom = ($item['id'] ?? '') === 'custom-cake'
                                || !empty($options['flavor'])
                                || !empty($options['size'])
                                || !empty($options['dedication'])
                                || !empty($options['reference']);
                            $qty = (int) ($item['qty'] ?? 1);
                            $unit_price = (float) ($item['price'] ?? 0);
                            $line_total = $unit_price * $qty;
                            ?>
                            <li class="confirm-item">
                                <div class="confirm-item-image">
                                    <img src="<?php echo e($item['image'] ?? 'images/logo-.png'); ?>" alt="<?php echo e($item['name']); ?>">
                                </div>
                                <div class="confirm-item-info">
                                    <h4><?php echo e($item['name']); ?></h4>
                                    <?php if ($is_custom && !empty($options['size'])): ?>
                                        <p>Size: <?php echo e($options['size']); ?></p>
                                    <?php endif; ?>
                                    <p>Qty: <?php echo $qty; ?></p>
                                    <?php if ($qty > 1): ?>
                                        <p><?php echo format_price($unit_price); ?> each</p>
                                    <?php endif; ?>
                                    <?php if ($is_custom): ?>
                                        <?php if (!empty($options['flavor'])): ?>
                                            <p>Flavor: <?php echo e($options['flavor']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($options['dedication'])): ?>
                                            <p>Dedication: <?php echo e($options['dedication']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($options['reference'])): ?>
                                            <p>Reference photo:</p>
                                            <img class="confirm-ref-photo" src="<?php echo e($options['reference']); ?>" alt="Cake reference">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <strong class="confirm-item-price"><?php echo format_price($line_total); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="confirm-totals">
                        <p><span>Sub Total</span> <strong><?php echo format_price($subtotal); ?></strong></p>
                        <?php if ($is_delivery): ?>
                            <p><span>Delivery</span> <strong><?php echo $delivery_fee > 0 ? format_price($delivery_fee) : 'Free'; ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($order['notes'])): ?>
                            <p><span>Notes</span> <strong><?php echo e($order['notes']); ?></strong></p>
                        <?php endif; ?>
                        <p class="confirm-total"><span>Order Total</span> <strong><?php echo format_price($total); ?></strong></p>
                    </div>
                </div>
                <svg class="confirm-ticket-zigzag" viewBox="0 0 180 14" preserveAspectRatio="none" aria-hidden="true">
                    <path fill="currentColor" d="M0 0h180v1L172.5 14 165 1l-7.5 13L150 1l-7.5 13L135 1l-7.5 13L120 1l-7.5 13L105 1l-7.5 13L90 1l-7.5 13L75 1l-7.5 13L60 1l-7.5 13L45 1l-7.5 13L30 1 22.5 14 15 1 7.5 14 0 1z"/>
                </svg>
            </aside>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
