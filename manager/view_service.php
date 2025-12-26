<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    exit('Access Denied');
}

if (!isset($_GET['id'])) {
    exit('Invalid ID');
}

$service_id = $_GET['id'];
$message = '';

// Handle Worker Assignment for Service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_worker'])) {
    $worker_id = $_POST['worker_id'];
    $start_date = date('Y-m-d'); // Assume starts today
    
    try {
        $stmt = $pdo->prepare("INSERT INTO After_Sale_Assignments (service_id, worker_id, assigned_by, assignment_date, status) VALUES (?, ?, ?, ?, 'assigned')");
        $stmt->execute([$service_id, $worker_id, $_SESSION['user_id'], $start_date]);
        
        // Update Request Status
        $stmt = $pdo->prepare("UPDATE After_Sale_Requests SET status = 'completed' WHERE service_id = ?"); // Assuming assignment means handling it? Or maybe keep it approved until done?
        // Requirement: "Track status for all after-sale assignments."
        // Let's keep Request 'approved' or maybe 'processing', and Assignment 'assigned'. 
        // The Prompt says: "Track status for all after-sale assignments."
        // I'll keep the Request as 'approved' for now or maybe 'completed' if assignment closes the loop for manager.
        // Let's say "Processing". But ENUM is requested, approved, rejected, completed.
        // So let's mark it as completed here assuming handover? No, status is for the request.
        // Let's leave it as approved, and update only when worker finishes? 
        // Logic: Manager assigns worker -> Worker does job -> ...
        // For simplicity, let's just create the assignment.
        
        $message = "Worker assigned to service request successfully!";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch Service Details
$stmt = $pdo->prepare("SELECT asr.*, p.project_name FROM After_Sale_Requests asr JOIN Finished_Projects fp ON asr.project_id = fp.project_id JOIN Projects p ON fp.project_id = p.project_id WHERE asr.service_id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

// Fetch Workers
$stmt = $pdo->query("SELECT * FROM Users u JOIN Worker_Details w ON u.user_id = w.worker_id WHERE w.availability_status = 'available'");
$workers = $stmt->fetchAll();

?>
<?php include '../includes/header.php'; ?>

<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Service Request Details</h2>
     <?php if($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= $message ?></div>
    <?php endif; ?>

    <p><strong>Project:</strong> <?= htmlspecialchars($service['project_name']) ?></p>
    <p><strong>Issue:</strong> <?= htmlspecialchars($service['service_description']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($service['request_date']) ?></p>

    <div class="mt-6 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Assign Worker</h3>
        <form method="POST" action="">
             <select name="worker_id" required class="block w-full border-gray-300 rounded-md shadow-sm p-2 border mb-4">
                <option value="">Choose an available worker...</option>
                <?php foreach ($workers as $worker): ?>
                    <option value="<?= $worker['user_id'] ?>"><?= htmlspecialchars($worker['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="assign_worker" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Assign Worker</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
