<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    exit('Access Denied');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];

    try {
        $pdo->beginTransaction();

        // 1. Remove from Under_Construction_Projects
        $stmt = $pdo->prepare("DELETE FROM Under_Construction_Projects WHERE project_id = ?");
        $stmt->execute([$project_id]);

        // 2. Insert into Finished_Projects
        $handover_date = date('Y-m-d');
        $warranty_expiry = date('Y-m-d', strtotime('+1 year')); // 1 year warranty
        
        $stmt = $pdo->prepare("INSERT INTO Finished_Projects (project_id, handover_date, warranty_expiry) VALUES (?, ?, ?)");
        $stmt->execute([$project_id, $handover_date, $warranty_expiry]);

        $pdo->commit();
        header("Location: ../manager/view_project.php?id=$project_id&success=Project marked as Finished");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
