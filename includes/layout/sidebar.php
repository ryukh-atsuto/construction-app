<?php
$role = $_SESSION['role'] ?? 'guest';
$base_url = '/construction_app'; // Adjust if running in root

// Menu Items based on Role
$menu_items = [];

if ($role === 'admin') {
    $menu_items = [
        ['label' => 'Overview', 'icon' => 'fas fa-chart-pie', 'link' => "$base_url/admin/dashboard.php"],
        ['label' => 'Projects', 'icon' => 'fas fa-building', 'link' => "$base_url/admin/projects.php"],
        ['label' => 'Users', 'icon' => 'fas fa-users', 'link' => "$base_url/admin/users.php"],
        ['label' => 'Financials', 'icon' => 'fas fa-wallet', 'link' => "$base_url/admin/financials.php"],
        ['label' => 'AI Tools', 'icon' => 'fas fa-robot', 'link' => "$base_url/admin/ai_tools.php"], // New Feature
    ];
} elseif ($role === 'manager') {
    $menu_items = [
        ['label' => 'Dashboard', 'icon' => 'fas fa-th-large', 'link' => "$base_url/manager/dashboard.php"],
        ['label' => 'My Sites', 'icon' => 'fas fa-map-marked-alt', 'link' => "$base_url/manager/sites.php"],
        ['label' => 'Workforce', 'icon' => 'fas fa-hard-hat', 'link' => "$base_url/manager/workforce.php"],
    ];
} elseif ($role === 'owner') {
    $menu_items = [
        ['label' => 'My Projects', 'icon' => 'fas fa-home', 'link' => "$base_url/owner/dashboard.php"],
        ['label' => 'Cost Estimate', 'icon' => 'fas fa-calculator', 'link' => "$base_url/owner/estimate_cost.php"], // New Feature
        ['label' => 'Support', 'icon' => 'fas fa-headset', 'link' => "$base_url/owner/support.php"],
    ];
} elseif ($role === 'worker' || $role === 'engineer') {
    $menu_items = [
        ['label' => 'Dashboard', 'icon' => 'fas fa-clipboard-list', 'link' => "$base_url/$role/dashboard.php"],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'link' => "$base_url/profile.php"],
    ];
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="hidden md:flex flex-col w-64 bg-brand-900 border-r border-brand-800">
    <div class="flex items-center justify-center h-16 border-b border-brand-800">
        <span class="text-xl font-bold text-white tracking-wider uppercase">ConstructFlow</span>
    </div>
    
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-2">
            <?php foreach ($menu_items as $item): ?>
                <?php $active = strpos($item['link'], $current_page) !== false; ?>
                <a href="<?= $item['link'] ?>" class="<?= $active ? 'bg-brand-800 text-white' : 'text-brand-100 hover:bg-brand-800 hover:text-white' ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors">
                    <i class="<?= $item['icon'] ?> mr-3 flex-shrink-0 h-6 w-6 text-center pt-1 <?= $active ? 'text-white' : 'text-brand-300 group-hover:text-white' ?>"></i>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- User Profile Snippet (Bottom) -->
    <div class="border-t border-brand-800 p-4">
        <div class="flex items-center justify-between mb-4">
            <span class="text-xs text-brand-400 font-semibold uppercase tracking-wider">Settings</span>
            <button onclick="toggleTheme()" class="text-brand-400 hover:text-white transition-colors focus:outline-none" title="Toggle Dark Mode">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
        </div>
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php
                $pp = $_SESSION['profile_picture'] ?? 'default';
                // Check if file physically exists
                $pp_path = $_SERVER['DOCUMENT_ROOT'] . $base_url . '/uploads/profile_pictures/' . $pp;
                $display_img = (!empty($pp) && $pp !== 'default' && file_exists($pp_path)) 
                    ? "$base_url/uploads/profile_pictures/$pp" 
                    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['name'] ?? 'User') . "&background=random";
                ?>
                <img class="h-10 w-10 rounded-full border-2 border-brand-600 object-cover" src="<?= $display_img ?>" alt="">
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white line-clamp-1"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></p>
                <a href="<?= $base_url ?>/auth/logout.php" class="text-xs font-medium text-brand-300 hover:text-brand-100 flex items-center mt-1">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign out
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Header (Visible only on small screens) -->
<div class="md:hidden bg-brand-900 text-white flex items-center justify-between p-4">
    <span class="font-bold">ConstructFlow</span>
    <button class="text-white focus:outline-none">
        <i class="fas fa-bars"></i>
    </button>
</div>
