<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consultation_id']) && isset($_POST['action'])) {
    $consultation_id = $_POST['consultation_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE Engineer_Consultations SET status = 'approved', approved_by = ? WHERE consultation_id = ?");
            $stmt->execute([$_SESSION['user_id'], $consultation_id]);

            header("Location: ../dashboard.php?success=Consultation Approved");
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
