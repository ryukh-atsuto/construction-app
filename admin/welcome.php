<?php
session_start();
require_once '../config/db.php';
require_once '../includes/ui_components.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch Quick Stats for Welcome Page
$total_projects = $pdo->query("SELECT COUNT(*) FROM Projects")->fetchColumn();
$pending_projects = $pdo->query("SELECT COUNT(*) FROM Projects WHERE approval_status = 'pending'")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();

// Dynamic Greeting
$hour = date('H');
$greeting = ($hour < 12) ? "Good Morning" : (($hour < 18) ? "Good Afternoon" : "Good Evening");
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>

<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .hero-gradient {
        background: linear-gradient(135deg, #020617 0%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.3;
        animation: float 10s infinite ease-in-out;
    }
    .orb-1 { width: 300px; height: 300px; background: #6366f1; top: -50px; right: -50px; }
    .orb-2 { width: 200px; height: 200px; background: #ec4899; bottom: -50px; left: -50px; animation-delay: 2s; }
    @keyframes float {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(20px, -20px); }
    }
    .card-3d { transform-style: preserve-3d; transform: perspective(1000px); }
    .card-content { transform: translateZ(20px); }
</style>

<div class="min-h-screen bg-transparent flex flex-col justify-center relative overflow-hidden">
    <!-- Immersive Hero Background -->
    <div class="absolute inset-0 z-0 hero-gradient">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 30px 30px;"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left: Text -->
            <div class="text-white space-y-6 animate-fade-in-up">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/30 backdrop-blur-md text-sm font-bold text-indigo-100 mb-2 shadow-inner">
                    <i class="fas fa-shield-alt mr-2 text-indigo-300"></i> Admin Command Center
                </div>
                <h1 class="text-6xl font-black tracking-tight leading-tight">
                    <span class="block text-indigo-200 drop-shadow-sm"><?= $greeting ?>,</span>
                    <span class="block text-white drop-shadow-md"><?= htmlspecialchars($_SESSION['name']) ?></span>
                </h1>
                <p class="text-xl text-white/90 max-w-lg font-medium drop-shadow-sm leading-relaxed">
                    System overview at a glance. Manage users, approve projects, and monitor platform health in real-time.
                </p>
                
                <div class="flex space-x-4 pt-4">
                    <a href="../dashboard.php" class="relative inline-flex items-center justify-center px-8 py-4 overflow-hidden font-bold text-white transition-all duration-300 bg-indigo-600 rounded-xl group hover:bg-indigo-500 hover:scale-105 hover:shadow-[0_0_40px_-10px_rgba(99,102,241,0.5)]">
                        <span class="absolute inset-x-0 bottom-0 h-[2px] bg-white transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                        <span class="relative flex items-center">Manage System <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i></span>
                    </a>
                </div>
            </div>

            <!-- Right: 3D Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 perspective-1000">
                <!-- Card 1 -->
                <div class="glass-panel p-6 rounded-2xl shadow-2xl relative group card-3d" data-tilt data-tilt-max="15" data-tilt-speed="400" data-tilt-glare data-tilt-max-glare="0.5">
                    <div class="absolute top-4 right-4 text-indigo-500 opacity-80 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-building text-3xl"></i>
                    </div>
                    <div class="card-content">
                        <p class="text-xs uppercase tracking-wider text-gray-600 font-bold mb-1">Total Projects</p>
                        <h3 class="text-3xl font-extrabold text-gray-900"><?= $total_projects ?></h3>
                    </div>
                </div>

                <!-- Card 2 (Actionable) -->
                <div class="glass-panel p-6 rounded-2xl shadow-2xl relative group card-3d" data-tilt data-tilt-max="15" data-tilt-speed="400">
                    <div class="absolute top-4 right-4 text-amber-500 opacity-80 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-clipboard-check text-3xl"></i>
                    </div>
                    <div class="card-content">
                        <p class="text-xs uppercase tracking-wider text-gray-600 font-bold mb-1">Pending Approval</p>
                        <h3 class="text-3xl font-extrabold text-gray-900"><?= $pending_projects ?></h3>
                        <?php if($pending_projects > 0): ?>
                        <div class="mt-2 text-xs text-amber-600 font-bold animate-pulse">Action Required</div>
                        <?php endif; ?>
                    </div>
                </div>

                 <!-- Card 3 -->
                 <div class="glass-panel p-6 rounded-2xl shadow-2xl relative group card-3d sm:col-span-2" data-tilt data-tilt-max="10" data-tilt-speed="400">
                    <div class="flex items-center space-x-4 card-content">
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-500 font-bold">Active User Base</p>
                            <h3 class="text-2xl font-extrabold text-gray-800"><?= $users_count ?> Users</h3>
                        </div>
                        <div class="ml-auto">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Healthy
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    VanillaTilt.init(document.querySelectorAll("[data-tilt]"), { max: 25, speed: 400, glare: true, "max-glare": 0.5 });
</script>

<?php include '../includes/footer.php'; ?>
