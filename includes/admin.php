<?php

function table_has_column($table, $column)
{
    $stmt = db()->query('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');
    foreach ($stmt->fetchAll() as $col) {
        if (strcasecmp($col['Field'], $column) === 0) {
            return true;
        }
    }
    return false;
}

function ensure_admin_schema()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (!table_has_column('users', 'role')) {
        db()->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'customer'");
    }
    if (!table_has_column('products', 'stock')) {
        db()->exec('ALTER TABLE products ADD COLUMN stock INT UNSIGNED NOT NULL DEFAULT 20');
        db()->exec('UPDATE products SET stock = 20 WHERE stock = 0');
    }
    if (!table_has_column('orders', 'ready_date')) {
        db()->exec('ALTER TABLE orders ADD COLUMN ready_date DATE NULL');
    }

    db()->exec(
        'CREATE TABLE IF NOT EXISTS stock_movements (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id VARCHAR(64) NOT NULL,
            qty_change INT NOT NULL,
            qty_after INT UNSIGNED NOT NULL,
            reason VARCHAR(120) NOT NULL DEFAULT \'\',
            admin_id INT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_move_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_move_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    db()->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(64) PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $defaults = [
        'low_stock' => '5',
        'phone' => PHONE_NUMBER,
        'email' => EMAIL_ADDRESS,
        'location' => LOCATION,
    ];
    $insert = db()->prepare('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($defaults as $key => $value) {
        $insert->execute([$key, $value]);
    }

    $admins = (int) db_query("SELECT COUNT(*) FROM users WHERE role = ?", ['admin'])->fetchColumn();
    if ($admins === 0) {
        $existing = find_user_by_email('admin@pastryhaven.local');
        if ($existing) {
            db_query('UPDATE users SET role = ? WHERE id = ?', ['admin', (int) $existing['id']]);
        } else {
            db_query(
                'INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)',
                [
                    'Pastry Haven Admin',
                    'admin@pastryhaven.local',
                    PHONE_NUMBER,
                    password_hash('Admin123!', PASSWORD_DEFAULT),
                    'admin',
                ]
            );
        }
    }
}

function get_setting($key, $default = '')
{
    $stmt = db_query('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1', [$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function set_setting($key, $value)
{
    db_query(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = ?',
        [$key, $value, $value]
    );
}

function low_stock_threshold()
{
    return max(1, (int) get_setting('low_stock', '5'));
}

function is_admin($user = null)
{
    $user = $user ?: current_user();
    if (!$user) {
        return false;
    }
    if (($user['role'] ?? '') === 'admin') {
        return true;
    }
    $stmt = db_query('SELECT role FROM users WHERE id = ? LIMIT 1', [(int) $user['id']]);
    $row = $stmt->fetch();
    if ($row && ($row['role'] ?? '') === 'admin') {
        $_SESSION['user']['role'] = 'admin';
        return true;
    }
    return false;
}

function require_admin()
{
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Please log in with an admin account.');
        redirect('../account.php');
    }
    $stmt = db_query('SELECT * FROM users WHERE id = ? LIMIT 1', [(int) $user['id']]);
    $row = $stmt->fetch();
    if (!$row || ($row['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Admin only</title>';
        echo '<link rel="stylesheet" href="../style.css"></head><body class="cravings inner-page">';
        echo '<p class="about-text">This page is for bakery staff only.</p>';
        echo '<a class="cta-button" href="../index.php">BACK TO HOME</a></body></html>';
        exit;
    }
    $_SESSION['user'] = public_user($row);
}

function product_slug($name)
{
    $id = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    if ($id === '') {
        $id = 'item-' . bin2hex(random_bytes(3));
    }
    $base = mb_substr($id, 0, 50);
    $id = $base;
    $n = 1;
    while (get_product($id)) {
        $id = $base . '-' . $n;
        $n++;
    }
    return $id;
}

function record_stock_move($product_id, $qty_change, $qty_after, $reason = '')
{
    $admin = current_user();
    db_query(
        'INSERT INTO stock_movements (product_id, qty_change, qty_after, reason, admin_id)
         VALUES (?, ?, ?, ?, ?)',
        [$product_id, (int) $qty_change, max(0, (int) $qty_after), sanitize_string($reason, 120), $admin['id'] ?? null]
    );
}

function adjust_product_stock($product_id, $qty_change, $reason = '')
{
    $product = get_product($product_id);
    if (!$product) {
        return 'That product was not found.';
    }
    $qty_change = (int) $qty_change;
    if ($qty_change === 0) {
        return 'Enter a quantity other than zero.';
    }
    $next = (int) $product['stock'] + $qty_change;
    if ($next < 0) {
        return 'Stock cannot go below zero.';
    }
    db_query('UPDATE products SET stock = ? WHERE id = ?', [$next, $product_id]);
    record_stock_move($product_id, $qty_change, $next, $reason);
    return true;
}

function set_product_stock($product_id, $qty, $reason = 'Stock set')
{
    $product = get_product($product_id);
    if (!$product) {
        return 'That product was not found.';
    }
    $qty = max(0, (int) $qty);
    $change = $qty - (int) $product['stock'];
    db_query('UPDATE products SET stock = ? WHERE id = ?', [$qty, $product_id]);
    record_stock_move($product_id, $change, $qty, $reason);
    return true;
}

function handle_product_image_upload($field = 'image')
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || $_FILES[$field]['size'] > 2 * 1024 * 1024) {
        return '';
    }
    if (@getimagesize($_FILES[$field]['tmp_name']) === false) {
        return '';
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'], true)) {
        return '';
    }
    $filename = 'prod-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return '';
    }
    return 'uploads/' . $filename;
}

function cart_stock_issue()
{
    foreach (get_cart() as $item) {
        if ($item['id'] === 'custom-cake') {
            continue;
        }
        $product = get_product($item['id']);
        if (!$product) {
            return 'A pastry in your cart is no longer available.';
        }
        if ((int) $product['stock'] < (int) $item['qty']) {
            return $product['name'] . ' only has ' . (int) $product['stock'] . ' left.';
        }
    }
    return null;
}

function admin_nav_items()
{
    return [
        'index.php' => ['Dashboard', 'fa-heart'],
        'products.php' => ['Products / Menu', 'fa-birthday-cake'],
        'inventory.php' => ['Inventory / Stocks', 'fa-boxes'],
        'orders.php' => ['Orders', 'fa-receipt'],
        'users.php' => ['Users', 'fa-users'],
        'settings.php' => ['Settings', 'fa-cog'],
    ];
}

function admin_layout_start($title, $active)
{
    require_admin();
    $flash = get_flash();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> | Admin | Pastry Haven</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="index.php">
                <img src="../images/logo-.png" alt="Pastry Haven">
                <span>Admin</span>
            </a>
            <nav>
                <?php foreach (admin_nav_items() as $href => $item): ?>
                    <a href="<?php echo e($href); ?>" class="<?php echo $active === $href ? 'active' : ''; ?>">
                        <i class="fas <?php echo e($item[1]); ?>"></i> <?php echo e($item[0]); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="logout.php" class="admin-logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="cta-button">LOG OUT</button>
            </form>
            <a class="admin-back" href="../index.php">View bakery site</a>
        </aside>
        <main class="admin-main">
            <div class="cravings-header">
                <h2><?php echo e($title); ?></h2>
                <div class="heart-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>
            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
    <?php
}

function admin_layout_end()
{
    ?>
        </main>
    </div>
</body>
</html>
    <?php
}
