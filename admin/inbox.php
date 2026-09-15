<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

$user = current_user();
$selected_id = (int) ($_GET['user'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('inbox.php');
    }
    $selected_id = (int) ($_POST['user_id'] ?? 0);
    $body = sanitize_string($_POST['body'] ?? '', 2000);
    $customer = inbox_customer($selected_id);
    if (!$customer) {
        set_flash('error', 'That conversation was not found.');
        redirect('inbox.php');
    }
    if ($body === '') {
        set_flash('error', 'Please type a reply.');
        redirect('inbox.php?user=' . $selected_id);
    }
    inbox_send($selected_id, $user['id'], $body);
    redirect('inbox.php?user=' . $selected_id);
}

$conversations = inbox_conversations();
if ($selected_id === 0 && $conversations) {
    $selected_id = (int) $conversations[0]['user_id'];
}

$partner = $selected_id ? inbox_customer($selected_id) : null;
if ($selected_id && !$partner) {
    set_flash('error', 'That conversation was not found.');
    redirect('inbox.php');
}

$messages = $partner ? inbox_messages_for_customer($partner['id']) : [];
if ($partner) {
    inbox_mark_customer_read($partner['id'], $user);
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Admin | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="msg-page">
    <header class="msg-top">
        <h1>Messages</h1>
        <a href="index.php">Admin</a>
    </header>

    <div class="msg-shell">
        <aside class="msg-list">
            <?php if (!$conversations): ?>
                <p class="msg-empty">No customer messages yet.</p>
            <?php else: ?>
                <?php foreach ($conversations as $row): ?>
                    <a class="msg-person<?php echo (int) $row['user_id'] === $selected_id ? ' is-active' : ''; ?><?php echo (int) $row['unread'] > 0 ? ' has-unread' : ''; ?>" href="inbox.php?user=<?php echo (int) $row['user_id']; ?>">
                        <strong><?php echo e($row['customer_name']); ?></strong>
                        <span><?php echo e($row['customer_email']); ?></span>
                        <span><?php echo e(inbox_preview($row['last_body'])); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <section class="msg-chat">
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <?php if (!$partner): ?>
                <div class="msg-blank">Select a conversation to read and reply.</div>
            <?php else: ?>
                <div class="msg-chat-head">
                    <h2><?php echo e($partner['name']); ?></h2>
                    <p>
                        <?php echo e($partner['email']); ?>
                        · <a href="users.php">Account #<?php echo (int) $partner['id']; ?></a>
                    </p>
                </div>
                <div class="msg-log" id="msg-log">
                    <?php if (!$messages): ?>
                        <p class="msg-empty">No messages in this conversation yet.</p>
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
                    <input type="hidden" name="user_id" value="<?php echo (int) $partner['id']; ?>">
                    <label class="sr-only" for="admin-reply">Reply</label>
                    <input id="admin-reply" type="text" name="body" maxlength="2000" required placeholder="Reply">
                    <button type="submit" class="cta-button">Send</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
    <script>
    (function () {
        var log = document.getElementById('msg-log');
        if (log) { log.scrollTop = log.scrollHeight; }
    })();
    </script>
</body>
</html>
