<?php
require_once __DIR__ . '/includes/config.php';

$id = $_GET['id'] ?? '';
if ($id === '') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'on' => false]);
        exit;
    }
    redirect('favorites.php');
}

$on = toggle_favorite($id);
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'on' => $on]);
    exit;
}

set_flash('success', $on ? 'Saved to favorites.' : 'Removed from favorites.');
$back = $_SERVER['HTTP_REFERER'] ?? 'favorites.php';
$host = parse_url($back, PHP_URL_HOST);
if ($host && isset($_SERVER['HTTP_HOST']) && $host !== $_SERVER['HTTP_HOST']) {
    $back = 'favorites.php';
}
$path = parse_url($back, PHP_URL_PATH) ?? '';
if (preg_match('#/favorite\.php$#', $path)) {
    $back = 'favorites.php';
}
redirect($back);
