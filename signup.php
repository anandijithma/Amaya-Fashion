<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$name = trim($_POST["name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$telephone = trim($_POST["telephone"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($name === "" || $username === "" || $email === "" || $telephone === "" || $password === "" || $confirmPassword === "") {
    echo "<script>
            alert('All fields are required.');
            window.history.back();
          </script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Invalid email address.');
            window.history.back();
          </script>";
    exit();
}

if ($password !== $confirmPassword) {
    echo "<script>
            alert('Passwords do not match.');
            window.history.back();
          </script>";
    exit();
}

if (strlen($password) < 6) {
    echo "<script>
            alert('Password must be at least 6 characters long.');
            window.history.back();
          </script>";
    exit();
}

/* Check whether email already exists */
$checkSql = "SELECT id FROM `user` WHERE email = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);

if (!$checkStmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($checkStmt, "s", $email);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    echo "<script>
            alert('This email is already registered.');
            window.history.back();
          </script>";
    exit();
}

mysqli_stmt_close($checkStmt);

/* Hash password */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* Set user role safely from backend */
$userole = "customer";

/* Insert user */
$insertSql = "INSERT INTO `user` (`name`, `username`, `password`, `email`, `telephone`, `userole`) 
              VALUES (?, ?, ?, ?, ?, ?)";

$insertStmt = mysqli_prepare($conn, $insertSql);

if (!$insertStmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $insertStmt,
    "ssssss",
    $name,
    $username,
    $hashedPassword,
    $email,
    $telephone,
    $userole
);

if (mysqli_stmt_execute($insertStmt)) {
    echo "<script>
            alert('Account created successfully. Please login.');
            window.location.href = 'login.html';
          </script>";
    exit();
} else {
    echo "Signup failed: " . mysqli_error($conn);
}

mysqli_stmt_close($insertStmt);
mysqli_close($conn);
?>