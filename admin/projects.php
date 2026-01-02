<?php
session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Manage Projects";

// Handle Actions (Approve/Reject/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['project_id'])) {
        $pid = $_POST['project_id'];
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE Projects SET approval_status = 'approved' WHERE project_id = ?");
            $stmt->execute([$pid]);
            $msg = "Project approved successfully.";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE Projects SET approval_status = 'rejected' WHERE project_id = ?");
            $stmt->execute([$pid]);
            $msg = "Project rejected.";
        }
    }
}

// Fetch Projects
$stmt = $pdo->query("SELECT p.*, u.name as owner_name FROM Projects p JOIN Users u ON p.owner_id = u.user_id ORDER BY p.created_at DESC");
$projects = $stmt->fetchAll();

include '../includes/layout/header.php';
include '../includes/layout/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include '../includes/layout/topbar.php'; ?>
    
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">All Projects</h1>
            <div class="flex space-x-2">
                <span class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-filter mr-2"></i> Filter
                </span>
                <span class="bg-brand-600 text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm cursor-pointer hover:bg-brand-700">
                    <i class="fas fa-download mr-2"></i> Export
                </span>
            </div>
        </div>

        <?php if(isset($msg)): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Budget</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Timeline</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $p): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-brand-100 dark:bg-gray-700 text-brand-600 dark:text-brand-400 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($p['project_name']) ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($p['location']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= htmlspecialchars($p['owner_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                ];
                                $cls = $status_colors[$p['approval_status']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $cls ?>">
                                    <?= ucfirst($p['approval_status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 font-medium">
                                $<?= number_format($p['total_project_cost']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= date('M Y', strtotime($p['start_date'])) ?> - <?= date('M Y', strtotime($p['end_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="project_id" value="<?= $p['project_id'] ?>">
                                    <?php if($p['approval_status'] === 'pending'): ?>
                                    <button type="submit" name="action" value="approve" class="text-green-600 hover:text-green-900 dark:hover:text-green-400 mx-1" title="Approve"><i class="fas fa-check"></i></button>
                                    <button type="submit" name="action" value="reject" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 mx-1" title="Reject"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                    <a href="#" class="text-brand-600 dark:text-brand-400 hover:text-brand-900 dark:hover:text-brand-300 mx-1"><i class="fas fa-eye"></i></a>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No projects found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/layout/footer.php'; ?>
