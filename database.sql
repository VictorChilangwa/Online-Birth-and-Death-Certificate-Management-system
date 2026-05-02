-- Database: birth_death_management
CREATE DATABASE IF NOT EXISTS birth_death_management;
USE birth_death_management;

-- Table for Users (Staff, Registrar, Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('hospital_staff', 'registrar', 'admin') NOT NULL,
    hospital_name VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for Birth Applications
CREATE TABLE IF NOT EXISTS birth_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    child_fullname VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(200) NOT NULL,
    father_fullname VARCHAR(100),
    mother_fullname VARCHAR(100),
    -- Third party details
    third_party_name VARCHAR(100) NOT NULL,
    third_party_nid VARCHAR(50) NOT NULL,
    third_party_relation VARCHAR(50) NOT NULL,
    third_party_contact VARCHAR(50) NOT NULL,
    -- Financials & Processing
    fee_amount DECIMAL(10,2) DEFAULT 35.00,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_remarks TEXT,
    supporting_doc VARCHAR(255),
    -- Audit
    registered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Death Applications
CREATE TABLE IF NOT EXISTS death_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    deceased_fullname VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    date_of_death DATE NOT NULL,
    place_of_death VARCHAR(200) NOT NULL,
    cause_of_death VARCHAR(255),
    age_at_death INT,
    -- Third party details
    third_party_name VARCHAR(100) NOT NULL,
    third_party_nid VARCHAR(50) NOT NULL,
    third_party_relation VARCHAR(50) NOT NULL,
    third_party_contact VARCHAR(50) NOT NULL,
    -- Financials & Processing
    fee_amount DECIMAL(10,2) DEFAULT 45.00,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_remarks TEXT,
    supporting_doc VARCHAR(255),
    -- Audit
    registered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin (Username: admin, Password: admin123)
INSERT INTO users (fullname, email, username, password, role) 
VALUES ('Super Admin', 'admin@system.com', 'admin', '$2y$10$U114AQBscXT364Gxf1lH5uZBUw5k9if7N98i2AiIaYjYobqZ9wI/2', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Insert a test Hospital Staff
INSERT INTO users (fullname, email, username, password, role, hospital_name) 
VALUES ('John Doe Staff', 'staff@system.com', 'staff1', '$2y$10$U114AQBscXT364Gxf1lH5uZBUw5k9if7N98i2AiIaYjYobqZ9wI/2', 'hospital_staff', 'Lusaka General Hospital')
ON DUPLICATE KEY UPDATE id=id;

-- Insert a test Registrar
INSERT INTO users (fullname, email, username, password, role) 
VALUES ('Jane Smith Registrar', 'registrar@system.com', 'registrar1', '$2y$10$U114AQBscXT364Gxf1lH5uZBUw5k9if7N98i2AiIaYjYobqZ9wI/2', 'registrar')
ON DUPLICATE KEY UPDATE id=id;
