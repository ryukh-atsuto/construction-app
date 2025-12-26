<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

// Ensure Project_Estimates table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS Project_Estimates (
        estimate_id INT AUTO_INCREMENT PRIMARY KEY,
        created_by INT NOT NULL,
        project_name VARCHAR(255) NOT NULL,
        project_size FLOAT NOT NULL,
        project_type VARCHAR(50) NOT NULL,
        ai_estimated_cost DECIMAL(15, 2) NOT NULL,
        real_budget DECIMAL(15, 2) NULL,
        plan_image_path VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('draft', 'finalized') DEFAULT 'draft',
        FOREIGN KEY (created_by) REFERENCES Users(user_id)
    )");
} catch (Exception $e) {
    $message = "Database Setup Error: " . $e->getMessage();
}

$estimate = null;
$message = '';
$upload_dir = '../assets/uploads/plans/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['generate_estimate'])) {
        $project_name = $_POST['project_name'];
        $size = $_POST['project_size']; 
        $type = $_POST['project_type'];
        $image_path = '';

        if (isset($_FILES['plan_image']) && $_FILES['plan_image']['error'] == 0) {
            $ext = pathinfo($_FILES['plan_image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['plan_image']['tmp_name'], $upload_dir . $filename)) {
                $image_path = $upload_dir . $filename;
            }
        }

        // Logic for AI Estimate (Simulated)
        $base_rate = ($type == 'commercial') ? 150 : (($type == 'industrial') ? 200 : 100);
        $complexity_multiplier = 1 + (rand(5, 20) / 100); // Random AI factor 1.05 - 1.20
        $total_ai_cost = $size * $base_rate * $complexity_multiplier;

        $materials_cost = $total_ai_cost * 0.6;
        $labor_cost = $total_ai_cost * 0.3;
        $engineer_cost = $total_ai_cost * 0.1;
        
        $estimate = [
            'total' => $total_ai_cost,
            'materials' => $materials_cost,
            'labor' => $labor_cost,
            'engineer' => $engineer_cost,
            'image_path' => $image_path,
            'project_name' => $project_name,
            'size' => $size,
            'type' => $type
        ];
        
        // Save to DB as draft
        $stmt = $pdo->prepare("INSERT INTO Project_Estimates (created_by, project_name, project_size, project_type, ai_estimated_cost, plan_image_path, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')");
        $stmt->execute([$_SESSION['user_id'], $project_name, $size, $type, $total_ai_cost, $image_path]);
        $estimate['id'] = $pdo->lastInsertId();

        $message = "AI Scanning Complete! See the results below.";
    }

    if (isset($_POST['save_real_budget'])) {
        $real_budget = $_POST['real_budget'];
        $estimate_id = $_POST['estimate_id'];
        
        $stmt = $pdo->prepare("UPDATE Project_Estimates SET real_budget = ?, status = 'finalized' WHERE estimate_id = ? AND created_by = ?");
        $stmt->execute([$real_budget, $estimate_id, $_SESSION['user_id']]);
        
        $success_msg = "Budget finalized successfully!";
        header("Location: dashboard.php?msg=" . urlencode($success_msg));
        exit;
    }
}
?>
<?php include '../includes/header.php'; ?>

<style>
    .ai-scanner {
        position: relative;
        overflow: hidden;
    }
    .scanner-line {
        position: absolute;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, transparent, #0ea5e9, transparent);
        top: 0;
        left: 0;
        animation: scan 3s infinite ease-in-out;
        box-shadow: 0 0 15px #0ea5e9;
        z-index: 10;
    }
    @keyframes scan {
        0% { top: 0; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .blur-preview {
        filter: blur(4px);
        transition: filter 1s ease;
    }
    .blur-preview.active {
        filter: blur(0);
    }
</style>

<div class="max-w-6xl mx-auto mt-10 p-4 lg:p-8 min-h-screen">
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white flex items-center">
            <span class="bg-brand-600 text-white p-3 rounded-2xl mr-4 shadow-lg shadow-brand-500/30">
                <i class="fas fa-robot"></i>
            </span>
            AI Cost Analytics
        </h1>
        <p class="mt-2 text-gray-500 dark:text-gray-400">Upload your site photo or construction plan for an instant AI-powered budget breakdown.</p>
    </div>

    <?php if($message): ?>
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-blue-800 dark:text-blue-300 px-4 py-3 rounded mb-8 animate-bounce">
            <i class="fas fa-check-circle mr-2"></i> <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <!-- Left: Upload Section -->
        <div class="xl:col-span-5">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Step 1: Project Details</h3>
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6" id="estimateForm">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Project Reference</label>
                            <input type="text" name="project_name" required placeholder="e.g. Modern Villa - Block A" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 transition-all outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Area (sq ft)</label>
                                <input type="number" name="project_size" required placeholder="1200" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Building Type</label>
                                <select name="project_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 transition-all outline-none">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Upload Plan / Photo</label>
                            <div class="relative group cursor-pointer border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 transition-colors hover:border-brand-500 text-center" onclick="document.getElementById('plan_image').click()">
                                <input type="file" name="plan_image" id="plan_image" class="hidden" onchange="previewFile()">
                                <div id="previewContainer" class="hidden absolute inset-0 z-0 p-2">
                                    <img id="imagePreview" src="#" class="w-full h-full object-cover rounded-xl opacity-30 dark:opacity-20 blur-[1px]">
                                </div>
                                <div class="relative z-10">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-brand-500 transition-colors mb-3"></i>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400" id="uploadText">Click or Drag & Drop construction plan</p>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="generate_estimate" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-brand-500/30 hover:shadow-brand-500/50 transform hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                            <i class="fas fa-microchip"></i>
                            <span>Start AI Analysis</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Results Section -->
        <div class="xl:col-span-7">
            <?php if ($estimate): ?>
                <div class="space-y-6 animate-fade-in">
                    <!-- Scan View -->
                    <div class="bg-gray-900 rounded-3xl overflow-hidden relative ai-scanner shadow-2xl h-64 border border-white/10">
                        <div class="scanner-line"></div>
                        <?php if($estimate['image_path']): ?>
                            <img src="<?= $estimate['image_path'] ?>" class="w-full h-full object-cover opacity-60">
                        <?php else: ?>
                            <div class="h-full flex items-center justify-center bg-brand-950/50">
                                <i class="fas fa-drafting-compass text-gray-700 text-8xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute bottom-4 left-4 flex items-center space-x-2">
                             <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            <span class="text-xs text-green-400 font-mono tracking-tighter uppercase">AI Processing Done... Complexity: 1.4x</span>
                        </div>
                    </div>

                    <!-- Breakdown Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-8 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h2 class="text-sm uppercase tracking-widest text-gray-500 font-bold mb-1">Estimated Project Budget</h2>
                                <p class="text-5xl font-black text-brand-600 dark:text-brand-400">$<?= number_format($estimate['total'], 2) ?></p>
                            </div>
                            <div class="mt-4 md:mt-0 bg-brand-50 dark:bg-brand-900/30 p-4 rounded-2xl border border-brand-100 dark:border-brand-800">
                                <p class="text-xs text-brand-700 dark:text-brand-300 font-bold uppercase mb-1">AI Confidence</p>
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-500" style="width: 94%"></div>
                                    </div>
                                    <span class="text-sm font-black text-brand-900 dark:text-white">94%</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-cubes text-blue-500 mb-2"></i>
                                <p class="text-xs text-gray-500 font-bold uppercase">Materials</p>
                                <p class="text-lg font-bold text-gray-800 dark:text-gray-200">$<?= number_format($estimate['materials'], 2) ?></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-user-hard-hat text-green-500 mb-2"></i>
                                <p class="text-xs text-gray-500 font-bold uppercase">Labor</p>
                                <p class="text-lg font-bold text-gray-800 dark:text-gray-200">$<?= number_format($estimate['labor'], 2) ?></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-hard-hat text-purple-500 mb-2"></i>
                                <p class="text-xs text-gray-500 font-bold uppercase">Engineering</p>
                                <p class="text-lg font-bold text-gray-800 dark:text-gray-200">$<?= number_format($estimate['engineer'], 2) ?></p>
                            </div>
                        </div>

                        <!-- User Input Section -->
                        <div class="bg-gray-50 dark:bg-gray-900 p-6 rounded-2xl border-2 border-brand-500/20">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 2: Finalize Official Budget</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">The AI calculated a realistic estimate. Please provide your confirmed real-world budget to finalize the record.</p>
                            <form method="POST" action="" class="flex flex-col md:flex-row gap-4">
                                <input type="hidden" name="estimate_id" value="<?= $estimate['id'] ?>">
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input type="number" name="real_budget" required step="0.01" value="<?= round($estimate['total'], 2) ?>" class="w-full bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-4 py-3 text-gray-900 dark:text-white focus:border-brand-500 outline-none transition-all">
                                </div>
                                <button type="submit" name="save_real_budget" class="bg-brand-600 text-white font-bold px-8 py-3 rounded-xl hover:bg-brand-700 transition-all shadow-md">
                                    Finalize & Save
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="h-full flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700 p-12 text-center min-h-[500px]">
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-lg mb-6">
                        <i class="fas fa-chart-line text-gray-300 dark:text-gray-600 text-7xl"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Ready for Analysis</h4>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm">Please provide project details and a plan image to generate your first AI estimate.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function previewFile() {
        const preview = document.getElementById('imagePreview');
        const container = document.getElementById('previewContainer');
        const text = document.getElementById('uploadText');
        const file = document.querySelector('input[type=file]').files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
            container.classList.remove('hidden');
            text.innerText = "Plan: " + file.name;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            container.classList.add('hidden');
        }
    }
</script>

<?php include '../includes/footer.php'; ?>

