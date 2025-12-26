<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id']) && isset($_POST['action'])) {
    $service_id = $_POST['service_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE After_Sale_Requests SET status = 'approved', approved_by = ? WHERE service_id = ?");
            $stmt->execute([$_SESSION['user_id'], $service_id]);

            header("Location: ../dashboard.php?success=Service Request Approved");
            exit;
        } catch (Exception $e) {
            echo "Error processing request: " . $e->getMessage();
        }
    }
} else {
    header("Location: ../dashboard.php");
    exit;
}
?>
