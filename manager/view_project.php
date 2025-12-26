<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$project_id = $_GET['id'];
$message = '';

// Handle Worker Assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_worker'])) {
    $worker_id = $_POST['worker_id'];
    $task = $_POST['task_description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Worker_Assignments (project_id, worker_id, assigned_by, task_description, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $worker_id, $_SESSION['user_id'], $task, $start_date, $end_date]);
        
        // Update worker status to busy
        $stmt = $pdo->prepare("UPDATE Worker_Details SET availability_status = 'busy' WHERE worker_id = ?");
        $stmt->execute([$worker_id]);

        $message = "Worker assigned successfully!";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch Project Details
try {
    $stmt = $pdo->prepare("SELECT * FROM Projects WHERE project_id = ? AND managed_by = ?");
    $stmt->execute([$project_id, $_SESSION['user_id']]);
    $project = $stmt->fetch();

    if (!$project) {
        die("Project not found or access denied.");
    }

    // Fetch Available Workers
    $stmt = $pdo->query("SELECT u.user_id, u.name, w.skillset FROM Users u JOIN Worker_Details w ON u.user_id = w.worker_id WHERE w.availability_status = 'available'");
    $workers = $stmt->fetchAll();

    // Fetch Current Assignments
    $stmt = $pdo->prepare("SELECT wa.*, u.name as worker_name FROM Worker_Assignments wa JOIN Users u ON wa.worker_id = u.user_id WHERE wa.project_id = ?");
    $stmt->execute([$project_id]);
    $assignments = $stmt->fetchAll();

    // Fetch Approved Consultations needing coordination
    $stmt = $pdo->prepare("SELECT * FROM Engineer_Consultations WHERE project_id = ? AND status = 'approved'");
    $stmt->execute([$project_id]);
    $consultations = $stmt->fetchAll();

    // Fetch Engineers
    $stmt = $pdo->query("SELECT u.user_id, u.name, e.specialization FROM Users u JOIN Engineer_Details e ON u.user_id = e.engineer_id");
    $engineers = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Handle Engineer Coordination
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_engineer'])) {
    $consultation_id = $_POST['consultation_id'];
    $engineer_id = $_POST['engineer_id'];
    $date = $_POST['consultation_date'];

    try {
        // Keep status as 'approved' but set engineer and date. 
        // Presence of engineer_id implies it's scheduled.
        $stmt = $pdo->prepare("UPDATE Engineer_Consultations SET engineer_id = ?, coordinated_by = ?, consultation_date = ? WHERE consultation_id = ?");
        $stmt->execute([$engineer_id, $_SESSION['user_id'], $date, $consultation_id]);
        $message = "Engineer assigned and consultation scheduled!";
        
        // Refresh page
        header("Refresh:0"); 
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manage Project: <?= htmlspecialchars($project['project_name']) ?></h2>
        <a href="../dashboard.php" class="text-indigo-600 hover:text-indigo-800">Back to Dashboard</a>
    </div>

    <?php if($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Assign Worker Form -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Assign Worker</h3>
            <form method="POST" action="" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Worker</label>
                    <select name="worker_id" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white p-2 border">
                        <option value="">Choose an available worker...</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['user_id'] ?>"><?= htmlspecialchars($worker['name']) ?> (<?= htmlspecialchars($worker['skillset']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Description</label>
                    <textarea name="task_description" required rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2 border">
                    </div>
                </div>
                <button type="submit" name="assign_worker" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">Assign to Project</button>
            </form>
        </div>

        <!-- Current Assignments List -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Current Assignments</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
                <ul class="divide-y divide-gray-200">
                    <?php if (count($assignments) > 0): ?>
                        <?php foreach ($assignments as $assignment): ?>
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="truncate">
                                    <p class="text-sm font-medium text-indigo-600 truncate"><?= htmlspecialchars($assignment['worker_name']) ?></p>
                                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($assignment['task_description']) ?></p>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= ucfirst($assignment['status']) ?>
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">Due: <?= $assignment['end_date'] ?></p>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="px-4 py-4 text-sm text-gray-500 text-center">No active assignments.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Finish Project Action -->
    <div class="mt-6 border-t border-gray-200 pt-6">
        <form method="POST" action="../manager/finish_project.php" onsubmit="return confirm('Are you sure you want to mark this project as Finished? This is irreversible.');">
            <input type="hidden" name="project_id" value="<?= $project['project_id'] ?>">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                Mark Project as Finished
            </button>
        </form>
    </div>
    
    <!-- Engineer Coordination Section -->
    <?php if(count($consultations) > 0): ?>
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Pending Engineer Consultations</h3>
        <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($consultations as $consultation): ?>
                <li class="px-4 py-4 sm:px-6">
                    <form method="POST" action="" class="flex items-center justify-between gap-4">
                        <input type="hidden" name="consultation_id" value="<?= $consultation['consultation_id'] ?>">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Request ID: <?= $consultation['consultation_id'] ?></p>
                            <p class="text-xs text-gray-500">Status: <?= ucfirst($consultation['status']) ?></p>
                        </div>
                        <div class="flex-1">
                            <select name="engineer_id" required class="block w-full border-gray-300 rounded-md shadow-sm text-sm p-1 border">
                                <option value="">Select Engineer...</option>
                                <?php foreach ($engineers as $engineer): ?>
                                    <option value="<?= $engineer['user_id'] ?>"><?= htmlspecialchars($engineer['name']) ?> (<?= htmlspecialchars($engineer['specialization']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                             <input type="datetime-local" name="consultation_date" required class="block w-full border-gray-300 rounded-md shadow-sm text-sm p-1 border">
                        </div>
                        <button type="submit" name="assign_engineer" class="bg-purple-600 text-white px-3 py-2 rounded-md hover:bg-purple-700 text-sm">Assign</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
