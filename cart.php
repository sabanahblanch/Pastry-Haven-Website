<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('cart.php');
    }

    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add', 'buy_now', 'add_custom'], true) && !current_user()) {
        $next = $action === 'add_custom'
            ? safe_next_path($_POST['return'] ?? 'menu.php?category=cakes&customize=1', 'menu.php')
            : safe_next_path($_POST['return'] ?? 'menu.php', 'menu.php');
        require_login($next, 'Please log in or create an account to add items to your cart.');
    }

    if ($action === 'add') {
        $id = $_POST['id'] ?? '';
        $qty = (int) ($_POST['qty'] ?? 1);
        if (add_to_cart($id, $qty)) {
            set_flash('success', 'Added to your cart.');
        } else {
            set_flash('error', 'That pastry could not be added. It may be out of stock.');
        }
        redirect(safe_return_path($_POST['return'] ?? 'cart.php'));
    }

    if ($action === 'buy_now') {
        $id = $_POST['id'] ?? '';
        $qty = (int) ($_POST['qty'] ?? 1);
        if (add_to_cart($id, $qty)) {
            redirect('checkout.php');
        }
        set_flash('error', 'That pastry could not be added. It may be out of stock.');
        redirect(safe_return_path($_POST['return'] ?? 'menu.php'));
    }

    if ($action === 'add_custom') {
        $path = handle_reference_upload();
        add_custom_to_cart(
            $_POST['flavor'] ?? 'Chocolate',
            $_POST['size'] ?? 'Small',
            $_POST['dedication'] ?? '',
            $path
        );
        set_flash('success', 'Your custom cake was added to the cart.');
        redirect('cart.php');
    }

    if ($action === 'update') {
        $key = $_POST['key'] ?? '';
        $qty = (int) ($_POST['qty'] ?? 1);
        update_cart_qty($key, $qty);
        set_flash('success', 'Cart updated.');
        redirect('cart.php');
    }

    if ($action === 'remove') {
        remove_from_cart($_POST['key'] ?? '');
        set_flash('success', 'Item removed.');
        redirect('cart.php');
    }
}

if (($_GET['action'] ?? '') === 'add') {
    require_login(
        safe_next_path($_GET['return'] ?? 'menu.php', 'menu.php'),
        'Please log in or create an account to add items to your cart.'
    );
    $id = $_GET['id'] ?? '';
    if (add_to_cart($id, 1)) {
        set_flash('success', 'Added to your cart.');
    } else {
        set_flash('error', 'That pastry could not be added.');
    }
    redirect('cart.php');
}

$cart = get_cart();
$flash = get_flash();
$active_nav = 'cart';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="best-sellers inner-page">
        <div class="section-title-row">
            <h2>YOUR CART</h2>
            <a href="menu.php" class="view-all">CONTINUE SHOPPING &rarr;</a>
        </div>
        <?php if ($flash): ?>
            <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
        <?php endif; ?>

        <?php if (!$cart): ?>
            <p class="about-text">Your cart is empty. Browse the menu to add a treat.</p>
            <a href="menu.php" class="cta-button">EXPLORE MENU</a>
        <?php else: ?>
            <div class="product-grid cart-list">
                <?php foreach ($cart as $item): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo e($item['name']); ?></h3>
                            <p class="price"><?php echo format_price($item['price'] * $item['qty']); ?></p>
                            <?php if (!empty($item['options'])): ?>
                                <p class="about-text">
                                    <?php echo e($item['options']['flavor'] ?? ''); ?>
                                    <?php echo !empty($item['options']['size']) ? ' · ' . e($item['options']['size']) : ''; ?>
                                    <?php echo !empty($item['options']['dedication']) ? ' · ' . e($item['options']['dedication']) : ''; ?>
                                </p>
                            <?php endif; ?>
                            <form method="post" class="section-title-row">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="key" value="<?php echo e($item['key']); ?>">
                                <div class="step-input">
                                    <input type="number" name="qty" value="<?php echo (int) $item['qty']; ?>" min="1" max="20">
                                </div>
                                <button type="submit" class="pill-btn">Update</button>
                            </form>
                            <form method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="key" value="<?php echo e($item['key']); ?>">
                                <button type="submit" class="view-all">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cravings-header">
                <h2>Total <?php echo format_price(cart_subtotal()); ?></h2>
            </div>
            <?php if ($user): ?>
                <a href="checkout.php" class="cta-button">CHECKOUT</a>
            <?php else: ?>
                <p class="about-text">Log in or create an account to place your order.</p>
                <a href="<?php echo e(account_url('', 'checkout.php')); ?>" class="cta-button">LOG IN TO CHECKOUT</a>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
