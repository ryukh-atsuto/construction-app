<?php
// config/db_migration_v3.php
require_once 'db.php';

echo "Starting Phase 3 Database Migration (Smart Profiles)...<br>";

try {
    // 1. Update Users Table
    $pdo->exec("ALTER TABLE Users 
        ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT 'default_avatar.png' AFTER role,
        ADD COLUMN IF NOT EXISTS age INT AFTER profile_picture,
        ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other') AFTER age,
        ADD COLUMN IF NOT EXISTS national_id VARCHAR(50) AFTER gender,
        ADD COLUMN IF NOT EXISTS address TEXT AFTER national_id,
        ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255) AFTER address,
        ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(100) AFTER linkedin_url,
        ADD COLUMN IF NOT EXISTS completion_status INT DEFAULT 0 AFTER emergency_contact");
    echo "Users table updated.<br>";

    // 2. Update Owner_Details
    $pdo->exec("ALTER TABLE Owner_Details 
        ADD COLUMN IF NOT EXISTS owner_type ENUM('Individual', 'Company') DEFAULT 'Individual',
        ADD COLUMN IF NOT EXISTS business_reg_number VARCHAR(100),
        ADD COLUMN IF NOT EXISTS investment_range VARCHAR(100),
        ADD COLUMN IF NOT EXISTS past_experience TEXT,
        ADD COLUMN IF NOT EXISTS verification_doc_path VARCHAR(255)");
    echo "Owner_Details table updated.<br>";

    // 3. Update Manager_Details
    $pdo->exec("ALTER TABLE Manager_Details 
        ADD COLUMN IF NOT EXISTS leadership_cert_path VARCHAR(255),
        ADD COLUMN IF NOT EXISTS availability_status ENUM('available','busy','on_leave') DEFAULT 'available',
        ADD COLUMN IF NOT EXISTS performance_rating DECIMAL(3,2)");
    echo "Manager_Details table updated.<br>";

    // 4. Update Engineer_Details
    $pdo->exec("ALTER TABLE Engineer_Details 
        ADD COLUMN IF NOT EXISTS issuing_authority VARCHAR(150),
        ADD COLUMN IF NOT EXISTS certificates_json JSON,
        ADD COLUMN IF NOT EXISTS years_experience INT,
        ADD COLUMN IF NOT EXISTS portfolio_url VARCHAR(255),
        ADD COLUMN IF NOT EXISTS availability_calendar_link VARCHAR(255)");
    echo "Engineer_Details table updated.<br>";

    // 5. Update Worker_Details
    $pdo->exec("ALTER TABLE Worker_Details 
        ADD COLUMN IF NOT EXISTS secondary_skills TEXT,
        ADD COLUMN IF NOT EXISTS work_availability_notes TEXT,
        ADD COLUMN IF NOT EXISTS project_photos_json JSON,
        ADD COLUMN IF NOT EXISTS safety_cert_path VARCHAR(255),
        ADD COLUMN IF NOT EXISTS fitness_declaration BOOLEAN DEFAULT FALSE");
    echo "Worker_Details table updated.<br>";

    // 6. Update Admin_Details
    $pdo->exec("ALTER TABLE Admin_Details 
        ADD COLUMN IF NOT EXISTS permission_scope VARCHAR(150),
        ADD COLUMN IF NOT EXISTS system_access_areas TEXT,
        ADD COLUMN IF NOT EXISTS audit_responsibility TEXT");
    echo "Admin_Details table updated.<br>";

    echo "<strong>Migration Successful!</strong><br>";
    echo "Please visit <a href='setup_demo_data.php'>setup_demo_data.php</a> to refresh demo content if needed.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
