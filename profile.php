<?php
// profile.php
session_start();
require_once 'config/db.php';
require_once 'includes/ui_components.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch Primary User Data
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    exit('User not found');
}

// Fetch Role-Specific Data
$role_data = [];
$detail_table = "";
switch ($role) {
    case 'admin': $detail_table = "Admin_Details"; $pk = "admin_id"; break;
    case 'owner': $detail_table = "Owner_Details"; $pk = "owner_id"; break;
    case 'manager': $detail_table = "Manager_Details"; $pk = "manager_id"; break;
    case 'engineer': $detail_table = "Engineer_Details"; $pk = "engineer_id"; break;
    case 'worker': $detail_table = "Worker_Details"; $pk = "worker_id"; break;
}

require_once 'includes/profile_forms.php';

if ($detail_table) {
    $stmt = $pdo->prepare("SELECT * FROM $detail_table WHERE $pk = ?");
    $stmt->execute([$user_id]);
    $role_data = $stmt->fetch();
} else {
    $role_data = [];
}

$page_title = "Smart Profile - " . htmlspecialchars($user['name']);
include 'includes/header.php';
?>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Profile Header -->
    <div class="relative mb-8">
        <div class="h-48 w-full bg-gradient-to-r from-brand-900 to-brand-700 rounded-3xl overflow-hidden shadow-2xl">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        </div>
        
        <div class="absolute -bottom-12 left-8 flex items-end space-x-6 w-full pr-16">
            <div class="relative group flex-shrink-0">
                <div class="w-32 h-32 rounded-3xl border-4 border-white dark:border-slate-900 shadow-xl overflow-hidden bg-white">
                    <img id="avatar-preview" src="<?= htmlspecialchars(!empty($user['profile_picture']) && file_exists('uploads/profiles/' . $user['profile_picture']) ? ('/construction_app/uploads/profiles/' . $user['profile_picture']) : '/construction_app/assets/images/default_avatar.png') ?>" 
                         alt="User Profile" class="w-full h-full object-cover">
                </div>
                <label class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white rounded-3xl">
                    <i class="fas fa-camera text-2xl"></i>
                    <input type="file" name="profile_picture" form="profileForm" class="hidden" onchange="previewImage(this)">
                </label>
            </div>
            
            <div class="pb-4 flex-grow">
                <h1 class="text-3xl font-extrabold text-white drop-shadow-md truncate max-w-md"><?= htmlspecialchars($user['name']) ?></h1>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 text-brand-50 text-[10px] font-bold rounded-full uppercase tracking-wider">
                        <?= $role ?> Portal
                    </span>
                    <span class="text-brand-200 text-xs italic">Member since <?= date('M Y', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-24">
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 p-8 sticky top-24">
                <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Profile Health</h3>
                
                <div class="relative w-40 h-40 mx-auto mb-6">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="80" cy="80" r="72" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100 dark:text-slate-700"/>
                        <circle cx="80" cy="80" r="72" stroke="currentColor" stroke-width="12" fill="transparent" 
                                stroke-dasharray="452.16" 
                                stroke-dashoffset="<?= 452.16 * (1 - (($user['completion_status'] ?? 0) / 100)) ?>"
                                stroke-linecap="round"
                                class="text-brand-600 transition-all duration-1000 ease-out"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span class="text-4xl font-black text-gray-900 dark:text-white"><?= $user['completion_status'] ?? 0 ?>%</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Complete</span>
                    </div>
                </div>
                
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    Complete your profile to gain trust and access high-priority projects.
                </p>
            </div>
        </div>

        <!-- Form Area -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <!-- Step Indicator -->
                <div class="flex border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
                    <div id="tab1" class="flex-1 px-4 py-5 text-center transition-all duration-300 border-b-2 border-brand-600">
                        <span class="text-xs font-black text-brand-600 uppercase tracking-widest">1. Personal</span>
                    </div>
                    <div id="tab2" class="flex-1 px-4 py-5 text-center transition-all duration-300 border-b-2 border-transparent opacity-40">
                        <span class="text-xs font-black text-gray-500 uppercase tracking-widest">2. Profession</span>
                    </div>
                    <div id="tab3" class="flex-1 px-4 py-5 text-center transition-all duration-300 border-b-2 border-transparent opacity-40">
                        <span class="text-xs font-black text-gray-500 uppercase tracking-widest">3. Documents</span>
                    </div>
                </div>

                <div class="p-10">
                    <form id="profileForm" action="actions/update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <input type="hidden" name="role" value="<?= $role ?>">

                        <!-- Step 1: Personal (Common) -->
                        <div id="step1" class="space-y-8 animate-fadeIn">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                                    <div class="relative group">
                                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-brand-500 transition-colors"></i>
                                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                                               class="w-full pl-12 pr-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-brand-500 outline-none transition-all font-semibold">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Phone Number</label>
                                    <div class="relative group">
                                        <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-brand-500 transition-colors"></i>
                                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                                               class="w-full pl-12 pr-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-brand-500 outline-none transition-all font-semibold">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Age</label>
                                    <input type="number" name="age" value="<?= htmlspecialchars($user['age'] ?? '') ?>"
                                               class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-brand-500 outline-none transition-all font-semibold">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Gender</label>
                                    <select name="gender" class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 outline-none focus:border-brand-500 font-semibold">
                                        <option value="Male" <?= ($user['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= ($user['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= ($user['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">National ID / Passport #</label>
                                <input type="text" name="national_id" value="<?= htmlspecialchars($user['national_id'] ?? '') ?>"
                                       class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-brand-500 outline-none transition-all font-semibold">
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Address</label>
                                <textarea name="address" rows="3"
                                          class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-brand-500 outline-none transition-all font-semibold"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="button" onclick="nextStep(2)" 
                                        class="px-10 py-4 bg-brand-600 hover:bg-brand-700 text-white font-black rounded-2xl shadow-xl shadow-brand-500/30 transform hover:-translate-y-1 transition-all active:scale-95 uppercase tracking-widest text-xs">
                                    Continue <i class="fas fa-arrow-right ml-2 opacity-60"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Role Specific -->
                        <?php renderRoleStep2($role, $role_data); ?>

                        <!-- Step 3: Documents -->
                        <?php renderRoleStep3($role, $role_data); ?>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function nextStep(step) {
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.add('hidden');
        
        document.getElementById('step' + step).classList.remove('hidden');
        
        // Update Tabs
        for(let i=1; i<=3; i++) {
            let tab = document.getElementById('tab' + i);
            let text = tab.querySelector('span');
            if(i == step) {
                tab.classList.remove('opacity-40', 'border-transparent');
                tab.classList.add('border-brand-600');
                text.classList.add('text-brand-600');
                text.classList.remove('text-gray-500');
            } else if(i < step) {
                tab.classList.add('opacity-100', 'border-green-500');
                tab.classList.remove('opacity-40', 'border-brand-600', 'border-transparent');
                text.classList.remove('text-brand-600', 'text-gray-500');
                text.classList.add('text-green-600');
            } else {
                tab.classList.add('opacity-40', 'border-transparent');
                tab.classList.remove('border-brand-600', 'border-green-500');
                text.classList.add('text-gray-500');
                text.classList.remove('text-brand-600', 'text-green-600');
            }
        }

        window.scrollTo({ top: 100, behavior: 'smooth' });
    }

    function prevStep(step) {
        nextStep(step);
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
