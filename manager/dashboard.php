<?php
// Manager Dashboard
if (!defined('APP_RUNNING')) {
    header("Location: ../dashboard.php");
    exit;
}

$manager_id = $_SESSION['user_id'];

// Fetch Stats
$project_count = $pdo->prepare("SELECT COUNT(*) FROM Projects WHERE managed_by = ?");
$project_count->execute([$manager_id]);
$total_managed = $project_count->fetchColumn();

$assignment_count = $pdo->prepare("SELECT COUNT(*) FROM Worker_Assignments WHERE assigned_by = ? AND status = 'assigned'");
$assignment_count->execute([$manager_id]);
$total_active_tasks = $assignment_count->fetchColumn();

// Fetch Managed Projects
$stmt = $pdo->prepare("SELECT * FROM Projects WHERE managed_by = ? ORDER BY project_id DESC");
$stmt->execute([$manager_id]);
$projects = $stmt->fetchAll();

// Service Requests
$stmt = $pdo->prepare("SELECT asr.*, p.project_name FROM After_Sale_Requests asr JOIN Finished_Projects fp ON asr.project_id = fp.project_id JOIN Projects p ON fp.project_id = p.project_id WHERE p.managed_by = ? AND asr.status = 'approved'");
$stmt->execute([$manager_id]);
$services = $stmt->fetchAll();

// Team Management Data
// 1. Available Workers
$stmt = $pdo->query("SELECT u.name, wd.skillset, wd.hourly_rate, wd.rating FROM Users u JOIN Worker_Details wd ON u.user_id = wd.worker_id WHERE wd.availability_status = 'available' ORDER BY wd.rating DESC LIMIT 5");
$available_workers = $stmt->fetchAll();

// 2. Top Performers
$stmt = $pdo->query("SELECT u.name, wd.skillset, wd.rating FROM Users u JOIN Worker_Details wd ON u.user_id = wd.worker_id ORDER BY wd.rating DESC LIMIT 5");
$top_performers = $stmt->fetchAll();

// 3. Average Team Rating
$stmt = $pdo->query("SELECT AVG(rating) FROM Worker_Details");
$avg_rating = $stmt->fetchColumn();
$team_performance = $avg_rating ? number_format($avg_rating * 20, 0) . "%" : "0%";
?>

<div class="space-y-6">

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <?php renderStatsCard("Projects Managed", $total_managed, "fas fa-briefcase", "brand"); ?>
        <?php renderStatsCard("Active Tasks", $total_active_tasks, "fas fa-user-clock", "orange"); ?>
        <?php renderStatsCard("Pending Services", count($services), "fas fa-wrench", count($services) > 0 ? "red" : "green"); ?>
        <?php renderStatsCard("Team Performance", $team_performance, "fas fa-chart-line", "green"); ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Projects) -->
        <div class="lg:col-span-2 space-y-6">
            <?php renderSectionHeader("Active Projects"); ?>

            <?php if (count($projects) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($projects as $project): ?>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="rounded-lg bg-brand-50 dark:bg-brand-900/30 p-3 hidden md:block">
                                    <i class="fas fa-building text-brand-600 dark:text-brand-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($project['project_name']) ?></h3>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($project['location']) ?>
                                        <span class="mx-2">•</span>
                                        $<?= number_format($project['total_project_cost']) ?>
                                    </div>
                                    <?php renderStatusBadge($project['approval_status']); ?>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="manager/view_project.php?id=<?= $project['project_id'] ?>"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <i class="fas fa-eye mr-2"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center text-gray-500 dark:text-gray-400">
                    <p>No projects currently assigned.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar (Service Requests) -->
        <div class="space-y-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="font-bold text-gray-900 dark:text-white">Service Requests</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (count($services) > 0): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">
                                        <?= htmlspecialchars($service['project_name']) ?></h4>
                                    <span
                                        class="text-xs text-gray-400 dark:text-gray-500"><?= date('M d', strtotime($service['request_date'])) ?></span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
                                    <?= htmlspecialchars($service['service_description']) ?></p>
                                <a href="manager/view_service.php?id=<?= $service['service_id'] ?>"
                                    class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300">
                                    Assign Worker &rarr;
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No pending service requests.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Team Overview -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white">Team Insights</h3>
                    <span
                        class="text-xs font-medium px-2 py-1 bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 rounded-full">Live
                        Stats</span>
                </div>

                <div class="p-4 space-y-6">
                    <!-- Available Workers -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">
                            Available for Hire</h4>
                        <div class="space-y-3">
                            <?php foreach ($available_workers as $worker): ?>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-green-600 dark:text-green-400 text-xs font-bold">
                                            <?= substr($worker['name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                                <?= htmlspecialchars($worker['name']) ?></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($worker['skillset']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                                            $<?= $worker['hourly_rate'] ?>/hr</p>
                                        <div class="flex items-center text-amber-500 text-[10px]">
                                            <i class="fas fa-star mr-1"></i>
                                            <span><?= $worker['rating'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Performance Rankings -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Top
                            Performers</h4>
                        <div class="space-y-3">
                            <?php foreach ($top_performers as $i => $worker): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="text-sm font-bold <?= $i < 3 ? 'text-amber-500' : 'text-gray-400 dark:text-gray-500' ?>">#<?= $i + 1 ?></span>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                                <?= htmlspecialchars($worker['name']) ?></p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($worker['skillset']) ?></p>
                                        </div>
                                    </div>
                                    <div
                                        class="px-2 py-1 bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-xs font-bold">
                                        <?= $worker['rating'] ?> ★
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>