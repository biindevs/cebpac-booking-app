<?php
/**
 * Database Configuration File
 * Cebu Pacific Booking Tracker
 */

// Database Connection Parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cebpac_booking_db');
define('DB_PORT', 3306);

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Optional: Disable warnings in production
$conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

// Global database connection variable
global $db;
$db = $conn;

?>
