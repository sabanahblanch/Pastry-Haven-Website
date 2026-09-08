<?php
require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

$site_title = "Pastry Haven";
$phone_number = "09263445337";
$email_address = "pastryhaven@gmail.com";
$location = "Dumaguete City";
$current_year = date("Y");

define('SITE_TITLE', $site_title);
define('PHONE_NUMBER', $phone_number);
define('EMAIL_ADDRESS', $email_address);
define('LOCATION', $location);
define('BASE_PATH', dirname(__DIR__));
define('DATA_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'data');
define('UPLOAD_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');

$custom_cake_prices = [
    'Small' => 450,
    'Medium' => 680,
    'Large' => 890,
];

$categories = [
    'cakes' => 'Cakes',
    'breads' => 'Breads',
    'cookies' => 'Cookies',
    'cupcakes' => 'Cupcakes',
];

require_once __DIR__ . '/db-config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

ensure_storage();

if (!defined('INSTALLING')) {
    try {
        db();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Setup needed</title>';
        echo '<link rel="stylesheet" href="style.css"></head><body class="cravings inner-page">';
        echo '<p class="about-text">The bakery database is not ready yet. Open the installer once, then come back here.</p>';
        echo '<a class="cta-button" href="sql/install.php">RUN INSTALLER</a></body></html>';
        exit;
    }
}
