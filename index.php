<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConstructFlow - Building the Future</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        accent: {
                            500: '#f97316',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-pattern {
            background-color: #0c4a6e;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230ea5e9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-white">

    <!-- Navbar -->
    <nav class="absolute w-full z-20 top-0 left-0 bg-transparent py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0 flex items-center">
                    <img class="h-12 w-auto mr-3 shadow-sm rounded-lg" src="<?= APP_PATH ?>assets/images/logo.png" alt="Logo">
                    <span class="text-2xl font-extrabold text-white tracking-tight">ConstructFlow</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="auth/login.php" class="text-gray-300 hover:text-white font-medium px-3 py-2 transition-colors">Sign In</a>
                    <a href="auth/register.php" class="bg-white text-brand-900 hover:bg-gray-100 px-5 py-2.5 rounded-full font-bold transition-all shadow-lg transform hover:-translate-y-0.5">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative hero-pattern pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-brand-800 border border-brand-700 text-brand-100 font-semibold text-xs mb-6 uppercase tracking-wide">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span> v2.0 Platform Live
                    </div>
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl">
                        <span class="block">Construct with</span>
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-brand-400">Confidence</span>
                    </h1>
                    <p class="mt-4 text-lg text-gray-300 sm:mt-6">
                        The all-in-one platform for effortless project management. Connect owners, architects, and crews in a single, powerful workspace.
                    </p>
                    <div class="mt-8 sm:max-w-lg sm:mx-auto sm:text-center lg:text-left lg:mx-0 flex flex-col sm:flex-row gap-4">
                        <a href="auth/register.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-accent-500 hover:bg-accent-600 md:text-lg md:px-10 shadow-xl transition-all hover:scale-105">
                            Start Building Files
                        </a>
                        <a href="auth/login.php" class="inline-flex items-center justify-center px-8 py-4 border border-gray-600 text-base font-semibold rounded-lg text-gray-200 hover:bg-gray-800 md:text-lg md:px-10 transition-all">
                            Log In
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-gray-400">
                        <i class="fas fa-check-circle text-green-400 mr-2"></i> Free account creation. No card required.
                    </p>
                </div>
                <div class="mt-12 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6 lg:flex lg:items-center">
                    <div class="relative mx-auto w-full rounded-lg shadow-2xl lg:max-w-md overflow-hidden transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-tr from-brand-600 to-transparent opacity-40 z-10"></div>
                        <img class="w-full" src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="App Screenshot">
                         <!-- Floating Badge -->
                        <div class="absolute bottom-6 left-6 z-20 bg-white/95 backdrop-blur rounded-lg p-4 shadow-lg border border-gray-100 max-w-xs">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold">Project Status</p>
                                    <p class="text-sm font-bold text-gray-900">12 Active Sites</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base text-brand-600 font-semibold tracking-wide uppercase">Core Capabilities</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Everything you need to manage construction
                </p>
            </div>

            <div class="mt-20">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                        <div class="absolute -top-6 left-6">
                            <div class="inline-flex items-center justify-center p-3 bg-brand-500 rounded-xl shadow-lg">
                                <i class="fas fa-layer-group text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Project Trak</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Create projects, set timelines, and track progress from excavation to handover with ease.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                        <div class="absolute -top-6 left-6">
                            <div class="inline-flex items-center justify-center p-3 bg-brand-500 rounded-xl shadow-lg">
                                <i class="fas fa-users-cog text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Workforce Mgmt</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Assign managers, engineers, and workers. Track availability and performance ratings.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                        <div class="absolute -top-6 left-6">
                            <div class="inline-flex items-center justify-center p-3 bg-brand-500 rounded-xl shadow-lg">
                                <i class="fas fa-wallet text-white text-xl"></i>
                            </div>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Financials</h3>
                        <p class="mt-2 text-base text-gray-500">
                            Secure payments with automatic admin commissions. Generate AI-powered cost estimates.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-brand-900 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 text-center">
                <div>
                    <div class="text-4xl font-extrabold text-white" id="stat-projects">0</div>
                    <div class="mt-2 text-sm font-medium text-brand-200 uppercase tracking-wider">Projects Completed</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white" id="stat-workers">0</div>
                    <div class="mt-2 text-sm font-medium text-brand-200 uppercase tracking-wider">Expert Workers</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white" id="stat-value">0</div>
                    <div class="mt-2 text-sm font-medium text-brand-200 uppercase tracking-wider">Million USD Value</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white">24/7</div>
                    <div class="mt-2 text-sm font-medium text-brand-200 uppercase tracking-wider">Support</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-white">
        <div class="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Ready to optimize your workflow?
            </h2>
            <p class="mt-4 text-lg leading-6 text-gray-500">
                Join thousands of construction professionals managing their sites better today.
            </p>
            <a href="auth/register.php" class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 sm:w-auto transform hover:scale-105 transition-transform">
                Create Free Account
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Product</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Features</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Pricing</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Enterprise</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Company</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">About</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Careers</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Support</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Contact Sales</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Help Center</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Status</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Privacy</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-gray-900">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-8 md:flex md:items-center md:justify-between">
                <div class="flex space-x-6 md:order-2">
                    <a href="#" class="text-gray-400 hover:text-gray-500"><span class="sr-only">Facebook</span><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-500"><span class="sr-only">Twitter</span><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-500"><span class="sr-only">GitHub</span><i class="fab fa-github"></i></a>
                </div>
                <p class="mt-8 text-base text-gray-400 md:mt-0 md:order-1">
                    &copy; 2025 ConstructFlow Inc. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Simple animation for stats
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = Math.floor(progress * (end - start) + start) + (end > 1000 ? '+' : '');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateValue(document.getElementById("stat-projects"), 0, 500, 2000);
                    animateValue(document.getElementById("stat-workers"), 0, 1200, 2000);
                    animateValue(document.getElementById("stat-value"), 0, 50, 2000);
                    observer.unobserve(entry.target);
                }
            });
        });
        
        const statsSection = document.getElementById("stat-projects");
        if(statsSection) observer.observe(statsSection);
    </script>
