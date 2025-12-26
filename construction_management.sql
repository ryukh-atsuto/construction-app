-- =====================================================
-- DATABASE CREATION
-- =====================================================
CREATE DATABASE IF NOT EXISTS construction_management;
USE construction_management;

-- =====================================================
-- USERS (SUPERCLASS)
-- =====================================================
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('owner','worker','engineer','manager','admin') NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default_avatar.png',
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    national_id VARCHAR(50),
    address TEXT,
    linkedin_url VARCHAR(255),
    emergency_contact VARCHAR(100),
    completion_status INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- ROLE DETAIL TABLES (SUBCLASSES)
-- =====================================================

CREATE TABLE Owner_Details (
    owner_id INT PRIMARY KEY,
    company_name VARCHAR(150),
    address TEXT,
    preferred_contact_method VARCHAR(50),
    owner_type ENUM('Individual', 'Company') DEFAULT 'Individual',
    business_reg_number VARCHAR(100),
    investment_range VARCHAR(100),
    past_experience TEXT,
    verification_doc_path VARCHAR(255),
    FOREIGN KEY (owner_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Worker_Details (
    worker_id INT PRIMARY KEY,
    skillset VARCHAR(150),
    secondary_skills TEXT,
    experience_years INT,
    hourly_rate DECIMAL(10,2),
    availability_status ENUM('available','busy','inactive') DEFAULT 'available',
    work_availability_notes TEXT,
    rating DECIMAL(3,2),
    project_photos_json JSON,
    safety_cert_path VARCHAR(255),
    fitness_declaration BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (worker_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Engineer_Details (
    engineer_id INT PRIMARY KEY,
    specialization VARCHAR(150),
    license_number VARCHAR(100),
    issuing_authority VARCHAR(150),
    certificates_json JSON,
    years_experience INT,
    consultation_fee DECIMAL(10,2),
    portfolio_url VARCHAR(255),
    availability_calendar_link VARCHAR(255),
    rating DECIMAL(3,2),
    FOREIGN KEY (engineer_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Manager_Details (
    manager_id INT PRIMARY KEY,
    assigned_region VARCHAR(100),
    experience_years INT,
    work_shift VARCHAR(50),
    leadership_cert_path VARCHAR(255),
    availability_status ENUM('available','busy','on_leave') DEFAULT 'available',
    performance_rating DECIMAL(3,2),
    FOREIGN KEY (manager_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Admin_Details (
    admin_id INT PRIMARY KEY,
    admin_level VARCHAR(50),
    permissions TEXT,
    permission_scope VARCHAR(150),
    system_access_areas TEXT,
    audit_responsibility TEXT,
    last_login DATETIME,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- PROJECTS (SUPERCLASS)
-- =====================================================
CREATE TABLE Projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    project_name VARCHAR(150),
    location TEXT,
    start_date DATE,
    end_date DATE,
    approval_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by INT,
    managed_by INT,
    total_project_cost DECIMAL(12,2),
    FOREIGN KEY (owner_id) REFERENCES Users(user_id),
    FOREIGN KEY (approved_by) REFERENCES Users(user_id),
    FOREIGN KEY (managed_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- PROJECT SUBCLASSES (ISA)
-- =====================================================
CREATE TABLE Under_Construction_Projects (
    project_id INT PRIMARY KEY,
    construction_phase VARCHAR(100),
    expected_completion_date DATE,
    FOREIGN KEY (project_id) REFERENCES Projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Finished_Projects (
    project_id INT PRIMARY KEY,
    handover_date DATE,
    warranty_expiry DATE,
    FOREIGN KEY (project_id) REFERENCES Projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SUPPLIERS & MATERIALS
-- =====================================================
CREATE TABLE Suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(150),
    contact_info TEXT
) ENGINE=InnoDB;

CREATE TABLE Materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(150),
    unit VARCHAR(50),
    unit_price DECIMAL(10,2),
    supplier_id INT,
    FOREIGN KEY (supplier_id) REFERENCES Suppliers(supplier_id)
) ENGINE=InnoDB;

-- =====================================================
-- PROJECT MATERIAL PLANNING
-- =====================================================
CREATE TABLE Project_Materials (
    project_material_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    material_id INT,
    planned_quantity DECIMAL(10,2),
    approved_by INT,
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    FOREIGN KEY (material_id) REFERENCES Materials(material_id),
    FOREIGN KEY (approved_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- WORKER ASSIGNMENTS
-- =====================================================
CREATE TABLE Worker_Assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    worker_id INT,
    assigned_by INT,
    task_description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('assigned','completed') DEFAULT 'assigned',
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    FOREIGN KEY (worker_id) REFERENCES Users(user_id),
    FOREIGN KEY (assigned_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- ENGINEER CONSULTATIONS
-- =====================================================
CREATE TABLE Engineer_Consultations (
    consultation_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    engineer_id INT,
    requested_by INT,
    approved_by INT,
    coordinated_by INT,
    consultation_date DATETIME,
    status ENUM('requested','approved','completed') DEFAULT 'requested',
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    FOREIGN KEY (engineer_id) REFERENCES Users(user_id),
    FOREIGN KEY (requested_by) REFERENCES Users(user_id),
    FOREIGN KEY (approved_by) REFERENCES Users(user_id),
    FOREIGN KEY (coordinated_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- AFTER SALE SERVICE
-- =====================================================
CREATE TABLE After_Sale_Requests (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    requested_by INT,
    service_description TEXT,
    request_date DATE,
    status ENUM('requested','approved','rejected','completed') DEFAULT 'requested',
    approved_by INT,
    FOREIGN KEY (project_id) REFERENCES Finished_Projects(project_id),
    FOREIGN KEY (requested_by) REFERENCES Users(user_id),
    FOREIGN KEY (approved_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

CREATE TABLE After_Sale_Assignments (
    after_sale_assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT,
    worker_id INT,
    assigned_by INT,
    assignment_date DATE,
    completion_date DATE,
    status ENUM('assigned','completed') DEFAULT 'assigned',
    FOREIGN KEY (service_id) REFERENCES After_Sale_Requests(service_id),
    FOREIGN KEY (worker_id) REFERENCES Users(user_id),
    FOREIGN KEY (assigned_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- PAYMENTS
-- =====================================================
CREATE TABLE Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    paid_by INT,
    total_amount DECIMAL(12,2),
    admin_commission DECIMAL(10,2),
    payment_date DATETIME,
    status ENUM('pending','completed','failed') DEFAULT 'pending',
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    FOREIGN KEY (paid_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

-- =====================================================
-- AI COST ESTIMATION (FAKE BUT LOGICAL)
-- =====================================================
CREATE TABLE Project_Estimates (
    estimate_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    created_by INT,
    estimate_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_estimated_cost DECIMAL(12,2),
    status ENUM('draft','approved') DEFAULT 'draft',
    FOREIGN KEY (project_id) REFERENCES Projects(project_id),
    FOREIGN KEY (created_by) REFERENCES Users(user_id)
) ENGINE=InnoDB;

CREATE TABLE Estimate_Items (
    estimate_item_id INT AUTO_INCREMENT PRIMARY KEY,
    estimate_id INT,
    item_type ENUM('worker','engineer','material'),
    item_id INT,
    estimated_quantity DECIMAL(10,2),
    estimated_cost DECIMAL(12,2),
    FOREIGN KEY (estimate_id) REFERENCES Project_Estimates(estimate_id)
) ENGINE=InnoDB;
