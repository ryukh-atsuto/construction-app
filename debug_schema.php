<?php
require_once 'config/db.php';
try {
    $stmt = $pdo->query("DESCRIBE Users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in Users table: " . implode(", ", $columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
