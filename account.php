<?php
require_once __DIR__ . '/includes/config.php';

$user = current_user();
$mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'login';
$login_error = '';
$register_error = '';
$login_email = remembered_email();
$remember_checked = remembered_email() !== '';
$register_values = ['name' => '', 'email' => '', 'phone' => ''];
$next = peek_login_next('');

if ($user) {
    $requested = safe_next_path($_GET['next'] ?? '', '');
    if ($requested !== '' && $requested !== 'account.php') {
        unset($_SESSION['after_login']);
        redirect($requested);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect(account_url($mode, $next));
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $login_email = sanitize_email($_POST['email'] ?? '');
        $remember_checked = isset($_POST['remember']);
        if (!valid_email($login_email) || ($_POST['password'] ?? '') === '') {
            $login_error = 'Please enter a valid email and password.';
            $mode = 'login';
        } else {
            $result = login_user($login_email, $_POST['password'] ?? '');
            if ($result === true) {
                remember_login($login_email, $remember_checked);
                set_flash('success', 'Welcome back! You are now logged in.');
                redirect(consume_login_next(after_login_home()));
            }
            $login_error = $result;
            $mode = 'login';
        }
    }

    if ($action === 'register') {
        $mode = 'signup';
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
}

$user = current_user();
$flash = get_flash();
$orders = $user ? user_orders() : [];
$active_nav = $user ? 'account' : '';
$page_title = $user ? 'My Account' : ($mode === 'signup' ? 'Sign Up' : 'Log In');
$initials = 'PH';
if ($user) {
    $initials = '';
    foreach (preg_split('/\s+/', trim($user['name'])) as $part) {
        if ($part !== '' && strlen($initials) < 2) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }
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
        <?php if ($user): ?>
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
                <p class="about-text">You are signed in. Browse treats, or log out when you are done.</p>
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
                <?php if ($orders): ?>
                    <div class="order-history">
                        <h3 class="about-subtitle">Recent orders</h3>
                        <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                            <div class="step-row">
                                <span class="step-title"><?php echo e($order['id']); ?></span>
                                <span class="about-text"><?php echo format_price($order['total']); ?> · <?php echo e($order['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="admin/index.php" class="cta-button">OPEN ADMIN</a>
                <?php endif; ?>
                <a href="logout.php" class="cta-button">LOG OUT</a>
                <a href="<?php echo e(home_url()); ?>" class="btn secondary-btn">BACK TO HOME</a>
            </div>
        </div>

        <?php elseif ($mode === 'signup'): ?>
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
                    <input type="hidden" name="action" value="register">
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

        <?php else: ?>
        <div class="auth-split">
            <div class="auth-split-left">
                <div class="cravings-header">
                    <h2>Welcome Back</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="about-text"><?php echo $next === 'checkout.php' ? 'Log in to complete your order from our Dumaguete kitchen.' : 'Log in to order freshly baked treats from our Dumaguete kitchen.'; ?></p>
            </div>
            <div class="auth-split-right">
                <?php if ($flash): ?>
                    <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
                <?php endif; ?>
                <h3 class="auth-form-title">LOG IN</h3>
                <?php if ($login_error): ?>
                    <p class="about-text auth-flash auth-flash-error"><?php echo e($login_error); ?></p>
                <?php endif; ?>
                <form method="post" class="build-right js-validate">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="next" value="<?php echo e($next); ?>">
                    <div class="step-row">
                        <span class="step-title">Email</span>
                        <div class="step-input auth-icon-input">
                            <i class="far fa-user" aria-hidden="true"></i>
                            <input type="email" name="email" placeholder="Enter your email" value="<?php echo e($login_email); ?>" maxlength="190" required>
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Password</span>
                        <div class="step-input auth-icon-input">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="heart-btn toggle-password" aria-label="Show password"><i class="far fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="section-title-row">
                        <label class="step-title">
                            <input type="checkbox" name="remember" value="1" <?php echo $remember_checked ? 'checked' : ''; ?>>
                            Remember me
                        </label>
                        <a class="view-all" href="forgot-password.php">Forgot password?</a>
                    </div>
                    <button type="submit" class="cta-button">LOG IN</button>
                </form>
                <a href="<?php echo e(account_url('signup', $next)); ?>" class="btn secondary-btn">CREATE ACCOUNT</a>
                <p class="about-text">Don't have an account? <a href="<?php echo e(account_url('signup', $next)); ?>">Sign Up</a></p>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
    <script src="js/auth.js"></script>
</body>
</html>
