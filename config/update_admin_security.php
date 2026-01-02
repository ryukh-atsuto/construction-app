<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - Admin Security</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-xl font-bold mb-4">Database Migration</h1>
        <div class="bg-gray-50 p-4 rounded border border-gray-200 mb-4 font-mono text-sm max-h-60 overflow-y-auto">
<?php
echo "Updating Admin Structure...\n<br>";

try {
    // 1. Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM Admin_Details LIKE 'secondary_password_hash'");
    if ($check->rowCount() == 0) {
        // 2. Add the column
        $pdo->exec("ALTER TABLE Admin_Details ADD COLUMN secondary_password_hash VARCHAR(255) DEFAULT NULL");
        echo "✅ Column 'secondary_password_hash' added.<br>";
        
        // 3. Set default secondary password for existing admins (admin123)
        $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE Admin_Details SET secondary_password_hash = ? WHERE secondary_password_hash IS NULL");
        $stmt->execute([$default_hash]);
        echo "✅ Default secondary password set for existing admins.<br>";
    } else {
        echo "ℹ️ Column already exists.<br>";
    }
    
    echo "<strong>Migration Complete.</strong><br>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
        </div>
        <a href="../auth/login.php" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700">Go to Login</a>
    </div>
</body>
</html>
