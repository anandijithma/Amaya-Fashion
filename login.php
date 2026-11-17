<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo "<script>
            alert('Email and password are required.');
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


$sql = "SELECT id, name, username, password, email, telephone, userole 
        FROM `user` 
        WHERE email = ? 
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    echo "<script>
            alert('No account found with this email.');
            window.history.back();
          </script>";
    exit();
}

$user = mysqli_fetch_assoc($result);


if (!password_verify($password, $user["password"])) {
    echo "<script>
            alert('Invalid password.');
            window.history.back();
          </script>";
    exit();
}


$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"] = $user["email"];
$_SESSION["telephone"] = $user["telephone"];
$_SESSION["userole"] = $user["userole"];


if ($user["userole"] === "admin") {
    header("Location: admin.php");
    exit();
} elseif ($user["userole"] === "customer") {
    header("Location: index.html");
    exit();
} else {
    echo "<script>
            alert('Invalid user role. Contact admin.');
            window.history.back();
          </script>";
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>