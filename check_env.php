<?php
header('Content-Type: text/plain');
echo "=== Environment Diagnostic ===\n";

$url = $_ENV['MYSQL_URL'] ?? $_SERVER['MYSQL_URL'] ?? getenv('MYSQL_URL');
echo "MYSQL_URL: " . ($url ? "Detected (Length: " . strlen($url) . ")" : "Not Set") . "\n";

$host = $_ENV['MYSQLHOST'] ?? $_SERVER['MYSQLHOST'] ?? getenv('MYSQLHOST');
echo "MYSQLHOST: " . ($host ? "Detected ($host)" : "Not Set") . "\n";

echo "============================\n";

if ($url || $host) {
    echo "SUCCESS: Database configuration found!\n";
} else {
    echo "FAILURE: No database variables found. Check Railway Dashboard.\n";
}
?>
