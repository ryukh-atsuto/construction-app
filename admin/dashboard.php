<?php
// Admin Dashboard
if (!defined('APP_RUNNING')) { header("Location: ../dashboard.php"); exit; }
// The session check and include header are handled by parent dashboard.php, 
// BUT this file is now linked directly from welcome.php in some flows? 
// No, the welcome page links to ../dashboard.php, which includes this file.
// So we can assume $pdo is available from dashboard.php.

// Fetch Stats
$total_projects = $pdo->query("SELECT COUNT(*) FROM Projects")->fetchColumn();
$pending_projects = $pdo->query("SELECT COUNT(*) FROM Projects WHERE approval_status = 'pending'")->fetchColumn();
$active_projects = $pdo->query("SELECT COUNT(*) FROM Projects WHERE approval_status = 'approved'")->fetchColumn();
$rejected_projects = $pdo->query("SELECT COUNT(*) FROM Projects WHERE approval_status = 'rejected'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(admin_commission) FROM Payments")->fetchColumn() ?: 0;

// Fetch Tables
$projects = $pdo->query("SELECT p.*, u.name as owner_name FROM Projects p JOIN Users u ON p.owner_id = u.user_id WHERE p.approval_status = 'pending' ORDER BY p.project_id DESC")->fetchAll();
$services = $pdo->query("SELECT asr.*, p.project_name FROM After_Sale_Requests asr JOIN Projects p ON asr.project_id = p.project_id WHERE asr.status = 'requested'")->fetchAll();
$users_recent = $pdo->query("SELECT * FROM Users ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="space-y-6">
    <!-- Stats Row -->
    <?php renderSectionHeader("System Overview"); ?>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <?php renderStatsCard("Total Projects", $total_projects, "fas fa-folder", "brand"); ?>
        <?php renderStatsCard("Pending Projects", $pending_projects, "fas fa-clock", $pending_projects > 0 ? "orange" : "brand"); ?>
        <?php renderStatsCard("Total Users", $total_users, "fas fa-users", "brand"); ?>
        <?php renderStatsCard("Total Revenue", "$" . number_format($total_revenue, 2), "fas fa-dollar-sign", "green"); ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Projects -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Pending Approvals</h3>
                <span class="bg-brand-50 text-brand-700 py-1 px-3 rounded-full text-xs font-medium"><?= count($projects) ?> New</span>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (count($projects) > 0): ?>
                    <?php foreach ($projects as $p): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['project_name']) ?></h4>
                            <span class="text-xs text-gray-500"><?= htmlspecialchars($p['owner_name']) ?></span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?= htmlspecialchars($p['location']) ?> - $<?= number_format($p['total_project_cost']) ?></p>
                        <div class="flex space-x-3">
                            <form method="POST" action="admin/approve_project.php">
                                <input type="hidden" name="project_id" value="<?= $p['project_id'] ?>">
                                <button type="submit" name="action" value="approve" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                    <i class="fas fa-check mr-1.5"></i> Approve
                                </button>
                            </form>
                             <form method="POST" action="admin/approve_project.php">
                                <input type="hidden" name="project_id" value="<?= $p['project_id'] ?>">
                                <button type="submit" name="action" value="reject" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200">
                                    <i class="fas fa-times mr-1.5"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl text-gray-300 mb-3 block"></i>
                        All caught up! No pending projects.
                    </div>
                <?php endif; ?>
            </div>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                <a href="#" class="text-xs font-medium text-brand-600 hover:text-brand-800">View all history &rarr;</a>
            </div>
        </div>

        <!-- Pending Services -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
             <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Service Requests</h3>
                 <span class="bg-orange-50 text-orange-700 py-1 px-3 rounded-full text-xs font-medium"><?= count($services) ?> New</span>
            </div>
             <div class="divide-y divide-gray-100">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $s): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                             <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($s['project_name']) ?></h4>
                             <span class="text-xs text-gray-400"><?= date('M d', strtotime($s['request_date'])) ?></span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($s['service_description']) ?></p>
                         <form method="POST" action="admin/approve_service.php">
                            <input type="hidden" name="service_id" value="<?= $s['service_id'] ?>">
                            <button type="submit" name="action" value="approve" class="w-full inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-brand-600 hover:bg-brand-700">
                                Approve Request
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                   <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-tools text-4xl text-gray-300 mb-3 block"></i>
                        No new service requests.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Recent Users</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users_recent as $u): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($u['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase text-xs"><?= htmlspecialchars($u['role']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
