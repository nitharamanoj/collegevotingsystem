<?php
// ============================================================
// DATABASE CONFIGURATION
// Copy this file to config.php and fill in your actual values
// NEVER commit config.php to version control
// ============================================================

$host   = "localhost";          // e.g. localhost or 127.0.0.1
$user   = "your_db_username";   // e.g. root (local) or your host DB user
$pass   = "your_db_password";   // your database password
$dbname = "votesystem";         // your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
