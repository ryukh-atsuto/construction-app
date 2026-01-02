<?php
// Removed language logic, fixed to English
$lang = 'en';

// Harden session security: force re-validation but allow browser history for better UX
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class', // Enable class-based dark mode
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        accent: {
                            500: '#f97316',
                            600: '#ea580c',
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
        // Dark mode is always enabled
        document.documentElement.classList.add('dark');
    </script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Hind+Siliguri:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family:
                <?= $lang == 'bn' ? "'Hind Siliguri', sans-serif" : "'Inter', sans-serif" ?>
            ;
        }

        /* Smooth transition for theme toggle */
        body,
        nav,
        div,
        span,
        a,
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark scrollbar */
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 dark:bg-slate-900 dark:text-gray-100 antialiased">
    <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex">
                    <?php
                    $logo_link = isset($_SESSION['role']) ? APP_PATH . $_SESSION['role'] . "/welcome.php" : APP_PATH . "index.php";
                    ?>
                    <a href="<?= $logo_link ?>" class="flex-shrink-0 flex items-center group">
                        <div class="bg-brand-50 dark:bg-white/10 p-1 rounded-md mr-2">
                            <img class="h-8 w-auto" src="<?= APP_PATH ?>assets/images/logo.png"
                                alt="ConstructFlow Logo">
                        </div>
                        <span
                            class="text-xl font-bold text-gray-900 dark:text-white tracking-tight group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            ConstructFlow
                        </span>
                    </a>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $welcome_link = "/construction_app/" . $_SESSION['role'] . "/welcome.php";
                        ?>
                        <a href="<?= $welcome_link ?>"
                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors font-medium flex items-center group hidden md:flex">
                            <span class="mr-2 group-hover:underline">Welcome,
                                <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                                (<?= ucfirst($_SESSION['role'] ?? 'Guest') ?>)</span>
                        </a>
                        <a href="/construction_app/dashboard.php"
                            class="text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                        <a href="/construction_app/profile.php"
                            class="text-brand-600 dark:text-brand-400 font-bold px-3 py-2 rounded-md text-sm flex items-center group">
                            <i class="fas fa-user-circle mr-1.5 group-hover:scale-110 transition-transform"></i> Smart
                            Profile
                        </a>
                        <a href="/construction_app/auth/logout.php"
                            class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="/construction_app/auth/login.php"
                            class="text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="/construction_app/auth/register.php"
                            class="bg-brand-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-brand-500 transition-colors shadow-lg shadow-brand-500/20">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="min-h-screen">