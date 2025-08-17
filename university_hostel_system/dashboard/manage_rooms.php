<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch hostels for dropdown
$hostels = $conn->query("SELECT * FROM hostels ORDER BY name ASC");

// Add room
if (isset($_POST['add_room'])) {
    $hostel_id = intval($_POST['hostel_id']);
    $room_number = $conn->real_escape_string($_POST['room_number']);
    $conn->query("INSERT INTO rooms (hostel_id, room_no) VALUES ($hostel_id, '$room_number')");
    header("Location: manage_rooms.php");
    exit();
}

// Delete room
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rooms WHERE id = $id");
    header("Location: manage_rooms.php");
    exit();
}

// Fetch rooms with hostel name
$rooms = $conn->query("SELECT r.id, r.room_no, h.name AS hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.id ORDER BY h.name, r.room_no");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Manage Rooms</h2>
    <a href="admin_dashboard.php">Back to Dashboard</a>
    <hr>
    <form method="post">
        <select name="hostel_id" required>
            <option value="" disabled selected>Select Hostel</option>
            <?php while ($h = $hostels->fetch_assoc()): ?>
                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="text" name="room_number" placeholder="Room Number" required>
        <button type="submit" name="add_room">Add Room</button>
    </form>
    <h3>Room List</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Hostel</th>
            <th>Room Number</th>
            <th>Action</th>
        </tr>
        <?php while ($r = $rooms->fetch_assoc()): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['hostel_name']) ?></td>
            <td><?= htmlspecialchars($r['room_no']) ?></td>
            <td>
                <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this room?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>