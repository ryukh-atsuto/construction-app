<?php
// Router for PHP built-in server on Railway
// This file handles routing when using php -S

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route to index.php for root
if ($uri === '/' || $uri === '') {
    require_once __DIR__ . '/index.php';
    exit;
}

// Route all other PHP files
$file = __DIR__ . $uri;
if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require_once $file;
    exit;
}

// If file doesn't exist, try to route to index.php
require_once __DIR__ . '/index.php';
