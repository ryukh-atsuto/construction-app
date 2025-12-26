<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    exit('Access Denied');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id']) && isset($_POST['amount'])) {
    $project_id = $_POST['project_id'];
    $amount = $_POST['amount'];
    
    // Commission Rate (e.g., 5%)
    $commission_rate = 0.05;
    $commission = $amount * $commission_rate;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO Payments (project_id, paid_by, total_amount, admin_commission, payment_date, status) VALUES (?, ?, ?, ?, NOW(), 'completed')");
        $stmt->execute([$project_id, $_SESSION['user_id'], $amount, $commission]);

        $pdo->commit();
        header("Location: view_project.php?id=$project_id&success=Payment processed successfully!");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
