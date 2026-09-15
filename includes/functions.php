<?php

function ensure_storage()
{
    foreach ([DATA_DIR, UPLOAD_DIR] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_price($amount)
{
    return '₱' . number_format((float) $amount, 2);
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function current_page_url()
{
    return $_SERVER['REQUEST_URI'] ?? 'index.php';
}

function sanitize_string($value, $max = 255)
{
    $value = trim(strip_tags((string) $value));
    $value = preg_replace('/\s+/', ' ', $value);
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function sanitize_email($value)
{
    return mb_strtolower(trim((string) $value));
}

function valid_email($email)
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize_phone($value)
{
    return preg_replace('/[^0-9+]/', '', (string) $value);
}

function valid_phone($phone, $required = false)
{
    if ($phone === '') {
        return !$required;
    }
    return (bool) preg_match('/^(09\d{9}|\+639\d{9}|\d{7,15})$/', $phone);
}

function cart_has_custom_cake($items = null)
{
    foreach ($items ?? get_cart() as $item) {
        if (($item['id'] ?? '') === 'custom-cake') {
            return true;
        }
    }
    return false;
}

function allowed_payments($items = null)
{
    if (cart_has_custom_cake($items)) {
        return ['Card'];
    }
    return ['Cash on Delivery', 'Card'];
}

function earliest_ready_date()
{
    return date('Y-m-d', strtotime('+1 day'));
}

function valid_ready_date($value)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
        return false;
    }
    return $value >= earliest_ready_date() && $value <= date('Y-m-d', strtotime('+90 days'));
}

function card_digits($value)
{
    return preg_replace('/\D+/', '', (string) $value);
}

function valid_card_number($number)
{
    $digits = card_digits($number);
    $len = strlen($digits);
    return $len >= 13 && $len <= 19;
}

function valid_card_expiry($value)
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', trim((string) $value), $m)) {
        return false;
    }
    $month = (int) $m[1];
    $year = 2000 + (int) $m[2];
    $exp = $year * 100 + $month;
    $now = ((int) date('Y')) * 100 + (int) date('n');
    return $exp >= $now;
}

function valid_card_cvv($value)
{
    return (bool) preg_match('/^\d{3,4}$/', trim((string) $value));
}

function hydrate_product($row)
{
    if (!$row) {
        return null;
    }
    $row['price'] = (float) $row['price'];
    $row['reviews'] = (int) $row['reviews'];
    $row['rating'] = (int) $row['rating'];
    $row['featured'] = !empty($row['featured']);
    $row['stock'] = isset($row['stock']) ? (int) $row['stock'] : 0;
    return $row;
}

function catalog_products()
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $stmt = db_query('SELECT * FROM products WHERE id <> ? ORDER BY featured DESC, name ASC', ['custom-cake']);
    $cache = array_map('hydrate_product', $stmt->fetchAll());
    return $cache;
}

function all_products()
{
    return catalog_products();
}

function get_product($id)
{
    $stmt = db_query('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
    return hydrate_product($stmt->fetch());
}

function featured_products()
{
    $stmt = db_query(
        'SELECT * FROM products WHERE featured = 1 AND id <> ? ORDER BY name ASC',
        ['custom-cake']
    );
    return array_map('hydrate_product', $stmt->fetchAll());
}

function products_by_category($category)
{
    if ($category === '' || $category === 'all') {
        return all_products();
    }
    $stmt = db_query(
        'SELECT * FROM products WHERE category = ? AND id <> ? ORDER BY name ASC',
        [$category, 'custom-cake']
    );
    return array_map('hydrate_product', $stmt->fetchAll());
}

function search_products($query)
{
    $query = trim(mb_strtolower($query));
    if ($query === '') {
        return all_products();
    }
    $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
    $stmt = db_query(
        'SELECT * FROM products
         WHERE id <> ?
           AND (LOWER(name) LIKE ? OR LOWER(category) LIKE ? OR LOWER(description) LIKE ?)
         ORDER BY name ASC',
        ['custom-cake', $like, $like, $like]
    );
    return array_map('hydrate_product', $stmt->fetchAll());
}

function rating_hearts($rating)
{
    $rating = (int) $rating;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="fas fa-heart"></i>'
            : '<i class="far fa-heart"></i>';
    }
    return $html;
}

function get_cart()
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function save_cart($cart)
{
    $_SESSION['cart'] = array_values($cart);
}

function cart_count()
{
    $count = 0;
    foreach (get_cart() as $item) {
        $count += (int) $item['qty'];
    }
    return $count;
}

function cart_subtotal()
{
    $total = 0;
    foreach (get_cart() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

function find_cart_index($key)
{
    foreach (get_cart() as $index => $item) {
        if ($item['key'] === $key) {
            return $index;
        }
    }
    return null;
}

function add_to_cart($product_id, $qty = 1)
{
    if (!can_purchase()) {
        return false;
    }
    $product = get_product($product_id);
    if (!$product || $product['id'] === 'custom-cake') {
        return false;
    }
    $stock = (int) ($product['stock'] ?? 0);
    if ($stock < 1) {
        return false;
    }
    $qty = max(1, min(20, (int) $qty));
    $cart = get_cart();
    foreach ($cart as &$item) {
        if ($item['id'] === $product_id && empty($item['options'])) {
            $item['qty'] = min($stock, min(20, $item['qty'] + $qty));
            save_cart($cart);
            select_cart_item($item['key']);
            return $item['key'];
        }
    }
    unset($item);
    $key = bin2hex(random_bytes(6));
    $cart[] = [
        'key' => $key,
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'qty' => min($stock, $qty),
        'image' => $product['image'],
        'options' => [],
    ];
    save_cart($cart);
    select_cart_item($key);
    return $key;
}

function add_custom_to_cart($flavor, $size, $dedication, $reference_path = '')
{
    if (!can_purchase()) {
        return false;
    }
    global $custom_cake_prices;
    $allowed_flavors = ['Chocolate', 'Vanilla', 'Strawberry', 'Red Velvet'];
    $allowed_sizes = ['Small', 'Medium', 'Large'];
    if (!in_array($flavor, $allowed_flavors, true)) {
        $flavor = 'Chocolate';
    }
    if (!in_array($size, $allowed_sizes, true)) {
        $size = 'Small';
    }
    $dedication = sanitize_string($dedication, 80);
    $price = $custom_cake_prices[$size];
    $cart = get_cart();
    $key = bin2hex(random_bytes(6));
    $cart[] = [
        'key' => $key,
        'id' => 'custom-cake',
        'name' => 'Custom ' . $flavor . ' Cake',
        'price' => $price,
        'qty' => 1,
        'image' => 'images/dark choco.png',
        'options' => [
            'flavor' => $flavor,
            'size' => $size,
            'dedication' => $dedication,
            'reference' => $reference_path,
        ],
    ];
    save_cart($cart);
    select_cart_item($key);
    return $key;
}

function update_cart_qty($key, $qty)
{
    $qty = (int) $qty;
    $cart = get_cart();
    $index = find_cart_index($key);
    if ($index === null) {
        return false;
    }
    if ($qty <= 0) {
        unset($cart[$index]);
    } else {
        $cart[$index]['qty'] = min(20, $qty);
    }
    save_cart($cart);
    return true;
}

function remove_from_cart($key)
{
    $cart = get_cart();
    $index = find_cart_index($key);
    if ($index === null) {
        return false;
    }
    unset($cart[$index]);
    save_cart($cart);
    return true;
}

function clear_cart()
{
    $_SESSION['cart'] = [];
    $_SESSION['checkout_keys'] = [];
}

function can_purchase()
{
    return is_logged_in() && !is_admin();
}

function get_checkout_keys()
{
    $valid = [];
    foreach (get_cart() as $item) {
        $valid[] = $item['key'];
    }
    $keys = $_SESSION['checkout_keys'] ?? null;
    if (!is_array($keys)) {
        return $valid;
    }
    return array_values(array_intersect($keys, $valid));
}

function set_checkout_keys($keys)
{
    $wanted = [];
    foreach ((array) $keys as $key) {
        $wanted[] = sanitize_string((string) $key, 32);
    }
    $valid = [];
    foreach (get_cart() as $item) {
        if (in_array($item['key'], $wanted, true)) {
            $valid[] = $item['key'];
        }
    }
    $_SESSION['checkout_keys'] = $valid;
    return $valid;
}

function select_cart_item($key)
{
    $keys = get_checkout_keys();
    if (!in_array($key, $keys, true)) {
        $keys[] = $key;
    }
    $_SESSION['checkout_keys'] = $keys;
}

function checkout_items()
{
    $keys = get_checkout_keys();
    $items = [];
    foreach (get_cart() as $item) {
        if (in_array($item['key'], $keys, true)) {
            $items[] = $item;
        }
    }
    return $items;
}

function checkout_subtotal($items = null)
{
    $total = 0;
    foreach ($items ?? checkout_items() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

function remove_checkout_items()
{
    $keys = get_checkout_keys();
    $cart = [];
    foreach (get_cart() as $item) {
        if (!in_array($item['key'], $keys, true)) {
            $cart[] = $item;
        }
    }
    save_cart($cart);
    $_SESSION['checkout_keys'] = [];
}

function public_user($row)
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'] ?? '',
        'role' => $row['role'] ?? 'customer',
    ];
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function home_url()
{
    return 'index.php';
}

function after_login_home()
{
    return is_admin() ? 'admin/index.php' : 'menu.php';
}

function safe_next_path($path, $fallback = 'account.php')
{
    $path = trim((string) $path);
    if ($path === '' || strpos($path, '://') !== false || strpos($path, '//') === 0) {
        return $fallback;
    }
    if (isset($path[0]) && ($path[0] === '/' || $path[0] === '\\' || strpos($path, '..') !== false)) {
        return $fallback;
    }
    $file = strtok($path, '?#');
    $allowed = [
        'account.php',
        'cart.php',
        'checkout.php',
        'favorites.php',
        'inbox.php',
        'index.php',
        'login.php',
        'menu.php',
        'product.php',
        'signup.php',
    ];
    if (!in_array($file, $allowed, true)) {
        return $fallback;
    }
    return $path;
}

function peek_login_next($fallback = 'account.php')
{
    $path = $_POST['next'] ?? $_GET['next'] ?? $_SESSION['after_login'] ?? '';
    return safe_next_path($path, $fallback);
}

function consume_login_next($fallback = 'account.php')
{
    $path = peek_login_next($fallback);
    unset($_SESSION['after_login']);
    return $path;
}

function account_url($mode = '', $next = null)
{
    if ($next === null) {
        $next = peek_login_next('');
    }
    $file = $mode === 'signup' ? 'signup.php' : 'login.php';
    $parts = [];
    if ($next !== '' && !in_array($next, ['account.php', 'login.php', 'signup.php'], true)) {
        $parts[] = 'next=' . urlencode(safe_next_path($next, 'login.php'));
    }
    return $parts ? $file . '?' . implode('&', $parts) : $file;
}

function require_login($next = 'account.php', $message = '')
{
    if (current_user()) {
        return;
    }
    $next = safe_next_path($next, 'account.php');
    $_SESSION['after_login'] = $next;
    if ($message === '') {
        $message = $next === 'checkout.php'
            ? 'Please log in or create an account to checkout.'
            : 'Please log in to continue.';
    }
    set_flash('error', $message);
    redirect(account_url('', $next));
}

function find_user_by_email($email)
{
    $stmt = db_query('SELECT * FROM users WHERE email = ? LIMIT 1', [sanitize_email($email)]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function session_favorite_ids()
{
    if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    return $_SESSION['favorites'];
}

function db_favorite_ids($user_id)
{
    $stmt = db_query('SELECT product_id FROM favorites WHERE user_id = ?', [(int) $user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_favorites()
{
    $user = current_user();
    if ($user) {
        return db_favorite_ids($user['id']);
    }
    return session_favorite_ids();
}

function add_favorite_row($user_id, $product_id)
{
    db_query('INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)', [(int) $user_id, $product_id]);
}

function merge_session_favorites($user_id)
{
    foreach (session_favorite_ids() as $product_id) {
        if (get_product($product_id)) {
            add_favorite_row($user_id, $product_id);
        }
    }
    $_SESSION['favorites'] = db_favorite_ids($user_id);
}

function is_favorite($product_id)
{
    return in_array($product_id, get_favorites(), true);
}

function toggle_favorite($product_id)
{
    if (!get_product($product_id) || $product_id === 'custom-cake') {
        return false;
    }
    $user = current_user();
    if ($user) {
        if (is_favorite($product_id)) {
            db_query('DELETE FROM favorites WHERE user_id = ? AND product_id = ?', [$user['id'], $product_id]);
            return false;
        }
        add_favorite_row($user['id'], $product_id);
        return true;
    }

    $favorites = session_favorite_ids();
    if (in_array($product_id, $favorites, true)) {
        $_SESSION['favorites'] = array_values(array_diff($favorites, [$product_id]));
        return false;
    }
    $favorites[] = $product_id;
    $_SESSION['favorites'] = array_values(array_unique($favorites));
    return true;
}

function register_user($name, $email, $phone, $password)
{
    $name = sanitize_string($name, 100);
    $email = sanitize_email($email);
    $phone = sanitize_phone($phone);
    if ($name === '' || !valid_email($email) || strlen($password) < 6) {
        return 'Please enter a valid name, email, and a password of at least 6 characters.';
    }
    if ($phone !== '' && !valid_phone($phone)) {
        return 'Please enter a valid phone number.';
    }
    if (find_user_by_email($email)) {
        return 'That email is already registered. Please log in.';
    }

    try {
        db_query(
            'INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)',
            [$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]
        );
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return 'That email is already registered. Please log in.';
    }

    $id = (int) db()->lastInsertId();
    merge_session_favorites($id);
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => 'customer',
    ];
    return true;
}

function login_user($email, $password)
{
    $user = find_user_by_email($email);
    if (!$user || !password_verify($password, $user['password'])) {
        return 'Incorrect email or password.';
    }
    merge_session_favorites((int) $user['id']);
    session_regenerate_id(true);
    $_SESSION['user'] = public_user($user);
    return true;
}

function remember_login($email, $remember)
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $options = [
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $secure,
    ];

    if ($remember) {
        $options['expires'] = time() + (60 * 60 * 24 * 30);
        setcookie('ph_remember_email', $email, $options);
        setcookie(session_name(), session_id(), $options);
        return;
    }

    $options['expires'] = time() - 3600;
    setcookie('ph_remember_email', '', $options);
}

function remembered_email()
{
    return isset($_COOKIE['ph_remember_email']) ? trim($_COOKIE['ph_remember_email']) : '';
}

function reset_password($email, $password)
{
    $email = sanitize_email($email);
    if (!valid_email($email) || strlen($password) < 6) {
        return 'Please enter a valid email and a password of at least 6 characters.';
    }
    $user = find_user_by_email($email);
    if (!$user) {
        return 'No account was found with that email.';
    }
    db_query(
        'UPDATE users SET password = ? WHERE id = ?',
        [password_hash($password, PASSWORD_DEFAULT), $user['id']]
    );
    return true;
}

function logout_user()
{
    clear_cart();
    unset($_SESSION['user']);
    unset($_SESSION['last_order']);
    remember_login('', false);
    session_regenerate_id(true);
}

function save_order($fields)
{
    $user = current_user();
    $cart = checkout_items();
    if (!$cart || !$user || is_admin()) {
        return false;
    }
    $total = checkout_subtotal($cart);
    $order_id = 'PH-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    $customer = [
        'name' => sanitize_string($fields['name'], 100),
        'email' => sanitize_email($fields['email']),
        'phone' => sanitize_phone($fields['phone']),
        'address' => sanitize_string($fields['address'] ?? '', 255),
    ];

    $pdo = db();
    $pdo->beginTransaction();
    try {
        db_query(
            'INSERT INTO orders
                (id, user_id, customer_name, customer_email, customer_phone, customer_address,
                 fulfillment, payment, notes, ready_date, total, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $order_id,
                $user['id'] ?? null,
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $fields['fulfillment'],
                $fields['payment'],
                sanitize_string($fields['notes'] ?? '', 120),
                !empty($fields['ready_date']) ? $fields['ready_date'] : null,
                $total,
                'Received',
            ]
        );

        foreach ($cart as $item) {
            db_query(
                'INSERT INTO order_items (order_id, product_id, name, price, qty, options_json)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $order_id,
                    $item['id'],
                    $item['name'],
                    $item['price'],
                    $item['qty'],
                    !empty($item['options']) ? json_encode($item['options']) : null,
                ]
            );
            if ($item['id'] !== 'custom-cake') {
                db_query('UPDATE products SET reviews = reviews + 1 WHERE id = ?', [$item['id']]);
                $product = get_product($item['id']);
                if ($product) {
                    $next = max(0, (int) $product['stock'] - (int) $item['qty']);
                    db_query('UPDATE products SET stock = ? WHERE id = ?', [$next, $item['id']]);
                    db_query(
                        'INSERT INTO stock_movements (product_id, qty_change, qty_after, reason, admin_id)
                         VALUES (?, ?, ?, ?, ?)',
                        [$item['id'], -1 * (int) $item['qty'], $next, 'Customer order ' . $order_id, $user['id'] ?? null]
                    );
                }
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        return false;
    }

    $order = [
        'id' => $order_id,
        'user_id' => $user['id'] ?? null,
        'customer' => $customer,
        'fulfillment' => $fields['fulfillment'],
        'payment' => $fields['payment'],
        'notes' => sanitize_string($fields['notes'] ?? '', 120),
        'ready_date' => !empty($fields['ready_date']) ? $fields['ready_date'] : null,
        'items' => $cart,
        'total' => $total,
        'status' => 'Received',
        'created_at' => date('c'),
    ];
    $_SESSION['last_order'] = $order;
    remove_checkout_items();
    return $order;
}

function attach_order_items($order)
{
    $stmt = db_query(
        'SELECT product_id, name, price, qty, options_json FROM order_items WHERE order_id = ?',
        [$order['id']]
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = [
            'id' => $row['product_id'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'qty' => (int) $row['qty'],
            'options' => $row['options_json'] ? json_decode($row['options_json'], true) : [],
        ];
    }
    $order['items'] = $items;
    $order['customer'] = [
        'name' => $order['customer_name'],
        'email' => $order['customer_email'],
        'phone' => $order['customer_phone'],
        'address' => $order['customer_address'],
    ];
    $order['total'] = (float) $order['total'];
    return $order;
}

function order_item_image($item)
{
    if (($item['id'] ?? '') === 'custom-cake') {
        return 'images/dark choco.png';
    }
    $product = get_product($item['id'] ?? '');
    return $product['image'] ?? 'images/logo-.png';
}

function get_saved_order($id)
{
    $id = sanitize_string($id, 32);
    if ($id === '') {
        return null;
    }
    $stmt = db_query('SELECT * FROM orders WHERE id = ? LIMIT 1', [$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $order = attach_order_items($row);
    foreach ($order['items'] as &$item) {
        $item['image'] = order_item_image($item);
    }
    unset($item);
    return $order;
}

function user_orders()
{
    $user = current_user();
    if (!$user) {
        return [];
    }
    $stmt = db_query(
        'SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY created_at DESC',
        [$user['id'], $user['email']]
    );
    return array_map('attach_order_items', $stmt->fetchAll());
}

function save_message($name, $email, $phone, $message)
{
    db_query(
        'INSERT INTO messages (name, email, phone, message) VALUES (?, ?, ?, ?)',
        [
            sanitize_string($name, 100),
            sanitize_email($email),
            sanitize_phone($phone),
            sanitize_string($message, 1000),
        ]
    );
    return true;
}

function handle_reference_upload()
{
    if (empty($_FILES['reference']) || $_FILES['reference']['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($_FILES['reference']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    if ($_FILES['reference']['size'] > 2 * 1024 * 1024) {
        return '';
    }
    $info = @getimagesize($_FILES['reference']['tmp_name']);
    if ($info === false) {
        return '';
    }
    $ext = strtolower(pathinfo($_FILES['reference']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
    if (!in_array($ext, $allowed, true)) {
        return '';
    }
    $filename = 'ref-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($_FILES['reference']['tmp_name'], $dest)) {
        return '';
    }
    return 'uploads/' . $filename;
}

function menu_url($overrides = [])
{
    $params = [
        'category' => $_GET['category'] ?? 'all',
        'q' => trim($_GET['q'] ?? ''),
        'sort' => $_GET['sort'] ?? 'featured',
        'filter' => $_GET['filter'] ?? 'all',
        'page' => $_GET['page'] ?? 1,
    ];
    $params = array_merge($params, $overrides);
    if (($params['category'] ?? 'all') === 'all' || $params['category'] === '') {
        unset($params['category']);
    }
    if (($params['q'] ?? '') === '') {
        unset($params['q']);
    }
    if (($params['sort'] ?? 'featured') === 'featured' || $params['sort'] === '') {
        unset($params['sort']);
    }
    if (($params['filter'] ?? 'all') === 'all' || $params['filter'] === '') {
        unset($params['filter']);
    }
    if ((int) ($params['page'] ?? 1) <= 1) {
        unset($params['page']);
    }
    $query = http_build_query($params);
    return 'menu.php' . ($query === '' ? '' : '?' . $query);
}

function filter_menu_products($items, $filter)
{
    if ($filter === 'featured') {
        return array_values(array_filter($items, function ($product) {
            return !empty($product['featured']);
        }));
    }
    if ($filter === 'under-100') {
        return array_values(array_filter($items, function ($product) {
            return (float) $product['price'] < 100;
        }));
    }
    return array_values($items);
}

function sort_menu_products($items, $sort)
{
    usort($items, function ($a, $b) use ($sort) {
        if ($sort === 'price-asc') {
            return $a['price'] <=> $b['price'];
        }
        if ($sort === 'price-desc') {
            return $b['price'] <=> $a['price'];
        }
        if ($sort === 'name') {
            return strcasecmp($a['name'], $b['name']);
        }
        if ($sort === 'reviews') {
            return $b['reviews'] <=> $a['reviews'];
        }
        return ((int) !empty($b['featured'])) <=> ((int) !empty($a['featured']))
            ?: strcasecmp($a['name'], $b['name']);
    });
    return $items;
}

function product_ready_time($category)
{
    $times = [
        'cakes' => '1 day',
        'breads' => '20 - 40 min',
        'cookies' => '15 - 25 min',
        'cupcakes' => '20 - 40 min',
    ];
    return $times[$category] ?? 'Same day';
}

function category_label($category)
{
    global $categories;
    return $categories[$category] ?? ucfirst((string) $category);
}

function safe_return_path($path)
{
    $path = trim((string) $path);
    if ($path === 'cart.php' || $path === 'menu.php' || strpos($path, 'menu.php?') === 0) {
        return $path;
    }
    if (strpos($path, 'product.php?id=') === 0) {
        return $path;
    }
    return 'cart.php';
}

function render_menu_card($product)
{
    global $categories;
    $fav_icon = is_favorite($product['id']) ? 'fas' : 'far';
    $url = 'product.php?id=' . urlencode($product['id']);
    $label = $categories[$product['category']] ?? ucfirst($product['category']);
    $stock = (int) ($product['stock'] ?? 0);
    $in_stock = $stock > 0;
    $blurb = $product['description'] ?? '';
    if (mb_strlen($blurb) > 90) {
        $blurb = mb_substr($blurb, 0, 87) . '...';
    }
    ob_start();
    ?>
        <article class="menu-card">
            <div class="menu-card-image">
                <button type="button" class="heart-btn" data-id="<?php echo e($product['id']); ?>" aria-label="Add to Favorites">
                    <i class="<?php echo $fav_icon; ?> fa-heart"></i>
                </button>
                <a href="<?php echo e($url); ?>">
                    <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>"<?php echo ($product['id'] ?? '') === 'classic-chocolate-cake' ? ' class="img-zoom-fill"' : ''; ?>>
                </a>
            </div>
            <div class="menu-card-body">
                <div class="menu-card-title-row">
                    <h3><a href="<?php echo e($url); ?>"><?php echo e($product['name']); ?></a></h3>
                    <span class="menu-price-pill"><?php echo format_price($product['price']); ?></span>
                </div>
                <p class="menu-card-desc"><?php echo e($blurb); ?></p>
                <div class="menu-card-meta">
                    <span><i class="fas fa-heart"></i> <?php echo number_format((float) $product['rating'], 1); ?> (<?php echo (int) $product['reviews']; ?>)</span>
                    <span><i class="fas fa-cookie-bite"></i> <?php echo e($label); ?></span>
                    <span class="<?php echo $in_stock ? 'stock-ok' : 'stock-out'; ?>">
                        <i class="fas fa-box"></i>
                        <?php echo $in_stock ? 'In stock (' . $stock . ')' : 'Out of stock'; ?>
                    </span>
                </div>
                <?php if ($in_stock && !is_admin()): ?>
                    <form method="post" action="cart.php" class="menu-card-cart">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo e($product['id']); ?>">
                        <input type="hidden" name="qty" value="1">
                        <input type="hidden" name="return" value="<?php echo e(menu_url()); ?>">
                        <button type="submit" name="action" value="add" class="cta-button">ADD TO CART</button>
                        <button type="submit" name="action" value="buy_now" class="btn secondary-btn">BUY NOW</button>
                    </form>
                <?php elseif ($in_stock && is_admin()): ?>
                    <p class="menu-card-desc">Admins cannot buy products.</p>
                <?php elseif (!$in_stock): ?>
                    <button type="button" class="cta-button" disabled>OUT OF STOCK</button>
                <?php endif; ?>
            </div>
        </article>
    <?php
    return ob_get_clean();
}

function render_product_card($product)
{
    $fav_icon = is_favorite($product['id']) ? 'fas' : 'far';
    $url = 'product.php?id=' . urlencode($product['id']);
    ob_start();
    ?>
        <div class="product-card">
            <div class="product-image">
                <button type="button" class="heart-btn" data-id="<?php echo e($product['id']); ?>" aria-label="Add to Favorites">
                    <i class="<?php echo $fav_icon; ?> fa-heart"></i>
                </button>
                <a href="<?php echo e($url); ?>">
                    <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>"<?php echo ($product['id'] ?? '') === 'classic-chocolate-cake' ? ' class="img-zoom-fill"' : ''; ?>>
                </a>
            </div>
            <div class="product-info">
                <h3><a href="<?php echo e($url); ?>"><?php echo e($product['name']); ?></a></h3>
                <p class="price"><?php echo format_price($product['price']); ?></p>
                <div class="reviews">
                    <span class="hearts">
                        <?php echo rating_hearts($product['rating']); ?>
                    </span>
                    <span class="count">(<?php echo (int) $product['reviews']; ?>)</span>
                </div>
            </div>
        </div>
    <?php
    return ob_get_clean();
}

function is_active_nav($page, $active)
{
    return $page === $active ? 'active' : '';
}
