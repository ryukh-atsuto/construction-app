<?php
require 'db.php';

try {
    echo "✅ Database connected successfully!";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
