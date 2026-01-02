<?php
// Engineer Dashboard
if (!defined('APP_RUNNING')) {
    header("Location: ../dashboard.php");
    exit;
}

$eng_id = $_SESSION['user_id'];

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consultation_id'])) {
    $consultation_id = $_POST['consultation_id'];
    try {
        $stmt = $pdo->prepare("UPDATE Engineer_Consultations SET status = 'completed' WHERE consultation_id = ? AND engineer_id = ?");
        $stmt->execute([$consultation_id, $eng_id]);
        $success = "Consultation marked as completed.";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Consultations
$stmt = $pdo->prepare("SELECT ec.*, p.project_name, p.location FROM Engineer_Consultations ec JOIN Projects p ON ec.project_id = p.project_id WHERE ec.engineer_id = ? ORDER BY ec.status ASC, ec.consultation_date ASC");
$stmt->execute([$eng_id]);
$consultations = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto space-y-6">

    <?php if (isset($success)): ?>
        <div
            class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
            <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <?php renderSectionHeader("Scheduled Consultations"); ?>

    <?php if (count($consultations) > 0): ?>
        <div class="space-y-4">
            <?php foreach ($consultations as $consult): ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col md:flex-row justify-between gap-6 hover:shadow-md transition-shadow">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="h-12 w-12 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                                <?= date('d', strtotime($consult['consultation_date'])) ?>
                            </div>
                            <div class="text-center text-xs text-indigo-600 dark:text-indigo-400 uppercase font-bold mt-1">
                                <?= date('M', strtotime($consult['consultation_date'])) ?>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg">
                                    <?= htmlspecialchars($consult['project_name']) ?></h3>
                                <?php renderStatusBadge($consult['status']); ?>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400"><i
                                    class="fas fa-map-pin mr-1 text-gray-400 dark:text-gray-500"></i>
                                <?= htmlspecialchars($consult['location']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <?php if ($consult['status'] !== 'completed'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="consultation_id" value="<?= $consult['consultation_id'] ?>">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-clipboard-check mr-2 text-green-500"></i> Mark Complete
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Report Submitted <i class="fas fa-check ml-1"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div
            class="bg-white dark:bg-gray-800 rounded-lg p-12 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500">
            <i class="fas fa-calendar-times text-4xl mb-3"></i>
            <p>No scheduled consultations.</p>
        </div>
    <?php endif; ?>

</div>