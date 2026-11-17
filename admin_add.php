<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";

/*
  Only admin can access this page
*/
if (!isset($_SESSION["user_id"]) || ($_SESSION["userole"] ?? "") !== "admin") {
    header("Location: login.html");
    exit();
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($name === "" || $username === "" || $email === "" || $telephone === "" || $password === "" || $confirmPassword === "") {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $errorMessage = "Password must be at least 6 characters long.";
    } else {
        /*
          Check duplicate email
        */
        $checkSql = "SELECT id FROM `user` WHERE email = ? OR username = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);

        if (!$checkStmt) {
            $errorMessage = "Database check failed: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($checkStmt, "ss", $email, $username);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $errorMessage = "Email or username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userole = "admin";

                $insertSql = "INSERT INTO `user` (`name`, `username`, `password`, `email`, `telephone`, `userole`)
                              VALUES (?, ?, ?, ?, ?, ?)";

                $insertStmt = mysqli_prepare($conn, $insertSql);

                if (!$insertStmt) {
                    $errorMessage = "Database insert failed: " . mysqli_error($conn);
                } else {
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
                        $successMessage = "Admin user created successfully.";

                        $name = "";
                        $username = "";
                        $email = "";
                        $telephone = "";
                    } else {
                        $errorMessage = "Admin creation failed: " . mysqli_error($conn);
                    }

                    mysqli_stmt_close($insertStmt);
                }
            }

            mysqli_stmt_close($checkStmt);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Admin - Amaya Fashion</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #111;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #111;
            color: white;
            padding: 25px;
        }

        .sidebar h2 {
            color: #d4af7a;
            margin-top: 0;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #b39b5e;
            color: #111;
        }

        .main {
            padding: 35px;
        }

        .form-card {
            max-width: 700px;
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            color: #111;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #b39b5e;
            box-shadow: 0 0 0 3px rgba(179,155,94,0.15);
        }

        .btn {
            padding: 13px 22px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-save {
            background: #b39b5e;
            color: white;
        }

        .btn-back {
            background: #111;
            color: white;
            margin-left: 10px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .note {
            background: #fff8e5;
            border-left: 4px solid #b39b5e;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #555;
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <aside class="sidebar">
        <h2>AMAYA ADMIN</h2>

        <a href="admin.php?table=user">Users</a>
        <a href="admin.php?table=orders">Orders</a>
        <a href="admin.php?table=order_items">Order Items</a>
        <a href="admin.php?table=amaya_messages">Messages</a>
        <a href="admin_add.php" class="active">Add Admin</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main">
        <div class="form-card">
            <h1>Add New Admin</h1>

            <?php if ($successMessage !== ""): ?>
                <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>
                <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="admin_add.php">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($name ?? ""); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($username ?? ""); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email ?? ""); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="telephone">Telephone</label>
                    <input
                        type="text"
                        id="telephone"
                        name="telephone"
                        value="<?php echo htmlspecialchars($telephone ?? ""); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required>
                </div>

                <button type="submit" class="btn btn-save">Create Admin</button>
                <a href="admin.php?table=user" class="btn btn-back">Back to Users</a>

            </form>
        </div>
    </main>

</div>

</body>
</html>