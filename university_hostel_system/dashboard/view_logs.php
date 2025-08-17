<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $userFilter = $_GET['user'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';

    $params = [];
    $where = [];

    if ($userFilter !== '') {
        $where[] = "u.id = ?";
        $params[] = $userFilter;
    }

    if ($dateFrom !== '') {
        $where[] = "l.created_at >= ?";
        $params[] = $dateFrom . " 00:00:00";
    }

    if ($dateTo !== '') {
        $where[] = "l.created_at <= ?";
        $params[] = $dateTo . " 23:59:59";
    }

    $sql = "SELECT l.id, u.name, l.action, l.created_at 
            FROM logs l 
            LEFT JOIN users u ON l.user_id = u.id";

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY l.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) {
        // Dynamically bind params
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activity_logs.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'User', 'Action', 'Date/Time']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'] ?? 'Unknown',
            $row['action'],
            $row['created_at']
        ]);
    }
    fclose($output);
    exit();
}

// Pagination and filtering
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$userFilter = $_GET['user'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$params = [];
$where = [];

if ($userFilter !== '') {
    $where[] = "u.id = ?";
    $params[] = $userFilter;
}
if ($dateFrom !== '') {
    $where[] = "l.created_at >= ?";
    $params[] = $dateFrom . " 00:00:00";
}
if ($dateTo !== '') {
    $where[] = "l.created_at <= ?";
    $params[] = $dateTo . " 23:59:59";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count total filtered logs
$countSql = "SELECT COUNT(*) as total FROM logs l LEFT JOIN users u ON l.user_id = u.id $whereSQL";
$stmt = $conn->prepare($countSql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$totalLogs = $countResult['total'] ?? 0;
$stmt->close();

$totalPages = ceil($totalLogs / $limit);

// Fetch filtered logs with limit/offset
$sql = "SELECT l.id, u.name, l.action, l.created_at 
        FROM logs l 
        LEFT JOIN users u ON l.user_id = u.id
        $whereSQL
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($params) {
    // Add two integer params for limit and offset
    $types = str_repeat('s', count($params)) . "ii";
    $bind_params = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($types, ...$bind_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$logs = $stmt->get_result();

// Fetch all users for filter dropdown
$usersRes = $conn->query("SELECT id, name FROM users ORDER BY name");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 900px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        a {
            text-decoration: none;
            color: #0066cc;
        }
        a:hover {
            text-decoration: underline;
        }
        .filter-form input, .filter-form select {
            padding: 5px;
            margin-right: 10px;
        }
        .pagination {
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 6px 12px;
            margin-right: 4px;
            border: 1px solid #ccc;
            color: #0066cc;
            text-decoration: none;
            cursor: pointer;
        }
        .pagination .current-page {
            background-color: #0066cc;
            color: white;
            cursor: default;
        }
    </style>
</head>
<body>
    <h2>Activity Logs</h2>
    <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>
    <hr>

    <form method="get" class="filter-form">
        <label for="user">User:</label>
        <select name="user" id="user">
            <option value="">All Users</option>
            <?php while ($u = $usersRes->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>" <?= ($userFilter == $u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="date_from">From:</label>
        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">

        <label for="date_to">To:</label>
        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">

        <button type="submit">Filter</button>
        <a href="activity_logs.php">Reset</a>

        <button type="submit" name="export" value="csv" style="float:right; background:#28a745; color:white; border:none; padding:6px 12px; cursor:pointer;">
            Export CSV
        </button>
    </form>

    <?php if ($logs && $logs->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['id']) ?></td>
                        <td><?= htmlspecialchars($log['name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p == $page): ?>
                    <span class="current-page"><?= $p ?></span>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p>No logs found for selected filters.</p>
    <?php endif; ?>

</body>
</html>
