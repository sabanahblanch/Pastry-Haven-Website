<?php
define('INSTALLING', true);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/db-config.php';

$done = false;
$error = '';
$notes = [];

try {
    $root = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $root->exec(
        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', DB_NAME) . '`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    $notes[] = 'Database `' . DB_NAME . '` is ready.';

    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $sql) {
        if ($sql !== '') {
            $pdo->exec($sql);
        }
    }
    $notes[] = 'Tables were created with InnoDB and foreign keys.';

    $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count === 0) {
        $products = require __DIR__ . '/products_seed.php';
        $insert = $pdo->prepare(
            'INSERT INTO products (id, name, price, category, image, reviews, rating, featured, description, details)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($products as $product) {
            $insert->execute([
                $product['id'],
                $product['name'],
                $product['price'],
                $product['category'],
                $product['image'],
                $product['reviews'],
                $product['rating'],
                $product['featured'],
                $product['description'],
                $product['details'],
            ]);
        }
        $notes[] = 'Menu products were seeded.';
    } else {
        $notes[] = 'Products already exist, so seeding was skipped.';
    }

    $usersFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'users.json';
    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($userCount === 0 && is_file($usersFile)) {
        $legacy = json_decode(file_get_contents($usersFile), true);
        if (is_array($legacy) && $legacy) {
            $insertUser = $pdo->prepare(
                'INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)'
            );
            $insertFav = $pdo->prepare(
                'INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)'
            );
            foreach ($legacy as $row) {
                $insertUser->execute([
                    $row['name'] ?? 'Customer',
                    mb_strtolower(trim($row['email'] ?? '')),
                    $row['phone'] ?? '',
                    $row['password'] ?? password_hash('changeme', PASSWORD_DEFAULT),
                ]);
                $newId = (int) $pdo->lastInsertId();
                foreach ($row['favorites'] ?? [] as $productId) {
                    $insertFav->execute([$newId, $productId]);
                }
            }
            $notes[] = 'Existing JSON accounts were imported into MySQL.';
        }
    }

    $done = true;
} catch (Throwable $e) {
    error_log($e->getMessage());
    $error = 'Could not install the database. Make sure MySQL is running in XAMPP (user root, no password).';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install | Pastry Haven</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <section class="about inner-page">
        <div class="page-narrow">
            <div class="cravings-header">
                <h2><?php echo $done ? 'INSTALL COMPLETE' : 'INSTALL NEEDED'; ?></h2>
                <div class="heart-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>
            <?php if ($error): ?>
                <p class="about-text auth-flash auth-flash-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php foreach ($notes as $note): ?>
                <p class="about-text"><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endforeach; ?>
            <?php if ($done): ?>
                <p class="about-text">You can now use the bakery site with MySQL, prepared statements, and account orders.</p>
                <a class="cta-button" href="../index.php">OPEN PASTRY HAVEN</a>
            <?php else: ?>
                <a class="cta-button" href="install.php">TRY AGAIN</a>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
