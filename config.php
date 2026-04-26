<?php
// 1. Error Reporting (Keep this on until the site is working)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Database Configuration for BookBridge
 * Version: LOCALHOST (XAMPP/WAMP)
 */

// 2. Database Connection Details
$host = "localhost";        // Standard for local servers
$user = "root";             // Default XAMPP/WAMP username
$pass = "";                 // Default XAMPP password is empty (blank)
$db   = "bookbridge_db";    // Change this to the name you created in phpMyAdmin

// 3. Create the connection
$conn = new mysqli($host, $user, $pass, $db);

// 4. Check if the connection works
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// 5. Set Charset to UTF-8
$conn->set_charset("utf8mb4");

// 6. Start Session (Required for Login)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Success! The database is now connected locally.
?>
