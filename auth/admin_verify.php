<?php
session_start();
require_once '../config/db.php';

// Ensure user has passed the first step
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_partial'])) {
    header("Location: login.php");
    exit();
}

// Language/Text
$t = [
    'title' => 'Admin Verification',
    'subtitle' => 'Enter your secondary security key',
    'label' => 'Security PIN / Password',
    'btn' => 'Unlock Dashboard',
    'error_invalid' => 'Invalid Security Key',
    'back' => 'Back to Login'
];

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth_key = $_POST['auth_key'];

    if (empty($auth_key)) {
        $error = "Security key is required.";
    } else {
        try {
            // Fetch the Admin's secondary hash
            $stmt = $pdo->prepare("SELECT secondary_password_hash FROM Admin_Details WHERE admin_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $admin_row = $stmt->fetch();

            if ($admin_row && password_verify($auth_key, $admin_row['secondary_password_hash'])) {
                // SUCCESS: Promote session to full admin
                $_SESSION['role'] = 'admin';
                unset($_SESSION['admin_partial']); // Remove partial flag

                header("Location: ../admin/dashboard.php");
                exit;
            } else {
                $error = "Invalid Security Key.";
            }
        } catch (Exception $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Check - ConstructFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl p-8 border-t-4 border-brand-600">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 mb-4">
                <i class="fas fa-shield-alt text-3xl text-brand-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900"><?= $t['title'] ?></h2>
            <p class="text-sm text-gray-500 mt-2"><?= $t['subtitle'] ?></p>
        </div>

        <?php if($error): ?>
            <div class="mb-4 bg-red-50 border-1 border-red-500 text-red-700 p-3 rounded-md text-sm flex items-center">
                <i class="fas fa-times-circle mr-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= $t['label'] ?></label>
                 <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-key"></i>
                    </span>
                    <input type="password" name="auth_key" required class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="••••••">
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all shadow-md">
                <?= $t['btn'] ?>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="logout.php" class="text-sm text-gray-400 hover:text-gray-600">
                <?= $t['back'] ?>
            </a>
        </div>
    </div>

</body>
</html>
