<?php
// config/db.php

// Cloud-ready config: Prioritize Railway environment variables
$host = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$db   = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'construction_management');
$user = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
$pass = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
$port = getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: '3306');
$charset = 'utf8mb4';

// Set up DSN (Data Source Name)
// Note: host=localhost on Unix/Linux defaults to socket; use host=127.0.0.1 for TCP
$connection_host = ($host === 'localhost' && getenv('RAILWAY_ENVIRONMENT')) ? '127.0.0.1' : $host;
$dsn = "mysql:host=$connection_host;port=$port;dbname=$db;charset=$charset";

// Dynamic Path Logic
$app_path = getenv('MYSQLHOST') ? '/' : '/construction_app/';
define('APP_PATH', $app_path);
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    if (getenv('RAILWAY_ENVIRONMENT')) {
        $debug_info = [
            'host' => $host,
            'port' => $port,
            'db' => $db,
            'user' => $user,
            'env_detected' => !!getenv('MYSQLHOST')
        ];
        echo "<!-- DB Debug: " . json_encode($debug_info) . " -->";
        exit('Database connection failed. Please ensure your Railway environment variables (MYSQLHOST, MYSQLUSER, etc.) are correctly set in the Variables tab.');
    }
    // For local, show the full error
    die("Database Connection Failed: " . $e->getMessage()); 
}
?>
