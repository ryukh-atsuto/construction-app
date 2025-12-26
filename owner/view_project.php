<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$project_id = $_GET['id'];
$message = '';

// Handle Manager Assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_manager'])) {
    $manager_id = $_POST['manager_id'];
    try {
        $stmt = $pdo->prepare("UPDATE Projects SET managed_by = ? WHERE project_id = ? AND owner_id = ?");
        $stmt->execute([$manager_id, $project_id, $_SESSION['user_id']]);
        $message = "Manager assigned successfully!";
    } catch (Exception $e) {
        $message = "Error assigning manager: " . $e->getMessage();
    }
}

// Handle Consultation Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_consultation'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Engineer_Consultations (project_id, requested_by, status) VALUES (?, ?, 'requested')");
        $stmt->execute([$project_id, $_SESSION['user_id']]);
        $message = "Consultation requested successfully! Waiting for Admin approval.";
    } catch (Exception $e) {
        $message = "Error requesting consultation: " . $e->getMessage();
    }
}

// Handle Service Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_service'])) {
    $desc = $_POST['service_description'];
    try {
        $stmt = $pdo->prepare("INSERT INTO After_Sale_Requests (project_id, requested_by, service_description, request_date, status) VALUES (?, ?, ?, NOW(), 'requested')");
        $stmt->execute([$project_id, $_SESSION['user_id'], $desc]);
        $message = "After-sale service requested successfully!";
    } catch (Exception $e) {
        $message = "Error requesting service: " . $e->getMessage();
    }
}

// Fetch Project Details
try {
    $stmt = $pdo->prepare("SELECT p.*, u.name as manager_name FROM Projects p LEFT JOIN Users u ON p.managed_by = u.user_id WHERE p.project_id = ? AND p.owner_id = ?");
    $stmt->execute([$project_id, $_SESSION['user_id']]);
    $project = $stmt->fetch();

    if (!$project) {
        die("Project not found or access denied.");
    }

    // Fetch Available Managers
    $stmt = $pdo->query("SELECT u.user_id, u.name FROM Users u JOIN Manager_Details m ON u.user_id = m.manager_id");
    $managers = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<?php include '../includes/header.php'; ?>

<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Project Details</h2>
        <a href="dashboard.php" class="text-indigo-600 hover:text-indigo-800">Back to Dashboard</a>
    </div>

    <?php if($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Project Information</h3>
            <div class="mt-4 space-y-2">
                <p><span class="font-bold">Name:</span> <?= htmlspecialchars($project['project_name']) ?></p>
                <p><span class="font-bold">Location:</span> <?= htmlspecialchars($project['location']) ?></p>
                <p><span class="font-bold">Start Date:</span> <?= htmlspecialchars($project['start_date']) ?></p>
                <p><span class="font-bold">End Date:</span> <?= htmlspecialchars($project['end_date']) ?></p>
                <p><span class="font-bold">Cost:</span> $<?= number_format($project['total_project_cost'], 2) ?></p>
                <p><span class="font-bold">Status:</span> 
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?= $project['approval_status'] == 'approved' ? 'bg-green-100 text-green-800' : ($project['approval_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                        <?= ucfirst($project['approval_status']) ?>
                    </span>
                </p>
            </div>

            <!-- Payment Section -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Project Payments</h3>
                 <?php 
                // Check existing payments
                $pay_stmt = $pdo->prepare("SELECT SUM(total_amount) as paid FROM Payments WHERE project_id = ? AND status = 'completed'");
                $pay_stmt->execute([$project_id]);
                $paid = $pay_stmt->fetchColumn() ?: 0;
                $remaining = $project['total_project_cost'] - $paid;
                ?>
                <div class="mb-4">
                    <p>Total Cost: $<span class="font-semibold"><?= number_format($project['total_project_cost'], 2) ?></span></p>
                    <p>Paid: $<span class="font-semibold text-green-600"><?= number_format($paid, 2) ?></span></p>
                    <p>Remaining: $<span class="font-semibold text-red-600"><?= number_format($remaining, 2) ?></span></p>
                </div>

                <?php if ($remaining > 0): ?>
                <form method="POST" action="process_payment.php" class="flex gap-4 items-end">
                    <input type="hidden" name="project_id" value="<?= $project_id ?>">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Amount to Pay</label>
                        <input type="number" step="0.01" name="amount" max="<?= $remaining ?>" value="<?= $remaining ?>" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Make Payment</button>
                </form>
                <?php else: ?>
                    <p class="text-green-600 font-bold">Project is fully paid!</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-700">Management</h3>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="mb-2"><span class="font-bold">Assigned Manager:</span> 
                    <?= $project['manager_name'] ? htmlspecialchars($project['manager_name']) : '<span class="text-red-500">None</span>' ?>
                </p>

                <?php if ($project['approval_status'] === 'approved'): ?>
                    <form method="POST" action="" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Assign/Change Manager</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <select name="manager_id" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300">
                                <option value="">Select a Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['user_id'] ?>" <?= $project['managed_by'] == $manager['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($manager['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_manager" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Assign
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-sm text-gray-500 mt-2">Manager assignment is locked until project is approved.</p>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-bold mb-2">Engineer Consultation</h4>
                <form method="POST" action="">
                     <button type="submit" name="request_consultation" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">Request Consultation</button>
                </form>
            </div>
            
            <?php 
            // Check if project is finished
            $is_finished = $pdo->prepare("SELECT * FROM Finished_Projects WHERE project_id = ?");
            $is_finished->execute([$project_id]);
            if ($is_finished->fetch()): 
            ?>
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="font-bold mb-2 text-yellow-800">After-Sale Service</h4>
                <form method="POST" action="">
                    <textarea name="service_description" placeholder="Describe the issue..." required class="w-full border rounded p-2 mb-2"></textarea>
                    <button type="submit" name="request_service" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">Request Service</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
