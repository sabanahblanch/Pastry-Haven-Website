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

function hydrate_product($row)
{
    if (!$row) {
        return null;
    }
    $row['price'] = (float) $row['price'];
    $row['reviews'] = (int) $row['reviews'];
    $row['rating'] = (int) $row['rating'];
    $row['featured'] = !empty($row['featured']);
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
    $product = get_product($product_id);
    if (!$product || $product['id'] === 'custom-cake') {
        return false;
    }
    $qty = max(1, min(20, (int) $qty));
    $cart = get_cart();
    foreach ($cart as &$item) {
        if ($item['id'] === $product_id && empty($item['options'])) {
            $item['qty'] = min(20, $item['qty'] + $qty);
            save_cart($cart);
            return true;
        }
    }
    $cart[] = [
        'key' => bin2hex(random_bytes(6)),
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'qty' => $qty,
        'image' => $product['image'],
        'options' => [],
    ];
    save_cart($cart);
    return true;
}

function add_custom_to_cart($flavor, $size, $dedication, $reference_path = '')
{
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
    $cart[] = [
        'key' => bin2hex(random_bytes(6)),
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
    return true;
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
}

function public_user($row)
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'] ?? '',
    ];
}

function current_user()
{
    return $_SESSION['user'] ?? null;
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
    unset($_SESSION['user']);
    remember_login('', false);
    session_regenerate_id(true);
}

function save_order($fields)
{
    $cart = get_cart();
    if (!$cart) {
        return false;
    }

    $user = current_user();
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
                 fulfillment, payment, notes, total, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
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
                cart_subtotal(),
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
        'items' => $cart,
        'total' => cart_subtotal(),
        'status' => 'Received',
        'created_at' => date('c'),
    ];
    $_SESSION['last_order'] = $order;
    clear_cart();
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
                    <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
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
