<?php
// Database connection parameters
$servername = "localhost"; // The server where MySQL is running (usually "localhost" for XAMPP)
$username = "root";      // The default MySQL username for XAMPP
$password = "";          // The default MySQL password for XAMPP (it's empty by default)
$dbname = "clinic_db";   // The name of the database we just created

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If there's an error, stop the script and display the error message
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to UTF-8 for proper handling of various characters
// This is good practice to prevent character encoding issues
$conn->set_charset("utf8");

// You can uncomment the line below temporarily to verify the connection is working.
// It will print "Connected successfully to the database!" if all is well.
// echo "Connected successfully to the database!";
?>