<?php
// Owner Dashboard
if (!defined('APP_RUNNING')) {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch Projects
$stmt = $pdo->prepare("SELECT p.*, u.name as manager_name FROM Projects p LEFT JOIN Users u ON p.managed_by = u.user_id WHERE p.owner_id = ? ORDER BY p.project_id DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Fetch Payments
$pay_stmt = $pdo->prepare("SELECT py.*, p.project_name FROM Payments py JOIN Projects p ON py.project_id = p.project_id WHERE py.paid_by = ? ORDER BY py.payment_id DESC LIMIT 5");
$pay_stmt->execute([$_SESSION['user_id']]);
$recent_payments = $pay_stmt->fetchAll();
?>

<div class="space-y-8">

    <!-- Projects Section -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">My Projects</h2>
            <div class="space-x-2">
                <a href="owner/create_project.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand-600 hover:bg-brand-700">
                    <i class="fas fa-plus mr-2"></i> New Project
                </a>
            </div>
        </div>

        <?php if (count($projects) > 0): ?>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($projects as $project): ?>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 flex flex-col h-full group">
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div
                                    class="rounded-full bg-brand-50 dark:bg-brand-900/30 p-3 border border-brand-100 dark:border-brand-500/20">
                                    <i class="fas fa-building text-brand-600 dark:text-brand-400 text-xl"></i>
                                </div>
                                <?php renderStatusBadge($project['approval_status']); ?>
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                <?= htmlspecialchars($project['project_name']) ?></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"><i class="fas fa-map-marker-alt mr-1"></i>
                                <?= htmlspecialchars($project['location']) ?></p>

                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span>Budget:</span>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white">$<?= number_format($project['total_project_cost']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Timeline:</span>
                                    <span><?= date('M Y', strtotime($project['start_date'])) ?> -
                                        <?= date('M Y', strtotime($project['end_date'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-100 dark:border-gray-700 rounded-b-lg">
                            <a href="owner/view_project.php?id=<?= $project['project_id'] ?>"
                                class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 flex items-center justify-center">
                                View Details <i
                                    class="fas fa-arrow-right ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div
                class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                <i class="fas fa-folder-open text-gray-400 dark:text-gray-500 text-4xl mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400">You don't have any projects yet.</p>
                <a href="owner/create_project.php"
                    class="text-brand-600 dark:text-brand-400 font-medium hover:underline mt-2 inline-block">Start your
                    first project</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Payments -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 md:flex md:items-center md:justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Transactions</h3>
            <a href="#"
                class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">View
                All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Project</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Amount</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (count($recent_payments) > 0): ?>
                        <?php foreach ($recent_payments as $pay): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($pay['project_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= date('M d, Y', strtotime($pay['payment_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                    $<?= number_format($pay['total_amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No recent
                                payments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>