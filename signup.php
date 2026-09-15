<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) {
    redirect(is_admin() ? 'admin/index.php' : 'account.php');
}

$register_error = '';
$register_values = ['name' => '', 'email' => '', 'phone' => ''];
$next = peek_login_next('');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect(account_url('signup', $next));
    }

    $register_values['name'] = sanitize_string($_POST['name'] ?? '', 100);
    $register_values['email'] = sanitize_email($_POST['email'] ?? '');
    $register_values['phone'] = sanitize_phone($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $register_error = 'Passwords do not match.';
    } else {
        $result = register_user(
            $register_values['name'],
            $register_values['email'],
            $register_values['phone'],
            $password
        );
        if ($result === true) {
            set_flash('success', 'Account created. You are now logged in.');
            redirect(consume_login_next(after_login_home()));
        }
        $register_error = $result;
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <section class="cravings auth-page">
        <div class="auth-split">
            <div class="auth-split-left">
                <div class="cravings-header">
                    <h2>Create Account</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="about-text">Join Pastry Haven to save favorites and check out your order.<?php echo $next === 'checkout.php' ? ' After you sign up, we will take you to checkout.' : ''; ?></p>
            </div>
            <div class="auth-split-right">
                <?php if ($flash): ?>
                    <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
                <?php endif; ?>
                <?php if ($register_error): ?>
                    <p class="about-text auth-flash auth-flash-error"><?php echo e($register_error); ?></p>
                <?php endif; ?>
                <form method="post" class="build-right js-validate">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="next" value="<?php echo e($next); ?>">
                    <div class="step-row">
                        <span class="step-title">Full name</span>
                        <div class="step-input">
                            <input type="text" name="name" placeholder="Enter your name" value="<?php echo e($register_values['name']); ?>" minlength="2" maxlength="100" required>
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Email</span>
                        <div class="step-input">
                            <input type="email" name="email" placeholder="Enter your email" value="<?php echo e($register_values['email']); ?>" maxlength="190" required>
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Phone <small>(Optional)</small></span>
                        <div class="step-input">
                            <input type="tel" name="phone" placeholder="09XXXXXXXXX" value="<?php echo e($register_values['phone']); ?>" maxlength="15" pattern="[0-9+]{7,15}">
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Password</span>
                        <div class="step-input">
                            <input type="password" name="password" placeholder="Create a password" minlength="6" required>
                            <button type="button" class="heart-btn toggle-password" aria-label="Show password"><i class="far fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Confirm password</span>
                        <div class="step-input">
                            <input type="password" name="confirm_password" placeholder="Repeat your password" minlength="6" required>
                            <button type="button" class="heart-btn toggle-password" aria-label="Show password"><i class="far fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="cta-button">CREATE ACCOUNT</button>
                </form>
                <p class="about-text">Already have an account? <a href="<?php echo e(account_url('', $next)); ?>">Log In</a></p>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
    <script src="js/auth.js"></script>
</body>
</html>
