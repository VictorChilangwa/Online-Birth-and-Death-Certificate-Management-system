<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'birth_death_management');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // If database doesn't exist yet, we'll handle it or show a helpful message
    $db_connection_error = $e->getMessage();
}

// Site settings
define('SITE_NAME', 'B and D RegOnline');
define('BASE_URL', 'http://localhost/birth-death-system/'); // Adjust as needed

session_start();

// Helper to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper to check if user is hospital staff
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hospital_staff';
}

// Helper to check if user is registrar
function isRegistrar() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'registrar';
}
?>
