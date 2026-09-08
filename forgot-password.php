<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) {
    redirect('account.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('forgot-password.php');
    }

    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = reset_password($email, $password);
        if ($result === true) {
            set_flash('success', 'Password updated. You can now log in.');
            redirect('account.php');
        }
        $error = $result;
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <section class="cravings auth-page">
        <div class="product-card auth-card">
            <div class="auth-card-body">
                <div class="icon-circle auth-icon"><i class="fas fa-heart"></i></div>
                <div class="cravings-header">
                    <h2>Reset Password</h2>
                    <div class="heart-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <p class="about-text">Enter the email on your account and choose a new password.</p>
                <?php if ($flash): ?>
                    <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p class="about-text auth-flash auth-flash-error"><?php echo e($error); ?></p>
                <?php endif; ?>
                <form method="post" class="build-right js-validate">
                    <?php echo csrf_field(); ?>
                    <div class="step-row">
                        <span class="step-title">Email</span>
                        <div class="step-input">
                            <input type="email" name="email" placeholder="Enter your email" value="<?php echo e($email); ?>" required>
                        </div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">New password</span>
                        <div class="step-input">
                            <input type="password" name="password" placeholder="Create a new password" minlength="6" required>
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
                    <button type="submit" class="cta-button">UPDATE PASSWORD</button>
                </form>
                <p class="about-text">Remembered it? <a href="account.php">Log In</a></p>
            </div>
            <div class="product-info">
                <p class="price">Freshly baked pastries made with love</p>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
    <script src="js/auth.js"></script>
</body>
</html>
