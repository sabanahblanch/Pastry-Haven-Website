<?php
require_once __DIR__ . '/includes/config.php';

require_login('checkout.php');

if (is_admin()) {
    set_flash('error', 'Admin accounts cannot buy products.');
    redirect('menu.php');
}

$items = checkout_items();
if (!$items) {
    set_flash('error', 'Please check the items you want to check out.');
    redirect('cart.php');
}

$active_nav = 'cart';
$user = current_user();
$error = '';
$custom_checkout = cart_has_custom_cake($items);
$values = [
    'name' => $user['name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'address' => '',
    'fulfillment' => 'Pickup',
    'payment' => $custom_checkout ? 'Card' : 'Cash on Delivery',
    'ready_date' => '',
    'notes' => '',
    'card_name' => $user['name'] ?? '',
    'card_number' => '',
    'card_expiry' => '',
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
    $values['payment'] = sanitize_string($_POST['payment'] ?? ($custom_checkout ? 'Card' : 'Cash on Delivery'), 40);
    $values['ready_date'] = sanitize_string($_POST['ready_date'] ?? '', 10);
    $values['notes'] = sanitize_string($_POST['notes'] ?? '', 120);
    $values['card_name'] = sanitize_string($_POST['card_name'] ?? '', 100);
    $values['card_number'] = card_digits($_POST['card_number'] ?? '');
    $values['card_expiry'] = sanitize_string($_POST['card_expiry'] ?? '', 5);
    $card_cvv = preg_replace('/\D+/', '', (string) ($_POST['card_cvv'] ?? ''));

    if ($custom_checkout) {
        $values['payment'] = 'Card';
    }

    if ($values['name'] === '' || !valid_email($values['email']) || !valid_phone($values['phone'], true)) {
        $error = 'Please enter your name, a valid email, and a phone number.';
    } elseif ($values['fulfillment'] === 'Delivery' && $values['address'] === '') {
        $error = 'Please enter a delivery address in Dumaguete City.';
    } elseif (!in_array($values['fulfillment'], ['Pickup', 'Delivery'], true)) {
        $error = 'Please choose pickup or delivery.';
    } elseif ($custom_checkout && !valid_ready_date($values['ready_date'])) {
        $error = 'Please select a date at least 1 day from today for your custom cake.';
    } elseif (!in_array($values['payment'], allowed_payments($items), true)) {
        $error = 'Please choose a payment method.';
    } elseif ($values['payment'] === 'Card' && ($values['card_name'] === '' || !valid_card_number($values['card_number']) || !valid_card_expiry($values['card_expiry']) || !valid_card_cvv($card_cvv))) {
        $error = 'Please enter valid card details (name, 13–19 digit number, MM/YY expiry, and CVV).';
    } elseif ($stock_error = cart_stock_issue($items)) {
        $error = $stock_error;
    } else {
        if ($values['payment'] === 'Card') {
            $last4 = substr($values['card_number'], -4);
            $card_note = 'Card ending in ' . $last4;
            $values['notes'] = $values['notes'] === '' ? $card_note : $values['notes'] . ' · ' . $card_note;
            if (mb_strlen($values['notes']) > 120) {
                $values['notes'] = mb_substr($values['notes'], 0, 120);
            }
        }
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
    <section class="cravings auth-page">
        <div class="checkout-box">
            <div class="checkout-box-left">
                <div class="cravings-header">
                    <h2>Checkout</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <ul class="checkout-items">
                    <?php foreach ($items as $item): ?>
                        <li>
                            <span><?php echo e($item['name']); ?> × <?php echo (int) $item['qty']; ?></span>
                            <strong><?php echo format_price($item['price'] * $item['qty']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="checkout-total">Total <?php echo format_price(checkout_subtotal($items)); ?></p>
                <a href="cart.php" class="btn secondary-btn">BACK TO CART</a>
            </div>
            <div class="checkout-box-right">
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
                <div class="step-row" id="delivery-address-fields" <?php echo $values['fulfillment'] === 'Delivery' ? '' : 'hidden'; ?>>
                    <span class="step-title">Address</span>
                    <div class="step-input"><input type="text" name="address" id="delivery-address" placeholder="Dumaguete City address" value="<?php echo e($values['address']); ?>" <?php echo $values['fulfillment'] === 'Delivery' ? 'required' : ''; ?>></div>
                </div>
                <?php if ($custom_checkout): ?>
                    <div class="step-row">
                        <span class="step-title">Select a date</span>
                        <div class="step-input">
                            <input type="date" name="ready_date" value="<?php echo e($values['ready_date']); ?>" min="<?php echo e(earliest_ready_date()); ?>" max="<?php echo e(date('Y-m-d', strtotime('+90 days'))); ?>" required>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="step-row">
                    <span class="step-title">Payment</span>
                    <div class="step-options" id="payment-options">
                        <?php if (!$custom_checkout): ?>
                            <label class="pill-btn <?php echo $values['payment'] === 'Cash on Delivery' ? 'active' : ''; ?>"><input type="radio" name="payment" value="Cash on Delivery" <?php echo $values['payment'] === 'Cash on Delivery' ? 'checked' : ''; ?>> Cash on Delivery</label>
                        <?php endif; ?>
                        <label class="pill-btn <?php echo $values['payment'] === 'Card' ? 'active' : ''; ?>"><input type="radio" name="payment" value="Card" <?php echo $values['payment'] === 'Card' ? 'checked' : ''; ?>> Card</label>
                    </div>
                </div>
                <div id="card-payment-fields" class="card-payment-fields" <?php echo $values['payment'] === 'Card' ? '' : 'hidden'; ?>>
                    <div class="step-row">
                        <span class="step-title">Name on card</span>
                        <div class="step-input"><input type="text" name="card_name" value="<?php echo e($values['card_name']); ?>" maxlength="100" autocomplete="cc-name" placeholder="Name on card"></div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Card number</span>
                        <div class="step-input"><input type="text" name="card_number" value="<?php echo e($values['card_number']); ?>" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="ACCT-000003"></div>
                    </div>
                    <div class="card-payment-row">
                        <div class="step-row">
                            <span class="step-title">Expiry</span>
                            <div class="step-input"><input type="text" name="card_expiry" value="<?php echo e($values['card_expiry']); ?>" maxlength="5" autocomplete="cc-exp" placeholder="MM/YY"></div>
                        </div>
                        <div class="step-row">
                            <span class="step-title">CVV</span>
                            <div class="step-input"><input type="password" name="card_cvv" maxlength="4" inputmode="numeric" autocomplete="cc-csc" placeholder="123"></div>
                        </div>
                    </div>
                </div>
                <div class="step-row">
                    <span class="step-title">Notes <small>(optional)</small></span>
                    <div class="step-input"><input type="text" name="notes" maxlength="120" value="<?php echo e($values['notes']); ?>"></div>
                </div>
                <button type="submit" class="cta-button">PLACE ORDER</button>
            </form>
            <p class="about-text" id="payment-hint"><?php echo $custom_checkout
                ? 'Custom cakes are paid by card and need at least 1 day notice. Choose the date you want your cake ready.'
                : 'Pay in cash at pickup or delivery, or choose Card to pay with a debit or credit card.'; ?></p>
            <a href="cart.php" class="btn secondary-btn">BACK TO CART</a>
            </div>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
