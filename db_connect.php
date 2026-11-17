<?php
$host = "localhost";
$db_username = "root";
$db_password = "";
$database = "amaya_fashion";

$conn = mysqli_connect($host, $db_username, $db_password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>