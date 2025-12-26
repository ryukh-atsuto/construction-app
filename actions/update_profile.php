<?php
// actions/update_profile.php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profile.php");
    exit;
}

$user_id = $_POST['user_id'];
$role = $_POST['role'];

// Verify session integrity
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    die("Unauthorized access.");
}

try {
    $pdo->beginTransaction();

    // 1. Handle Profile Picture Upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
        $dest_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest_path)) {
            $profile_picture = $file_name;
        }
    }

    // 2. Update Primary Users Table
    $user_fields = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'age' => $_POST['age'],
        'gender' => $_POST['gender'],
        'national_id' => $_POST['national_id'],
        'address' => $_POST['address']
    ];

    $sql = "UPDATE Users SET name = :name, phone = :phone, age = :age, gender = :gender, national_id = :national_id, address = :address";
    if ($profile_picture) {
        $sql .= ", profile_picture = :profile_picture";
        $user_fields['profile_picture'] = $profile_picture;
    }
    $sql .= " WHERE user_id = :user_id";
    $user_fields['user_id'] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($user_fields);

    // 3. Handle Role-Specific Document
    $doc_path = null;
    if (isset($_FILES['doc_upload']) && $_FILES['doc_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/documents/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['doc_upload']['name'], PATHINFO_EXTENSION);
        $file_name = "doc_" . $user_id . "_" . time() . "." . $file_ext;
        $dest_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['doc_upload']['tmp_name'], $dest_path)) {
            $doc_path = $file_name;
        }
    }

    // 4. Update Role-Specific Details Table
    $detail_table = "";
    $pk = "";
    $update_fields = [];
    $detail_sql = "";

    switch ($role) {
        case 'owner':
            $detail_table = "Owner_Details"; $pk = "owner_id";
            $detail_sql = "UPDATE Owner_Details SET owner_type = :owner_type, company_name = :company_name, business_reg_number = :business_reg_number, investment_range = :investment_range, past_experience = :past_experience";
            $update_fields = [
                'owner_type' => $_POST['owner_type'],
                'company_name' => $_POST['company_name'],
                'business_reg_number' => $_POST['business_reg_number'],
                'investment_range' => $_POST['investment_range'],
                'past_experience' => $_POST['past_experience']
            ];
            if ($doc_path) { $detail_sql .= ", verification_doc_path = :doc"; $update_fields['doc'] = $doc_path; }
            break;

        case 'manager':
            $detail_table = "Manager_Details"; $pk = "manager_id";
            $detail_sql = "UPDATE Manager_Details SET assigned_region = :assigned_region, experience_years = :experience_years, work_shift = :work_shift";
            $update_fields = [
                'assigned_region' => $_POST['assigned_region'],
                'experience_years' => $_POST['experience_years'],
                'work_shift' => $_POST['work_shift']
            ];
            if ($doc_path) { $detail_sql .= ", leadership_cert_path = :doc"; $update_fields['doc'] = $doc_path; }
            break;

        case 'engineer':
            $detail_table = "Engineer_Details"; $pk = "engineer_id";
            $detail_sql = "UPDATE Engineer_Details SET specialization = :specialization, license_number = :license_number, years_experience = :years_experience, consultation_fee = :consultation_fee";
            $update_fields = [
                'specialization' => $_POST['specialization'],
                'license_number' => $_POST['license_number'],
                'years_experience' => $_POST['years_experience'],
                'consultation_fee' => $_POST['consultation_fee']
            ];
            // JSON fields would go here if implemented more deeply
            break;

        case 'worker':
            $detail_table = "Worker_Details"; $pk = "worker_id";
            $detail_sql = "UPDATE Worker_Details SET skillset = :skillset, secondary_skills = :secondary_skills, hourly_rate = :hourly_rate, fitness_declaration = :fit";
            $update_fields = [
                'skillset' => $_POST['skillset'],
                'secondary_skills' => $_POST['secondary_skills'],
                'hourly_rate' => $_POST['hourly_rate'],
                'fit' => isset($_POST['fitness_declaration']) ? 1 : 0
            ];
            if ($doc_path) { $detail_sql .= ", safety_cert_path = :doc"; $update_fields['doc'] = $doc_path; }
            break;

        case 'admin':
            $detail_table = "Admin_Details"; $pk = "admin_id";
            $detail_sql = "UPDATE Admin_Details SET permission_scope = :permission_scope, audit_responsibility = :audit_responsibility";
            $update_fields = [
                'permission_scope' => $_POST['permission_scope'],
                'audit_responsibility' => $_POST['audit_responsibility']
            ];
            break;
    }

    if ($detail_sql) {
        $detail_sql .= " WHERE $pk = :uid";
        $update_fields['uid'] = $user_id;
        $stmt = $pdo->prepare($detail_sql);
        $stmt->execute($update_fields);
    }

    // 5. Calculate Completion Percentage (Simplified Algorithm)
    // Count non-empty values in POST
    $total_possible = count($_POST) + ($profile_picture ? 1 : 0) + ($doc_path ? 1 : 0);
    $filled = 0;
    foreach ($_POST as $key => $val) { if (!empty($val)) $filled++; }
    if ($profile_picture) $filled++;
    if ($doc_path) $filled++;

    // Basic scale to 100
    $completion = min(100, round(($filled / 15) * 100)); // Assuming ~15 key fields
    
    $pdo->prepare("UPDATE Users SET completion_status = ? WHERE user_id = ?")->execute([$completion, $user_id]);

    $pdo->commit();
    $_SESSION['success_message'] = "Profile updated successfully!";
    header("Location: ../profile.php");

} catch (Exception $e) {
    $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}
?>
