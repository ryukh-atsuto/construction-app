<?php
session_start();
define('APP_RUNNING', true); // Prevent direct access to includes
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$role = $_SESSION['role'];

// Include header
require_once 'includes/header.php';
require_once 'includes/ui_components.php';

// Route to role-specific dashboard
switch ($role) {
    case 'admin':
        include 'admin/dashboard.php';
        break;
    case 'owner':
        include 'owner/dashboard.php';
        break;
    case 'manager':
        include 'manager/dashboard.php';
        break;
    case 'engineer':
        include 'engineer/dashboard.php';
        break;
    case 'worker':
        include 'worker/dashboard.php';
        break;
    default:
        echo "<div class='container mx-auto mt-10 p-6 bg-red-100 text-red-700 rounded'>Error: Unknown Role</div>";
        break;
}

// Include footer
require_once 'includes/footer.php';
?>
