<?php
// config/db_connection.php

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (empty)
$dbname = "clinic_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set character set to UTF-8 for proper handling of characters
$conn->set_charset("utf8mb4");

// Start a session if not already started (important for login management)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
