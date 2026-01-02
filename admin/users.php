<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Manage Users & Roles";
$stmt = $pdo->query("SELECT * FROM Users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include '../includes/layout/header.php';
include '../includes/layout/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include '../includes/layout/topbar.php'; ?>
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
            <a href="#" class="bg-brand-600 text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-brand-700">
                <i class="fas fa-user-plus mr-2"></i> Add User
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Total Users</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= count($users) ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Workers</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <?= array_reduce($users, fn($c, $u) => $c + ($u['role'] === 'worker' ? 1 : 0), 0) ?>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Managers</div>
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                     <?= array_reduce($users, fn($c, $u) => $c + ($u['role'] === 'manager' ? 1 : 0), 0) ?>
                </div>
            </div>
             <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Owners</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                     <?= array_reduce($users, fn($c, $u) => $c + ($u['role'] === 'owner' ? 1 : 0), 0) ?>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-full object-cover" src="<?= !empty($u['profile_picture']) ? '../uploads/profile_pictures/'.$u['profile_picture'] : '../assets/images/default_avatar.png' ?>" alt="">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID: #<?= $u['user_id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 capitalize">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div><?= htmlspecialchars($u['email']) ?></div>
                            <div class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($u['phone']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= date('M d, Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-brand-600 dark:text-brand-400 hover:text-brand-900 dark:hover:text-brand-300">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/layout/footer.php'; ?>
