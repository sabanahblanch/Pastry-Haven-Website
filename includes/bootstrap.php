<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . DIRECTORY_SEPARATOR . 'php-error.log');

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($exception) {
    error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (!defined('INSTALLING')) {
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Something went wrong</title>';
        echo '<link rel="stylesheet" href="style.css"></head><body class="cravings inner-page">';
        echo '<p class="about-text">Something went wrong. Please try again in a moment.</p>';
        echo '<a class="cta-button" href="index.php">BACK TO HOME</a></body></html>';
    }
    exit;
});
