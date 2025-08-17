<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (Admin)</h2>
    <p><a href="../logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a></p>
    <hr>

    <h3>Hostel & Room Management</h3>
    <ul>
        <li><a href="manage_hostels.php">Manage Hostels</a></li>
        <li><a href="manage_rooms.php">Manage Rooms</a></li>
    </ul>

    <h3>User Management</h3>
    <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
    </ul>

    <h3>System Logs</h3>
    <ul>
        <li><a href="view_logs.php">View Activity Logs</a></li>
    </ul>

    <h3>Database Backup</h3>
    <ul>
        <li><a href="backup.php">Download Backup</a></li>
    </ul>
</body>
</html>
