<?php
session_start();
require_once '../config/db.php';

// Prevent logged-in users from accessing the registration page
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: ../" . $_SESSION['role'] . "/welcome.php");
    exit();
}

// Language fixed to English
$lang = 'en';

$t = [
    'create_account' => 'Create your account',
    'already_have' => 'Already have an account?',
    'signin_link' => 'Sign in',
    'fullname' => 'Full Name',
    'email' => 'Email Address',
    'password' => 'Password',
    'phone' => 'Phone (Optional)',
    'role_label' => 'I am a...',
    'select_role' => 'Select your role',
    'roles' => [
        'owner' => 'Project Owner',
        'manager' => 'Construction Manager',
        'engineer' => 'Civil Engineer',
        'worker' => 'Skilled Worker',
        'admin' => 'Administrator'
    ],
    'create_btn' => 'Create Account',
    'footer' => '&copy; 2025 ConstructFlow Inc. All rights reserved.',
    'hero_title' => 'Start your next big project.',
    'hero_desc' => '"We scaled our operations by 300% in the first year using ConstructFlow. It\'s essential."'
];

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields except phone are required.";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered.";
            } else {
                // Insert User
                $pdo->beginTransaction();
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $phone, $role]);
                $user_id = $pdo->lastInsertId();

                // Insert into specific role table
                switch ($role) {
                    case 'owner':
                        $stmt = $pdo->prepare("INSERT INTO Owner_Details (owner_id) VALUES (?)");
                        break;
                    case 'worker':
                        $stmt = $pdo->prepare("INSERT INTO Worker_Details (worker_id, availability_status) VALUES (?, 'available')");
                        break;
                    case 'engineer':
                        $stmt = $pdo->prepare("INSERT INTO Engineer_Details (engineer_id) VALUES (?)");
                        break;
                    case 'manager':
                        $stmt = $pdo->prepare("INSERT INTO Manager_Details (manager_id) VALUES (?)");
                        break;
                    case 'admin':
                        $stmt = $pdo->prepare("INSERT INTO Admin_Details (admin_id, admin_level) VALUES (?, 'Standard')");
                        break;
                }
                
                if (isset($stmt)) {
                    $stmt->execute([$user_id]);
                }

                $pdo->commit();
                $success = "Registration successful! You can now login.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - ConstructFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#0ea5e9',
                            600: '#0284c7', // Brand Blue
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e', // Deep Blue
                            950: '#082f49', // Darker Blue
                        },
                        accent: {
                            500: '#f97316',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        bengali: ['Hind Siliguri', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script>
        // Dark Mode Logic
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Smooth transition */
        body, div, span, a, p, h1, h2, h3, h4, input, button, select {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
</head>
<body class="bg-white dark:bg-brand-950 font-sans h-screen flex overflow-hidden">

    <!-- Left Side: Cinematic Background -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-brand-900 items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
             <img class="w-full h-full object-cover opacity-30 mix-blend-overlay" src="https://images.unsplash.com/photo-1590644365607-1c5a38fc43e0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Construction planning">
        </div>
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-brand-900/90 to-brand-800/80 z-0"></div>

        <div class="relative z-10 p-12 text-white max-w-lg">
            <div class="mb-6">
                 <img src="../assets/images/logo.png" alt="Logo" class="h-16 w-auto bg-white/10 p-2 rounded-lg backdrop-blur-sm border border-white/20">
            </div>
            <h2 class="text-4xl font-extrabold tracking-tight mb-4"><?= $t['hero_title'] ?></h2>
            <p class="text-lg text-brand-100 leading-relaxed opacity-90">
                <?= $t['hero_desc'] ?>
            </p>
            <div class="mt-8 flex items-center space-x-4">
                <img class="h-10 w-10 rounded-full border-2 border-brand-500" src="https://randomuser.me/api/portraits/women/44.jpg" alt="User">
                <div>
                    <p class="text-sm font-bold">Sarah Chen</p>
                    <p class="text-xs text-brand-300">Lead Architect, Urban Design</p>
                </div>
            </div>
        </div>
        <!-- Decorative Shapes -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-accent-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-brand-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 overflow-y-auto bg-white dark:bg-brand-900 text-gray-900 dark:text-white transition-colors duration-500">
        
        <!-- Floating Toggles (Top Right) -->
        <div class="absolute top-6 right-6 flex items-center space-x-4 z-20">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="p-2 text-gray-400 hover:text-brand-600 dark:text-brand-300 dark:hover:text-white transition-colors">
                 <i class="fas fa-moon hidden dark:block text-xl"></i>
                 <i class="fas fa-sun block dark:hidden text-amber-500 text-xl"></i>
            </button>
            

        </div>

        <div class="max-w-md w-full space-y-6">
            <div class="text-center lg:text-left">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white"><?= $t['create_account'] ?></h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-brand-200">
                    <?= $t['already_have'] ?> <a href="login.php" class="font-medium text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 underline"><?= $t['signin_link'] ?></a>
                </p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-md">
                    <p class="text-sm text-red-700 dark:text-red-200 font-medium"><?= $error ?></p>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-md">
                    <p class="text-sm text-green-700 dark:text-green-200 font-medium"><?= $success ?> <a href="login.php" class="underline">Login now</a></p>
                </div>
            <?php else: ?>

            <form class="mt-8 space-y-4" action="" method="POST">
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-brand-100 mb-1"><?= $t['fullname'] ?></label>
                        <input name="name" type="text" required class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-brand-700 rounded-lg placeholder-gray-400 dark:placeholder-brand-500/50 bg-white dark:bg-brand-950/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" placeholder="John Doe">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-brand-100 mb-1"><?= $t['email'] ?></label>
                        <input name="email" type="email" required class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-brand-700 rounded-lg placeholder-gray-400 dark:placeholder-brand-500/50 bg-white dark:bg-brand-950/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" placeholder="john@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-brand-100 mb-1"><?= $t['password'] ?></label>
                        <input name="password" type="password" required class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-brand-700 rounded-lg placeholder-gray-400 dark:placeholder-brand-500/50 bg-white dark:bg-brand-950/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-brand-100 mb-1"><?= $t['phone'] ?></label>
                        <input name="phone" type="text" class="appearance-none block w-full px-4 py-3 border border-gray-300 dark:border-brand-700 rounded-lg placeholder-gray-400 dark:placeholder-brand-500/50 bg-white dark:bg-brand-950/50 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" placeholder="+1 (555) 000-0000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-brand-100 mb-1"><?= $t['role_label'] ?></label>
                        <select name="role" required class="block w-full px-4 py-3 border border-gray-300 dark:border-brand-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition bg-white dark:bg-brand-950/50 text-gray-900 dark:text-white">
                            <option value="" disabled selected><?= $t['select_role'] ?></option>
                            <option value="owner"><?= $t['roles']['owner'] ?></option>
                            <option value="manager"><?= $t['roles']['manager'] ?></option>
                            <option value="engineer"><?= $t['roles']['engineer'] ?></option>
                            <option value="worker"><?= $t['roles']['worker'] ?></option>
                            <option value="admin"><?= $t['roles']['admin'] ?></option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <?= $t['create_btn'] ?>
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <div class="mt-6 text-center text-xs text-gray-400 dark:text-brand-400">
                <?= $t['footer'] ?>
            </div>
        </div>
    </div>

</body>
</html>
