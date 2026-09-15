<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) {
    redirect(is_admin() ? 'admin/index.php' : 'account.php');
}

$login_error = '';
$login_email = remembered_email();
$remember_checked = remembered_email() !== '';
$next = peek_login_next('');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect(account_url('', $next));
    }

    $login_email = sanitize_email($_POST['email'] ?? '');
    $remember_checked = isset($_POST['remember']);
    if (!valid_email($login_email) || ($_POST['password'] ?? '') === '') {
        $login_error = 'Please enter a valid email and password.';
    } else {
        $result = login_user($login_email, $_POST['password'] ?? '');
        if ($result === true) {
            remember_login($login_email, $remember_checked);
            set_flash('success', 'Welcome back! You are now logged in.');
            if (is_admin()) {
                unset($_SESSION['after_login']);
                redirect('admin/index.php');
            }
            redirect(consume_login_next(after_login_home()));
        }
        $login_error = $result;
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <section class="cravings auth-page">
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
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
    <script src="js/auth.js"></script>
</body>
</html>
