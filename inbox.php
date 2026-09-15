<?php
require_once __DIR__ . '/includes/config.php';

require_login('inbox.php');
if (is_admin()) {
    redirect('admin/inbox.php');
}

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('inbox.php');
    }
    $body = sanitize_string($_POST['body'] ?? '', 2000);
    if ($body === '') {
        set_flash('error', 'Please type a message.');
        redirect('inbox.php');
    }
    inbox_send($user['id'], $user['id'], $body);
    redirect('inbox.php');
}

$messages = inbox_messages_for_customer($user['id']);
inbox_mark_customer_read($user['id'], $user);
$flash = get_flash();
$active_nav = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="msg-page msg-page-user">
    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <header class="msg-top">
        <h1>Messages</h1>
        <a href="account.php" class="btn secondary-btn">BACK TO PROFILE</a>
    </header>

    <div class="msg-shell msg-shell-user">
        <section class="msg-chat">
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <div class="msg-chat-head">
                <h2>Pastry Haven</h2>
                <p><?php echo e($email_address); ?> · Admin</p>
            </div>
            <div class="msg-log" id="msg-log">
                <?php if (!$messages): ?>
                    <p class="msg-empty">No messages yet. Send a note and the bakery will reply here.</p>
                <?php else: ?>
                    <?php foreach ($messages as $row): ?>
                        <?php $mine = (int) $row['sender_id'] === (int) $user['id']; ?>
                        <article class="msg-bubble <?php echo $mine ? 'is-mine' : 'is-theirs'; ?>">
                            <p><?php echo nl2br(e($row['body'])); ?></p>
                            <small><?php echo e($mine ? 'You' : $row['sender_name']); ?> · <?php echo e(inbox_time($row['created_at'])); ?></small>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form method="post" class="msg-compose" autocomplete="off">
                <?php echo csrf_field(); ?>
                <label class="sr-only" for="user-reply">Reply</label>
                <input id="user-reply" type="text" name="body" maxlength="2000" required placeholder="Reply">
                <button type="submit" class="cta-button">Send</button>
            </form>
        </section>
    </div>
    <script src="js/site.js"></script>
    <script>
    (function () {
        var log = document.getElementById('msg-log');
        if (log) { log.scrollTop = log.scrollHeight; }
    })();
    </script>
</body>
</html>
