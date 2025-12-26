<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];
    $action = $_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        try {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            $stmt = $pdo->prepare("UPDATE Projects SET approval_status = ?, approved_by = ? WHERE project_id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $project_id]);

            header("Location: ../dashboard.php?success=Project " . ucfirst($status));
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
