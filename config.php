<?php
// Database connection settings
$host = 'localhost';          // Database server
$dbname = 'dolphin_crm';      // Database name
$username = 'root';            // Database username
$password = '';                // Database password (empty for XAMPP by default)

// Try to connect to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If connection fails, show error message
    die("Could not connect to database: " . $e->getMessage());
}

// Start user session for login tracking
session_start();
?>