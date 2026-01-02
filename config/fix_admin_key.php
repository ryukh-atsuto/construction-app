<?php
require_once 'db.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['new_key'] ?: 'admin123';

    try {
        // 1. Get User ID
        $stmt = $pdo->prepare("SELECT user_id, role, name FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['role'] !== 'admin') {
                $error = "User '{$user['name']}' is a {$user['role']}, not an admin.";
            } else {
                // 2. Check/Create Admin_Details
                $check = $pdo->prepare("SELECT * FROM Admin_Details WHERE admin_id = ?");
                $check->execute([$user['user_id']]);
                
                if ($check->rowCount() == 0) {
                    // Create missing entry
                    $ins = $pdo->prepare("INSERT INTO Admin_Details (admin_id, admin_level) VALUES (?, 'Start')");
                    $ins->execute([$user['user_id']]);
                    $message .= "Created missing Admin_Details row.<br>";
                }

                // 3. Update Hash
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE Admin_Details SET secondary_password_hash = ? WHERE admin_id = ?");
                $upd->execute([$hash, $user['user_id']]);

                $message .= "<span class='text-green-600 font-bold'>Success!</span> Secondary key for <strong>{$email}</strong> set to: <strong>{$pass}</strong>";
            }
        } else {
            $error = "User not found with email: $email";
        }

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Key</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Admin Key Reset</h1>
            <p class="text-sm text-gray-500">Fix "Invalid Security Key" issues</p>
        </div>

        <?php if($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded mb-4 text-sm">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                <input type="email" name="email" value="admin@test.com" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Security Key</label>
                <input type="text" name="new_key" value="admin123" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50">
                <p class="text-xs text-gray-400 mt-1">Default is admin123</p>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                Fix Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../auth/login.php" class="text-sm text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>
</body>
</html>
