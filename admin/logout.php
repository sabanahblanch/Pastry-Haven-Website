<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    logout_user();
    set_flash('success', 'You have been logged out.');
    redirect('../account.php');
}

redirect('index.php');
