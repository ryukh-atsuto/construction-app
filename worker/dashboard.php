<?php
// Worker Dashboard
if (!defined('APP_RUNNING')) {
    header("Location: ../dashboard.php");
    exit;
}

$worker_id = $_SESSION['user_id'];

// Check POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assignment_id'])) {
    $assignment_id = $_POST['assignment_id'];
    try {
        $stmt = $pdo->prepare("UPDATE Worker_Assignments SET status = 'completed' WHERE assignment_id = ? AND worker_id = ?");
        $stmt->execute([$assignment_id, $worker_id]);
        $success = "Task marked as completed!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Tasks
$stmt = $pdo->prepare("SELECT wa.*, p.project_name FROM Worker_Assignments wa JOIN Projects p ON wa.project_id = p.project_id WHERE wa.worker_id = ? ORDER BY wa.status ASC, wa.end_date ASC");
$stmt->execute([$worker_id]);
$tasks = $stmt->fetchAll();

// Fetch After Sale Tasks
$stmt = $pdo->prepare("SELECT asa.*, p.project_name, asr.service_description FROM After_Sale_Assignments asa JOIN After_Sale_Requests asr ON asa.service_id = asr.service_id JOIN Finished_Projects fp ON asr.project_id = fp.project_id JOIN Projects p ON fp.project_id = p.project_id WHERE asa.worker_id = ?");
$stmt->execute([$worker_id]);
$service_tasks = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto space-y-6">

    <?php if (isset($success)): ?>
        <div
            class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
            <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <!-- Main Tasks -->
    <div>
        <?php renderSectionHeader("My Tasks"); ?>
        <?php if (count($tasks) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($tasks as $task): ?>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-transform hover:-translate-y-1 duration-200">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-12 w-12 rounded-full bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                    <i class="fas fa-hammer"></i>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($task['project_name']) ?></h3>
                                    <?php renderStatusBadge($task['status']); ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 font-medium mb-1">
                                    <?= htmlspecialchars($task['task_description']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <i class="far fa-clock mr-1"></i> Due: <?= date('M d, Y', strtotime($task['end_date'])) ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($task['status'] !== 'completed'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="assignment_id" value="<?= $task['assignment_id'] ?>">
                                <button type="submit"
                                    class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-check mr-2"></i> Mark Done
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="text-green-600 font-medium flex items-center">
                                <i class="fas fa-check-double mr-2"></i> Done
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-12 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500">
                <i class="fas fa-mug-hot text-4xl mb-3"></i>
                <p>No active tasks. Enjoy your break!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Service Tasks -->
    <?php if (count($service_tasks) > 0): ?>
        <div class="mt-8">
            <?php renderSectionHeader("After-Sale Service Jobs", "", "", "orange"); ?>
            <div class="space-y-4">
                <?php foreach ($service_tasks as $st): ?>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-l-4 border-orange-400 dark:border-gray-700 dark:border-l-orange-400 p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($st['project_name']) ?>
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <?= htmlspecialchars($st['service_description']) ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    <i class="fas fa-calendar mr-1"></i> Assigned:
                                    <?= date('M d', strtotime($st['assigned_date'])) ?>
                                </p>
                            </div>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">
                                Service
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>