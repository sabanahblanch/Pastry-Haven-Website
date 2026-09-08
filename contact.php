<?php
require_once __DIR__ . '/includes/config.php';

$active_nav = 'contact';
$error = '';
$values = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];
$user = current_user();
if ($user) {
    $values['name'] = $user['name'];
    $values['email'] = $user['email'];
    $values['phone'] = $user['phone'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('contact.php');
    }
    $values['name'] = sanitize_string($_POST['name'] ?? '', 100);
    $values['email'] = sanitize_email($_POST['email'] ?? '');
    $values['phone'] = sanitize_phone($_POST['phone'] ?? '');
    $values['message'] = sanitize_string($_POST['message'] ?? '', 1000);

    if ($values['name'] === '' || !valid_email($values['email']) || $values['message'] === '') {
        $error = 'Please enter your name, a valid email, and a message.';
    } elseif ($values['phone'] !== '' && !valid_phone($values['phone'])) {
        $error = 'Please enter a valid phone number.';
    } else {
        save_message($values['name'], $values['email'], $values['phone'], $values['message']);
        set_flash('success', 'Message sent. We will get back to you soon.');
        redirect('contact.php');
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="about-header">
                <h2>CONTACT US</h2>
                <div class="small-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>
            <p class="about-text">Visit us in <?php echo e($location); ?>, call <?php echo e($phone_number); ?>, or send a message below.</p>
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="about-text auth-flash auth-flash-error"><?php echo e($error); ?></p>
            <?php endif; ?>
            <form method="post" class="build-right js-validate">
                <?php echo csrf_field(); ?>
                <div class="step-row">
                    <span class="step-title">Name</span>
                    <div class="step-input"><input type="text" name="name" value="<?php echo e($values['name']); ?>" minlength="2" maxlength="100" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Email</span>
                    <div class="step-input"><input type="email" name="email" value="<?php echo e($values['email']); ?>" maxlength="190" required></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Phone <small>(optional)</small></span>
                    <div class="step-input"><input type="tel" name="phone" value="<?php echo e($values['phone']); ?>" maxlength="15"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Message</span>
                    <div class="step-input"><input type="text" name="message" value="<?php echo e($values['message']); ?>" minlength="5" maxlength="1000" required></div>
                </div>
                <button type="submit" class="cta-button">SEND MESSAGE</button>
            </form>
        </div>
    </section>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
