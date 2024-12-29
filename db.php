<?php
$host = "localhost";
$dbname = "waste_management_system";
$username = "root";
$password = ""; 

$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
