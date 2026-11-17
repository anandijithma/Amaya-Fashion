<?php
session_start();

include "db_connect.php";
include "admin_config.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["userole"] ?? "") !== "admin") {
    header("Location: login.html");
    exit();
}

mysqli_set_charset($conn, "utf8mb4");

$table = $_GET["table"] ?? "user";
$config = getTableConfig($table);

if (!$config) {
    die("Invalid table.");
}

$search = trim($_GET["search"] ?? "");
$displayColumns = $config["display"];
$primaryKey = $config["primary"];

$selectSql = implode(", ", array_map("bt", $displayColumns));
$sql = "SELECT $selectSql FROM " . bt($table);

$params = [];
$types = "";

if ($search !== "") {
    $whereParts = [];

    foreach ($config["search"] as $column) {
        $whereParts[] = "CAST(" . bt($column) . " AS CHAR) LIKE ?";
        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    $sql .= " WHERE " . implode(" OR ", $whereParts);
}

$sql .= " ORDER BY " . bt($primaryKey) . " DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($config["label"]); ?> Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            color: #111;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .report-header h1 {
            margin: 0;
            color: #b39b5e;
        }

        .meta {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #111;
            color: white;
        }

        @media print {
            button {
                display: none;
            }

            body {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<button onclick="window.print()">Print / Save as PDF</button>

<div class="report-header">
    <h1>Amaya Fashion</h1>
    <h2><?php echo htmlspecialchars($config["label"]); ?> Report</h2>
    <div class="meta">
        Generated on: <?php echo date("Y-m-d H:i:s"); ?>
        <?php if ($search !== ""): ?>
            | Search: <?php echo htmlspecialchars($search); ?>
        <?php endif; ?>
    </div>
</div>

<table>
    <thead>
        <tr>
            <?php foreach ($displayColumns as $column): ?>
                <th><?php echo htmlspecialchars($column); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <?php foreach ($displayColumns as $column): ?>
                        <td><?php echo htmlspecialchars($row[$column] ?? ""); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo count($displayColumns); ?>">No records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    window.onload = function () {
        setTimeout(function () {
            window.print();
        }, 500);
    };
</script>

</body>
</html>