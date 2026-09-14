<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('settings.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'bakery') {
        $phone = sanitize_phone($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $location = sanitize_string($_POST['location'] ?? '', 100);
        $low = max(1, (int) ($_POST['low_stock'] ?? 5));
        if (!valid_email($email) || $location === '') {
            set_flash('error', 'Please enter a valid email and location.');
            redirect('settings.php');
        }
        set_setting('phone', $phone);
        set_setting('email', $email);
        set_setting('location', $location);
        set_setting('low_stock', (string) $low);
        set_flash('success', 'Settings saved.');
        redirect('settings.php');
    }

    if ($action === 'password') {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 6) {
            set_flash('error', 'Password must be at least 6 characters.');
            redirect('settings.php');
        }
        if ($password !== $confirm) {
            set_flash('error', 'Passwords do not match.');
            redirect('settings.php');
        }
        db_query(
            'UPDATE users SET password = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), (int) $me['id']]
        );
        set_flash('success', 'Admin password updated.');
        redirect('settings.php');
    }
}

admin_layout_start('Settings', 'settings.php');
?>
            <form method="post" class="build-right admin-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="bakery">
                <h3 class="about-subtitle">Bakery details</h3>
                <div class="step-row">
                    <span class="step-title">Phone</span>
                    <div class="step-input"><input type="tel" name="phone" value="<?php echo e(get_setting('phone', PHONE_NUMBER)); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Email</span>
                    <div class="step-input"><input type="email" name="email" required value="<?php echo e(get_setting('email', EMAIL_ADDRESS)); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Location</span>
                    <div class="step-input"><input type="text" name="location" required value="<?php echo e(get_setting('location', LOCATION)); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Low stock level</span>
                    <div class="step-input"><input type="number" name="low_stock" min="1" value="<?php echo e(low_stock_threshold()); ?>"></div>
                </div>
                <button type="submit" class="cta-button">SAVE SETTINGS</button>
            </form>

            <form method="post" class="build-right admin-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="password">
                <h3 class="about-subtitle">Change admin password</h3>
                <p class="about-text">Signed in as <?php echo e($me['email']); ?></p>
                <div class="step-row">
                    <span class="step-title">New password</span>
                    <div class="step-input"><input type="password" name="password" minlength="6" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Confirm password</span>
                    <div class="step-input"><input type="password" name="confirm_password" minlength="6" required></div>
                </div>
                <button type="submit" class="cta-button">UPDATE PASSWORD</button>
            </form>
<?php
admin_layout_end();
