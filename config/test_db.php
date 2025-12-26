<?php
// config/test_db.php
require 'db.php';

try {
    if ($pdo) {
        echo "✅ Connected to the '$db' database successfully!";
    }
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
