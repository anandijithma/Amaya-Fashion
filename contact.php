<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.html");
    exit();
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    echo "<script>
            alert('All fields are required.');
            window.history.back();
          </script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Please enter a valid email address.');
            window.history.back();
          </script>";
    exit();
}

$sql = "INSERT INTO `amaya_messages` (`name`, `email`, `subject`, `message`, `status`) 
        VALUES (?, ?, ?, ?, 'Unread')";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>
            alert('Message sent successfully.');
            window.location.href = 'contact.html';
          </script>";
    exit();
} else {
    echo "Message insert failed: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>