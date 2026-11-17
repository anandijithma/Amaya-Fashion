<?php
session_start();

include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = intval($_SESSION["user_id"]);
$successMessage = "";
$errorMessage = "";

/*
  Handle profile update
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");

    if ($name === "" || $username === "" || $email === "" || $telephone === "") {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } else {
        /*
          Check whether another user already has this email
        */
        $checkSql = "SELECT id FROM `user` WHERE email = ? AND id != ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "si", $email, $userId);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errorMessage = "This email is already used by another account.";
        } else {
            /*
              Update user profile
            */
            $updateSql = "UPDATE `user`
                          SET `name` = ?, `username` = ?, `email` = ?, `telephone` = ?
                          WHERE `id` = ?";

            $updateStmt = mysqli_prepare($conn, $updateSql);

            if (!$updateStmt) {
                $errorMessage = "Update prepare failed: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($updateStmt, "ssssi", $name, $username, $email, $telephone, $userId);

                if (mysqli_stmt_execute($updateStmt)) {
                    $_SESSION["name"] = $name;
                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;
                    $_SESSION["telephone"] = $telephone;

                    $successMessage = "Profile updated successfully.";
                } else {
                    $errorMessage = "Profile update failed: " . mysqli_error($conn);
                }

                mysqli_stmt_close($updateStmt);
            }
        }

        mysqli_stmt_close($checkStmt);
    }
}

/*
  Get latest user details from database
*/
$sql = "SELECT id, name, username, email, telephone, userole
        FROM `user`
        WHERE id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit();
}

$name = $user["name"] ?? "";
$username = $user["username"] ?? "";
$email = $user["email"] ?? "";
$telephone = $user["telephone"] ?? "";
$userole = $user["userole"] ?? "";

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - Amaya Fashion</title>

    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #111;
        }

        .header {
            background: #000;
            color: white;
            padding: 22px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            letter-spacing: 1px;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .profile-container {
            max-width: 850px;
            margin: 60px auto;
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        }

        .profile-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .profile-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b39b5e, #d4af7a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 34px;
        }

        h1 {
            margin: 0;
            font-size: 32px;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .details-box {
            border-top: 1px solid #eee;
            margin-top: 25px;
            padding-top: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #b39b5e;
            box-shadow: 0 0 0 3px rgba(179, 155, 94, 0.15);
        }

        .role-box {
            padding: 13px 14px;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #f8f8f8;
            color: #555;
            font-weight: bold;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            padding: 13px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-save {
            background: #27ae60;
            color: white;
        }

        .btn-home {
            background: #111;
            color: white;
        }

        .btn-logout {
            background: #e74c3c;
            color: white;
        }

        .btn-admin {
            background: #b39b5e;
            color: white;
        }

        .note {
            margin-top: 12px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            .profile-container {
                margin: 30px 15px;
                padding: 25px;
            }

            .header {
                padding: 18px 20px;
                flex-direction: column;
                gap: 12px;
            }

            .header a {
                margin-left: 10px;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <h2>AMAYA FASHION</h2>
    <div>
        <a href="index.html">Home</a>
        <a href="cart.html">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="profile-container">

    <div class="profile-title">
        <div class="profile-icon">
            <i class="bi bi-person"></i>
        </div>

        <div>
            <h1>My Account</h1>
            <p>Welcome, <?php echo htmlspecialchars($username); ?></p>
        </div>
    </div>

    <?php if ($successMessage !== ""): ?>
        <div class="message-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ""): ?>
        <div class="message-error">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
        <div class="details-box">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="telephone">Telephone</label>
                <input
                    type="text"
                    id="telephone"
                    name="telephone"
                    value="<?php echo htmlspecialchars($telephone); ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <div class="role-box">
                    <?php echo htmlspecialchars($userole); ?>
                </div>
                <p class="note">Role cannot be edited from the profile page.</p>
            </div>

        </div>

        <div class="actions">
            <button type="submit" class="btn btn-save">Save Changes</button>
            <a href="index.html" class="btn btn-home">Back to Home</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>

            <?php if ($userole === "admin"): ?>
                <a href="admin.php" class="btn btn-admin">Go to Admin Panel</a>
            <?php endif; ?>
        </div>
    </form>

</div>

</body>
</html>