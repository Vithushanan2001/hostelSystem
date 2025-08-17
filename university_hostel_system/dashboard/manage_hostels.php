<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add hostel
if (isset($_POST['add_hostel'])) {
    $name = $conn->real_escape_string($_POST['hostel_name']);
    $conn->query("INSERT INTO hostels (name) VALUES ('$name')");
    header("Location: manage_hostels.php");
    exit();
}

// Delete hostel
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM hostels WHERE id = $id");
    header("Location: manage_hostels.php");
    exit();
}

// Fetch hostels
$hostels = $conn->query("SELECT * FROM hostels ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Hostels</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Manage Hostels</h2>
    <a href="admin_dashboard.php">Back to Dashboard</a>
    <hr>
    <form method="post">
        <input type="text" name="hostel_name" placeholder="Hostel Name" required>
        <button type="submit" name="add_hostel">Add Hostel</button>
    </form>
    <h3>Hostel List</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
        <?php while ($h = $hostels->fetch_assoc()): ?>
        <tr>
            <td><?= $h['id'] ?></td>
            <td><?= htmlspecialchars($h['name']) ?></td>
            <td>
                <a href="?delete=<?= $h['id'] ?>" onclick="return confirm('Delete this hostel?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>