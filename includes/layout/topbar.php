<!-- Topbar -->
<header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 border-b border-gray-200">
    <h1 class="text-xl font-bold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h1>
    
    <div class="flex items-center space-x-4">
        <!-- Notification Bell -->
        <button class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
            <span class="sr-only">View notifications</span>
            <i class="fas fa-bell text-lg"></i>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>
        
        <!-- Profile Dropdown (Simplified) -->
        <a href="../profile.php" class="flex items-center space-x-2 text-gray-700 hover:text-brand-600 transition-colors">
            <span class="text-sm font-medium hidden sm:block">My Profile</span>
            <i class="fas fa-chevron-down text-xs"></i>
        </a>
    </div>
</header>
