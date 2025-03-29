<?php
// Database Configuration
$host = "localhost"; // Change if needed
$user = "root"; // Database username
$password = ""; // Database password
$dbname = "spotcat_db"; // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => 500, "message" => "Database connection failed: " . $conn->connect_error]));
}
?>
