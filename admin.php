<?php
session_start();

include "db_connect.php";
include "admin_config.php";

/* Access control: Only allow logged-in admins */

if (!isset($_SESSION["user_id"]) || ($_SESSION["userole"] ?? "") !== "admin") {
    header("Location: login.html");
    exit();
}

/*character encoding*/
mysqli_set_charset($conn, "utf8mb4");


/*Get Table from URL*/

$table = $_GET["table"] ?? "user";
$config = getTableConfig($table);

if (!$config) {
    $table = "user";
    $config = getTableConfig($table);
}

$primaryKey = $config["primary"];
$message = "";
$error = "";

/* Delete record */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $deleteTable = $_POST["table"] ?? "";
    $deleteId = intval($_POST["id"] ?? 0);
    $deleteConfig = getTableConfig($deleteTable);

    if ($deleteConfig && $deleteId > 0) {
        if ($deleteTable === "orders") {
            $childSql = "DELETE FROM `order_items` WHERE `order_id` = ?";
            $childStmt = mysqli_prepare($conn, $childSql);
            mysqli_stmt_bind_param($childStmt, "i", $deleteId);
            mysqli_stmt_execute($childStmt);
            mysqli_stmt_close($childStmt);
        }

        $sql = "DELETE FROM " . bt($deleteTable) . " WHERE " . bt($deleteConfig["primary"]) . " = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $deleteId);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Record deleted successfully.";
        } else {
            $error = "Delete failed: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}

/* Update record */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "update") {
    $updateTable = $_POST["table"] ?? "";
    $updateId = intval($_POST["id"] ?? 0);
    $updateConfig = getTableConfig($updateTable);

    if ($updateConfig && $updateId > 0) {
        $setParts = [];
        $values = [];
        $types = "";

        foreach ($updateConfig["editable"] as $column) {
            $setParts[] = bt($column) . " = ?";
            $values[] = $_POST[$column] ?? "";
            $types .= "s";
        }

        $values[] = $updateId;
        $types .= "i";

        $sql = "UPDATE " . bt($updateTable) . " SET " . implode(", ", $setParts) . " WHERE " . bt($updateConfig["primary"]) . " = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Record updated successfully.";
            } else {
                $error = "Update failed: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Update prepare failed: " . mysqli_error($conn);
        }
    }
}

/* Get selected edit record */
$editRecord = null;
$editId = intval($_GET["edit"] ?? 0);

if ($editId > 0) {
    $sql = "SELECT * FROM " . bt($table) . " WHERE " . bt($primaryKey) . " = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editRecord = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

/* Search records */
$search = trim($_GET["search"] ?? "");
$displayColumns = $config["display"];

$params = [];
$types = "";

/*
  Special query for order_items because customer email belongs to orders table.
*/
if ($table === "order_items") {

    $sql = "SELECT 
                oi.`id`,
                oi.`order_id`,
                o.`customer_email` AS `customer_email`,
                oi.`product_id`,
                oi.`product_name`,
                oi.`color`,
                oi.`size`,
                oi.`price`,
                oi.`quantity`,
                oi.`item_total`
            FROM `order_items` oi
            LEFT JOIN `orders` o ON oi.`order_id` = o.`id`";

    if ($search !== "") {
        $sql .= " WHERE 
                    CAST(oi.`id` AS CHAR) LIKE ? OR
                    CAST(oi.`order_id` AS CHAR) LIKE ? OR
                    o.`customer_email` LIKE ? OR
                    oi.`product_id` LIKE ? OR
                    oi.`product_name` LIKE ? OR
                    oi.`color` LIKE ? OR
                    oi.`size` LIKE ?";

        $searchTerm = "%" . $search . "%";

        for ($i = 0; $i < 7; $i++) {
            $params[] = $searchTerm;
            $types .= "s";
        }
    }

    $sql .= " ORDER BY oi.`id` DESC LIMIT 300";

} else {

    $selectSql = implode(", ", array_map("bt", $displayColumns));

    $sql = "SELECT $selectSql FROM " . bt($table);

    if ($search !== "") {
        $whereParts = [];

        foreach ($config["search"] as $column) {
            $whereParts[] = "CAST(" . bt($column) . " AS CHAR) LIKE ?";
            $params[] = "%" . $search . "%";
            $types .= "s";
        }

        $sql .= " WHERE " . implode(" OR ", $whereParts);
    }

    $sql .= " ORDER BY " . bt($primaryKey) . " DESC LIMIT 300";
}

/*Database Execute Query */
$stmt = mysqli_prepare($conn, $sql);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$records = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Amaya Fashion</title>
    
         <script>
    window.addEventListener("pageshow", function (event) {
        if (event.persisted || window.performance.navigation.type === 2) {
            // Reload to trigger PHP session check
            window.location.reload();
        }
    });
    </script>

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
            margin-top: 0;
            color: #d4af7a;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #b39b5e;
            color: #111;
        }

        .main {
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .search-box input {
            padding: 12px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .btn {
            border: none;
            padding: 11px 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-search {
            background: #111;
            color: white;
        }

        .btn-report {
            background: #b39b5e;
            color: white;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-save {
            background: #27ae60;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: #111;
            color: white;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .edit-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .edit-box input,
        .edit-box textarea,
        .edit-box select {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            margin-bottom: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .edit-box textarea {
            min-height: 100px;
        }

        .text-small {
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout {
            color: #d4af7a !important;
            margin-top: 30px;
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <aside class="sidebar">
        <h2> AMAYA ADMIN </h2>

        <?php foreach ($ADMIN_TABLES as $tableName => $tableInfo): ?>
            <a href="admin.php?table=<?php echo urlencode($tableName); ?>" class="<?php echo $tableName === $table ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($tableInfo["label"]); ?>
            </a>
        <?php endforeach; ?>

        <a href="admin_add.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'admin_add.php' ? 'active' : ''; ?>">
    Add Admin
        </a>

        <a href="logout.php" class="logout">Logout</a>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1><?php echo htmlspecialchars($config["label"]); ?></h1>
                <p>Manage, search, edit, delete, and generate reports.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="search-box">
    <form method="GET" action="admin.php">
        <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">

        <input 
            type="text" 
            name="search" 
            placeholder="Search <?php echo htmlspecialchars($config["label"]); ?>..." 
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <button class="btn btn-search" type="submit">Search</button>

        <a class="btn btn-report" href="admin.php?table=<?php echo urlencode($table); ?>">
            Clear
        </a>

        <a 
            class="btn btn-report" 
            target="_blank"
            href="admin_report.php?table=<?php echo urlencode($table); ?>&search=<?php echo urlencode($search); ?>">
            Generate Report
        </a>
    </form>
</div>

        <?php if ($editRecord): ?>
            <div class="edit-box">
                <h2>Edit Record #<?php echo htmlspecialchars($editRecord[$primaryKey]); ?></h2>

                <form method="POST" action="admin.php?table=<?php echo urlencode($table); ?>&search=<?php echo urlencode($search); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editRecord[$primaryKey]); ?>">

                    <?php foreach ($config["editable"] as $column): ?>
                        <label><?php echo htmlspecialchars($column); ?></label>

                        <?php if (in_array($column, ["message", "notes", "shipping_address"])): ?>
                            <textarea name="<?php echo htmlspecialchars($column); ?>"><?php echo htmlspecialchars($editRecord[$column] ?? ""); ?></textarea>
                        <?php elseif ($column === "userole"): ?>
                            <select name="userole">
                                <option value="customer" <?php echo ($editRecord[$column] ?? "") === "customer" ? "selected" : ""; ?>>customer</option>
                                <option value="admin" <?php echo ($editRecord[$column] ?? "") === "admin" ? "selected" : ""; ?>>admin</option>
                            </select>
                        <?php elseif ($column === "order_status"): ?>
                            <select name="order_status">
                                <?php
                                $statuses = ["Pending", "Processing", "Shipped", "Completed", "Cancelled"];
                                foreach ($statuses as $status):
                                ?>
                                    <option value="<?php echo $status; ?>" <?php echo ($editRecord[$column] ?? "") === $status ? "selected" : ""; ?>>
                                        <?php echo $status; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="<?php echo htmlspecialchars($column); ?>" value="<?php echo htmlspecialchars($editRecord[$column] ?? ""); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <button class="btn btn-save" type="submit">Save Changes</button>
                    <a class="btn btn-delete" href="admin.php?table=<?php echo urlencode($table); ?>">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <?php foreach ($displayColumns as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($records) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($records)): ?>
                        <tr>
                            <?php foreach ($displayColumns as $column): ?>
                                <td class="text-small">
                                    <?php echo htmlspecialchars($row[$column] ?? ""); ?>
                                </td>
                            <?php endforeach; ?>

                            <td>
                                <a class="btn btn-edit" href="admin.php?table=<?php echo urlencode($table); ?>&search=<?php echo urlencode($search); ?>&edit=<?php echo urlencode($row[$primaryKey]); ?>">
                                    Edit
                                </a>

                                <form method="POST" action="admin.php?table=<?php echo urlencode($table); ?>&search=<?php echo urlencode($search); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row[$primaryKey]); ?>">
                                    <button class="btn btn-delete" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo count($displayColumns) + 1; ?>">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</div>

</body>
</html>