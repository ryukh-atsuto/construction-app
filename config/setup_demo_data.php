<?php
require_once 'db.php';

echo "Setting up demo data...<br>";

try {
    // 1. Clear existing data (optional, but good for clean slate)
    // Disable foreign key checks to avoid deletion errors
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['Users', 'Owner_Details', 'Worker_Details', 'Manager_Details', 'Projects', 'Under_Construction_Projects', 'Finished_Projects', 'Suppliers', 'Materials', 'Project_Materials', 'Worker_Assignments', 'Engineer_Consultations', 'After_Sale_Requests', 'After_Sale_Assignments', 'Payments'];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Tables cleared.<br>";

    // 2. Create Users
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    // Admin
    $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Admin User', 'admin@test.com', $password]);
    $admin_id = $pdo->lastInsertId();

    // Owner
    $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, 'owner')");
    $stmt->execute(['John Owner', 'owner@test.com', $password]);
    $owner_id = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO Owner_Details (owner_id, company_name, address) VALUES (?, 'Skyline Developers', '123 Main St')")->execute([$owner_id]);

    // Managers (2)
    $mgrs = [];
    // Correct loop for Managers
    for ($i=1; $i<=2; $i++) {
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, 'manager')");
        $stmt->execute(["Sarah Manager $i", "manager$i@test.com", $password]);
        $mgr_id = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO Manager_Details (manager_id, experience_years, assigned_region, work_shift) VALUES (?, ?, 'Region 1', 'Day')")->execute([$mgr_id, 5+$i]);
        $mgrs[] = $mgr_id;
    }

    // Engineers (2)
    $engs = [];
    for ($i=1; $i<=2; $i++) {
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, 'engineer')");
        $stmt->execute(["Mike Engineer $i", "engineer$i@test.com", $password]);
        $engs[] = $pdo->lastInsertId();
    }

    // Workers (5)
    $workers = [];
    $skills = ['Masonry', 'Plumbing', 'Electrical', 'Carpentry', 'Painting'];
    $ratings = [4.8, 3.5, 4.2, 4.9, 4.0];
    $statuses = ['available', 'busy', 'available', 'available', 'busy'];

    foreach ($skills as $i => $skill) {
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, 'worker')");
        $stmt->execute(["Worker " . ($i+1) . " ($skill)", "worker" . ($i+1) . "@test.com", $password]);
        $wk_id = $pdo->lastInsertId();
        $workers[] = $wk_id;
        
        $pdo->prepare("INSERT INTO Worker_Details (worker_id, skillset, hourly_rate, availability_status, rating, experience_years) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$wk_id, $skill, 20 + ($i*5), $statuses[$i], $ratings[$i], 3 + $i]);
    }
    echo "Users created.<br>";

    // 3. Suppliers & Materials
    $suppliers_data = ['BuildPro Supply', 'SteelWorks Inc', 'Cement Bros'];
    $supplier_ids = [];
    foreach ($suppliers_data as $s) {
        $contact_details = "Contact: John Doe | Phone: 555-0100 | Email: supp@test.com | Address: Ind Rd";
        $stmt = $pdo->prepare("INSERT INTO Suppliers (supplier_name, contact_info) VALUES (?, ?)");
        $stmt->execute([$s, $contact_details]);
        $supplier_ids[] = $pdo->lastInsertId();
    }

    $materials_data = [
        ['Cement', 'Bags', 12.50, $supplier_ids[2]],
        ['Steel Beams', 'Ton', 800.00, $supplier_ids[1]],
        ['Bricks', '1000 pcs', 150.00, $supplier_ids[0]],
        ['Paint', 'Gallon', 45.00, $supplier_ids[0]],
        ['Wood Planks', 'Unit', 8.00, $supplier_ids[0]]
    ];
    foreach ($materials_data as $m) {
        $stmt = $pdo->prepare("INSERT INTO Materials (material_name, unit, unit_price, supplier_id) VALUES (?, ?, ?, ?)");
        $stmt->execute($m);
    }
    echo "Materials created.<br>";

    // 4. Projects
    // Project 1: Pending
    $stmt = $pdo->prepare("INSERT INTO Projects (owner_id, project_name, location, start_date, end_date, total_project_cost, approval_status) VALUES (?, 'Sunrise Apartments', 'Downtown', '2024-01-01', '2024-12-31', 500000.00, 'pending')");
    $stmt->execute([$owner_id]);
    
    // Project 2: Active (Under Construction)
    $stmt = $pdo->prepare("INSERT INTO Projects (owner_id, project_name, location, start_date, end_date, total_project_cost, approval_status, approved_by, managed_by) VALUES (?, 'City Mall Reno', 'Westside', '2024-02-01', '2024-08-30', 150000.00, 'approved', ?, ?)");
    $stmt->execute([$owner_id, $admin_id, $mgrs[0]]);
    $p2_id = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO Under_Construction_Projects (project_id, construction_phase, expected_completion_date) VALUES (?, 'Foundation', '2024-08-30')")->execute([$p2_id]);

    // Project 3: Finished
    $stmt = $pdo->prepare("INSERT INTO Projects (owner_id, project_name, location, start_date, end_date, total_project_cost, approval_status, approved_by, managed_by) VALUES (?, 'Lakeside Villa', 'Lakeview', '2023-01-01', '2023-11-30', 300000.00, 'approved', ?, ?)");
    $stmt->execute([$owner_id, $admin_id, $mgrs[1]]);
    $p3_id = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO Finished_Projects (project_id, handover_date, warranty_expiry) VALUES (?, '2023-12-01', '2024-12-01')")->execute([$p3_id]);
    
    echo "Projects created.<br>";

    // 5. Assignments & Consultations
    // Assign Worker to P2
    $stmt = $pdo->prepare("INSERT INTO Worker_Assignments (project_id, worker_id, assigned_by, task_description, start_date, end_date, status) VALUES (?, ?, ?, 'Lay foundation bricks', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'assigned')");
    $stmt->execute([$p2_id, $workers[0], $mgrs[0]]);

    // Consult Request for P2
    $stmt = $pdo->prepare("INSERT INTO Engineer_Consultations (project_id, engineer_id, requested_by, approved_by, coordinated_by, consultation_date, status) VALUES (?, ?, ?, ?, ?, NOW(), 'approved')");
    $stmt->execute([$p2_id, $engs[0], $owner_id, $admin_id, $mgrs[0]]);

    echo "Assignments created.<br>";
    echo "<strong>Demo Data Setup Complete! Password for all users: 'password123'</strong>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
