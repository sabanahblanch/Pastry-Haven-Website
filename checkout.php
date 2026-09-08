<?php
require_once __DIR__ . '/includes/config.php';

if (!get_cart()) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$user = current_user();
$error = '';
$values = [
    'name' => $user['name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'address' => '',
    'fulfillment' => 'Pickup',
    'payment' => 'Cash on Delivery',
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('checkout.php');
    }

    $values['name'] = sanitize_string($_POST['name'] ?? '', 100);
    $values['email'] = sanitize_email($_POST['email'] ?? '');
    $values['phone'] = sanitize_phone($_POST['phone'] ?? '');
    $values['address'] = sanitize_string($_POST['address'] ?? '', 255);
    $values['fulfillment'] = sanitize_string($_POST['fulfillment'] ?? 'Pickup', 20);
    $values['payment'] = sanitize_string($_POST['payment'] ?? 'Cash on Delivery', 40);
    $values['notes'] = sanitize_string($_POST['notes'] ?? '', 120);

    if ($values['name'] === '' || !valid_email($values['email']) || !valid_phone($values['phone'], true)) {
        $error = 'Please enter your name, a valid email, and a phone number.';
    } elseif ($values['fulfillment'] === 'Delivery' && $values['address'] === '') {
        $error = 'Please enter a delivery address in Dumaguete City.';
    } elseif (!in_array($values['fulfillment'], ['Pickup', 'Delivery'], true)) {
        $error = 'Please choose pickup or delivery.';
    } elseif (!in_array($values['payment'], ['Cash on Delivery', 'GCash'], true)) {
        $error = 'Please choose a payment method.';
    } else {
        $order = save_order($values);
        if ($order) {
            redirect('order-success.php');
        }
        $error = 'We could not place your order. Please try again.';
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="cravings-header">
                <h2>CHECKOUT</h2>
                <div class="heart-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>
            <p class="about-text">Total: <?php echo format_price(cart_subtotal()); ?></p>
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="about-text auth-flash auth-flash-error"><?php echo e($error); ?></p>
            <?php endif; ?>
            <form method="post" class="build-right js-validate">
                <?php echo csrf_field(); ?>
                <div class="step-row">
                    <span class="step-title">Full name</span>
                    <div class="step-input"><input type="text" name="name" value="<?php echo e($values['name']); ?>" minlength="2" maxlength="100" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Email</span>
                    <div class="step-input"><input type="email" name="email" value="<?php echo e($values['email']); ?>" maxlength="190" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Phone</span>
                    <div class="step-input"><input type="tel" name="phone" value="<?php echo e($values['phone']); ?>" maxlength="15" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Fulfillment</span>
                    <div class="step-options">
                        <label class="pill-btn <?php echo $values['fulfillment'] === 'Pickup' ? 'active' : ''; ?>"><input type="radio" name="fulfillment" value="Pickup" <?php echo $values['fulfillment'] === 'Pickup' ? 'checked' : ''; ?>> Pickup</label>
                        <label class="pill-btn <?php echo $values['fulfillment'] === 'Delivery' ? 'active' : ''; ?>"><input type="radio" name="fulfillment" value="Delivery" <?php echo $values['fulfillment'] === 'Delivery' ? 'checked' : ''; ?>> Delivery</label>
                    </div>
                </div>
                <div class="step-row">
                    <span class="step-title">Address <small>(for delivery)</small></span>
                    <div class="step-input"><input type="text" name="address" placeholder="Dumaguete City address" value="<?php echo e($values['address']); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Payment</span>
                    <div class="step-options">
                        <label class="pill-btn <?php echo $values['payment'] === 'Cash on Delivery' ? 'active' : ''; ?>"><input type="radio" name="payment" value="Cash on Delivery" <?php echo $values['payment'] === 'Cash on Delivery' ? 'checked' : ''; ?>> Cash on Delivery</label>
                        <label class="pill-btn <?php echo $values['payment'] === 'GCash' ? 'active' : ''; ?>"><input type="radio" name="payment" value="GCash" <?php echo $values['payment'] === 'GCash' ? 'checked' : ''; ?>> GCash</label>
                    </div>
                </div>
                <div class="step-row">
                    <span class="step-title">Notes <small>(optional)</small></span>
                    <div class="step-input"><input type="text" name="notes" maxlength="120" value="<?php echo e($values['notes']); ?>"></div>
                </div>
                <button type="submit" class="cta-button">PLACE ORDER</button>
            </form>
            <p class="about-text">GCash payments can be sent to <?php echo e($phone_number); ?>.</p>
            <a href="cart.php" class="view-all">BACK TO CART &rarr;</a>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
