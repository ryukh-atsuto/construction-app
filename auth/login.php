<?php
session_start();
require_once '../config/db.php';

// Prevent logged-in users from accessing the login page
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: ../" . $_SESSION['role'] . "/welcome.php");
    exit();
}

// Language fixed to English
$lang = 'en';

$t = [
    'signin_title' => 'Sign in to your account',
    'start_trial' => 'start your 14-day free trial',
    'or' => 'Or',
    'email' => 'Email address',
    'password' => 'Password',
    'remember_me' => 'Remember me',
    'forgot_password' => 'Forgot your password?',
    'signin_btn' => 'Sign in',
    'input_placeholder_email' => 'you@example.com',
    'protected' => 'Protected by reCAPTCHA',
    'hero_title' => 'Build with precision.',
    'hero_desc' => '"ConstructFlow transformed how we manage our sites. The real-time updates and seamless workflow are game-changers."',
    'footer' => '&copy; 2025 ConstructFlow Inc.'
];

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login Success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                header("Location: ../" . $user['role'] . "/welcome.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (Exception $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ConstructFlow</title>
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
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
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
        body, div, span, a, p, h1, h2, h3, h4, input, button {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
</head>
<body class="bg-white dark:bg-slate-900 font-sans h-screen flex overflow-hidden">

    <!-- Left Side: Cinematic Background -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-brand-900 items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
             <img class="w-full h-full object-cover opacity-40 mix-blend-overlay" src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Construction background">
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
                <img class="h-10 w-10 rounded-full border-2 border-brand-500" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                <div>
                    <p class="text-sm font-bold">David Miller</p>
                    <p class="text-xs text-brand-300">Project Manager, Skyline Inc.</p>
                </div>
            </div>
        </div>
        <!-- Decorative Shapes -->
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
        <div class="absolute top-0 right-0 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 relative bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors duration-500">
        
        <!-- Floating Toggles (Top Right) -->
        <div class="absolute top-6 right-6 flex items-center space-x-4 z-20">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="p-2 text-gray-400 hover:text-brand-600 dark:text-gray-300 dark:hover:text-white transition-colors">
                 <i class="fas fa-moon hidden dark:block text-xl"></i>
                 <i class="fas fa-sun block dark:hidden text-amber-500 text-xl"></i>
            </button>
            

        </div>

        <div class="max-w-md w-full space-y-8">
            <div class="text-center lg:text-left">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white"><?= $t['signin_title'] ?></h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <?= $t['or'] ?> <a href="register.php" class="font-medium text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 underline"><?= $t['start_trial'] ?></a>
                </p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 p-4 rounded-r-md animate-pulse">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-400 font-medium"><?= $error ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="email-address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= $t['email'] ?></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 dark:text-gray-500">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition duration-200" placeholder="<?= $t['input_placeholder_email'] ?>">
                        </div>
                    </div>
                    <div>
                         <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= $t['password'] ?></label>
                         <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 dark:text-gray-500">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition duration-200" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300"><?= $t['remember_me'] ?></label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300"><?= $t['forgot_password'] ?></a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-brand-200 group-hover:text-brand-100"></i>
                        </span>
                        <?= $t['signin_btn'] ?>
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400"><?= $t['protected'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Bottom Bar -->
        <div class="lg:hidden absolute bottom-4 text-center text-xs text-gray-400 dark:text-gray-500">
            <?= $t['footer'] ?>
        </div>
    </div>

</body>
</html>
