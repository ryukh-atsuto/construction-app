<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_name = trim($_POST['project_name']);
    $location = trim($_POST['location']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_cost = $_POST['total_cost'];
    $construction_phase = trim($_POST['construction_phase']);

    if (empty($project_name) || empty($location) || empty($start_date) || empty($total_cost)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert into Projects table
            $stmt = $pdo->prepare("INSERT INTO Projects (owner_id, project_name, location, start_date, end_date, total_project_cost, approval_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $project_name, $location, $start_date, $end_date, $total_cost]);
            $project_id = $pdo->lastInsertId();

            // Insert into Under_Construction_Projects (ISA relationship)
            $stmt = $pdo->prepare("INSERT INTO Under_Construction_Projects (project_id, construction_phase, expected_completion_date) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $construction_phase, $end_date]);

            $pdo->commit();
            $success = "Project created successfully! Waiting for Admin approval.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error creating project: " . $e->getMessage();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Create New Project</h2>
        <a href="../dashboard.php" class="text-indigo-600 hover:text-indigo-800 text-sm">Back to Dashboard</a>
    </div>
    
    <?php if($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <?= $success ?> <a href="../dashboard.php" class="font-bold underline">View Projects</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Project Name</label>
                <input type="text" name="project_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                <input type="text" name="location" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                <input type="date" name="start_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Expected End Date</label>
                <input type="date" name="end_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Estimated Total Cost</label>
                <input type="number" step="0.01" name="total_cost" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
             <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Initial Phase</label>
                <input type="text" name="construction_phase" value="Planning" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                Create Project
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
