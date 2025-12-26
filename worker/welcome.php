<?php
session_start();
require_once '../config/db.php';
require_once '../includes/ui_components.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../auth/login.php");
    exit;
}
$wk_id = $_SESSION['user_id'];
$active_tasks = $pdo->prepare("SELECT COUNT(*) FROM Worker_Assignments WHERE worker_id = ? AND status = 'assigned'");
$active_tasks->execute([$wk_id]);
$task_count = $active_tasks->fetchColumn();

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
    .orb-1 { width: 300px; height: 300px; background: #f59e0b; top: -50px; right: -50px; }
    .orb-2 { width: 200px; height: 200px; background: #0ea5e9; bottom: -50px; left: -50px; animation-delay: 2s; }
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
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber-500/20 border border-amber-400/30 backdrop-blur-md text-sm font-bold text-amber-100 mb-2 shadow-inner">
                    <i class="fas fa-tools mr-2 text-amber-300"></i> Worker Portal
                </div>
                <h1 class="text-6xl font-black tracking-tight leading-tight">
                    <span class="block text-amber-200 drop-shadow-sm"><?= $greeting ?>,</span>
                    <span class="block text-white drop-shadow-md"><?= htmlspecialchars($_SESSION['name']) ?></span>
                </h1>
                <p class="text-xl text-white/90 max-w-lg font-medium drop-shadow-sm leading-relaxed">
                    View your daily tasks, track progress, and log your hours efficiently.
                </p>
                
                <div class="flex space-x-4 pt-4">
                    <a href="../dashboard.php" class="relative inline-flex items-center justify-center px-8 py-4 overflow-hidden font-bold text-white transition-all duration-300 bg-brand-600 rounded-xl group hover:bg-brand-500 hover:scale-105 hover:shadow-[0_0_40px_-10px_rgba(245,158,11,0.5)]">
                        <span class="absolute inset-x-0 bottom-0 h-[2px] bg-white transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                        <span class="relative flex items-center">View Tasks <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i></span>
                    </a>
                </div>
            </div>

            <!-- Right: 3D Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 perspective-1000">
                <!-- Card 1 -->
                <div class="glass-panel p-6 rounded-2xl shadow-2xl relative group card-3d" data-tilt data-tilt-max="15" data-tilt-speed="400" data-tilt-glare data-tilt-max-glare="0.5">
                    <div class="absolute top-4 right-4 text-amber-600 opacity-80 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-hammer text-3xl"></i>
                    </div>
                    <div class="card-content">
                        <p class="text-xs uppercase tracking-wider text-gray-600 font-bold mb-1">Today's Tasks</p>
                        <h3 class="text-3xl font-extrabold text-gray-900"><?= $task_count ?></h3>
                         <div class="mt-4 flex items-center text-sm text-amber-700 font-bold bg-amber-100 w-max px-2 py-1 rounded-md">
                            <i class="fas fa-exclamation-circle mr-1"></i> Due Soon
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="glass-panel p-6 rounded-2xl shadow-2xl relative group card-3d" data-tilt data-tilt-max="15" data-tilt-speed="400">
                    <div class="absolute top-4 right-4 text-green-600 opacity-80 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-check-double text-3xl"></i>
                    </div>
                    <div class="card-content">
                        <p class="text-xs uppercase tracking-wider text-gray-600 font-bold mb-1">Performance</p>
                        <h3 class="text-3xl font-extrabold text-gray-900">Top 5%</h3>
                         <div class="mt-2 text-xs text-gray-500">Rating this month</div>
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
