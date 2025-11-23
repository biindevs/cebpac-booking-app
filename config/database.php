<?php
// Production database configuration for Render.com
// Uses environment variables set in Render dashboard

$db_host = getenv('DATABASE_HOST') ?: 'localhost';
$db_user = getenv('DATABASE_USER') ?: 'root';
$db_pass = getenv('DATABASE_PASS') ?: '';
$db_name = getenv('DATABASE_NAME') ?: 'cebpac_booking_db';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Define upload path (ensure it exists)
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

$app_env = getenv('APP_ENV') ?: 'development';
