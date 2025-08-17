<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Default for XAMPP is empty
$database = 'hostel_db'; // Make sure this DB is created in phpMyAdmin

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
