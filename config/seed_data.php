<?php
require_once 'db.php';
// Faker-like generator
function generateName() {
    $first = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth'];
    $last = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
    return $first[array_rand($first)] . ' ' . $last[array_rand($last)];
}

try {
    echo "<h1>Seeding Database...</h1>";
    
    // 1. Create Users
    $roles = ['manager', 'owner', 'worker', 'engineer'];
    $new_users = [];
    
    foreach ($roles as $role) {
        for ($i = 0; $i < 3; $i++) {
            $name = generateName();
            $email = strtolower(str_replace(' ', '.', $name)) . rand(10,99) . '@example.com';
            $pass = password_hash('password123', PASSWORD_DEFAULT);
            $phone = '555-' . rand(100,999) . '-' . rand(1000,9999);
            
            // Check existence
            $check = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() == 0) {
                $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $pass, $role, $phone]);
                $uid = $pdo->lastInsertId();
                $new_users[$role][] = $uid;
                
                // Role Details
                $table = ucfirst($role) . '_Details';
                $col = $role . '_id';
                $pdo->exec("INSERT INTO $table ($col) VALUES ($uid)");
                
                echo "Created $role: $name ($email)<br>";
            }
        }
    }
    
    // Refresh IDs
    $owners = $pdo->query("SELECT user_id FROM Users WHERE role='owner'")->fetchAll(PDO::FETCH_COLUMN);
    $managers = $pdo->query("SELECT user_id FROM Users WHERE role='manager'")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($owners)) die("No owners found. Run again.");

    // 2.1 Fix Schema: Ensure Projects has created_at
    try {
        $pdo->query("SELECT created_at FROM Projects LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE Projects ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✅ Added missing 'created_at' column to Projects table.<br>";
    }

    // 2. Create Projects
    $project_types = ['Residential', 'Commercial', 'Industrial'];
    $locations = ['New York, NY', 'Austin, TX', 'San Francisco, CA', 'Miami, FL', 'Chicago, IL'];
    
    for ($i = 0; $i < 5; $i++) {
        $owner_id = $owners[array_rand($owners)];
        $manager_id = !empty($managers) ? $managers[array_rand($managers)] : null;
        $name = "Project " . chr(65+$i) . " - " . $project_types[array_rand($project_types)];
        $loc = $locations[array_rand($locations)];
        $cost = rand(50000, 500000);
        $status = ['pending', 'approved', 'completed'][rand(0,2)];
        
        $sql = "INSERT INTO Projects (project_name, location, start_date, end_date, total_project_cost, owner_id, managed_by, approval_status, created_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), DATE_ADD(NOW(), INTERVAL ? DAY), ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, 
            $loc, 
            rand(1, 10), 
            rand(60, 360), 
            $cost, 
            $owner_id, 
            $manager_id, 
            $status
        ]);
        echo "Created Project: $name ($status)<br>";
    }

    // 3. Create Payments
    $projects = $pdo->query("SELECT project_id, owner_id, total_project_cost FROM Projects WHERE approval_status IN ('approved', 'completed')")->fetchAll();
    
    foreach ($projects as $p) {
            $amount = $p['total_project_cost'] * 0.1; // 10% deposit
            $fee = $amount * 0.05; // 5% fee
            $total = $amount + $fee;
            
            $stmt = $pdo->prepare("INSERT INTO Payments (project_id, paid_by, admin_commission, total_amount, payment_date, status) VALUES (?, ?, ?, ?, NOW(), 'completed')");
            $stmt->execute([$p['project_id'], $p['owner_id'], $fee, $total]);
            echo "Created Payment: $" . number_format($total) . " for Project #{$p['project_id']}<br>";
    }
    
    echo "<h2 style='color:green'>Seeding Complete!</h2>";
    echo "<a href='../admin/dashboard.php'>Go to Dashboard</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
