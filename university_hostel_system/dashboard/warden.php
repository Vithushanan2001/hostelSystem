<?php
// Show errors for debugging - remove or comment out in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/db.php';

// Check login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Warden';

// Variables for messages
$updateMessage = '';
$allocationMessage = '';
$appActionMessage = '';

// Handle status update for issues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_id'], $_POST['status'])) {
    $issue_id = intval($_POST['issue_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE issues SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $issue_id);

    if ($stmt->execute()) {
        $updateMessage = "✅ Issue #$issue_id status updated to '$status'.";
    } else {
        $updateMessage = "❌ Failed to update issue.";
    }
    $stmt->close();
}

// Handle room allocation with mapping to students.id
if (isset($_POST['allocate_room'], $_POST['allocate_student_id'], $_POST['room_id'])) {
    $user_id_to_allocate = intval($_POST['allocate_student_id']); // This is from room_applications.student_id (users.id)
    $room_id = intval($_POST['room_id']);

    // Find corresponding student_id in students table (assuming students.user_id FK to users.id)
    $stmtStudent = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmtStudent->bind_param("i", $user_id_to_allocate);
    $stmtStudent->execute();
    $stmtStudent->bind_result($student_id);
    if ($stmtStudent->fetch()) {
        $stmtStudent->close();

        // Insert allocation with student_id from students table
        $stmtAlloc = $conn->prepare("INSERT INTO allocations (student_id, room_id, allocated_at) VALUES (?, ?, NOW())");
        $stmtAlloc->bind_param("ii", $student_id, $room_id);

        if ($stmtAlloc->execute()) {
            $allocationMessage = "✅ Room allocated successfully to student ID $student_id.";
        } else {
            $allocationMessage = "❌ Room allocation failed.";
        }
        $stmtAlloc->close();
    } else {
        $allocationMessage = "❌ No student record found for this user ID.";
        $stmtStudent->close();
    }
}

// Handle application approval/rejection
if (isset($_POST['action'], $_POST['app_id'])) {
    $app_id = intval($_POST['app_id']);
    $action = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE room_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $app_id);
    if ($stmt->execute()) {
        $appActionMessage = "✅ Application ID $app_id has been $action.";
    } else {
        $appActionMessage = "❌ Failed to update application status.";
    }
    $stmt->close();
}

// Fetch all issues with student names
$result = $conn->query("
    SELECT issues.*, users.name AS student_name 
    FROM issues 
    JOIN users ON issues.student_id = users.id 
    ORDER BY created_at DESC
");

// Fetch pending applications — case-insensitive check for 'pending'
$pending_apps = $conn->query("
    SELECT ra.*, u.name AS student_name
    FROM room_applications ra
    JOIN users u ON ra.student_id = u.id
    WHERE LOWER(ra.status) = 'pending'
    ORDER BY ra.applied_at ASC
");

// Fetch approved applications without allocation
$approved = $conn->query("
    SELECT ra.*, u.name AS student_name
    FROM room_applications ra
    JOIN users u ON ra.student_id = u.id
    WHERE LOWER(ra.status) = 'approved'
      AND ra.student_id NOT IN (SELECT student_id FROM allocations)
    ORDER BY ra.applied_at ASC
");

// Fetch available rooms
$rooms = $conn->query("
    SELECT r.id, r.room_no, h.name AS hostel_name
    FROM rooms r
    JOIN hostels h ON r.hostel_id = h.id
    WHERE r.id NOT IN (SELECT room_id FROM allocations)
");

$room_options = [];
while ($room = $rooms->fetch_assoc()) {
    $room_options[] = [
        'id' => $room['id'],
        'label' => $room['hostel_name'] . ' - Room ' . $room['room_no']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warden Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($user_name) ?> (Warden)</h2>
    <p><a href="../logout.php">Logout</a></p>
    <hr>

    <?php if ($updateMessage): ?>
        <p style="color:green;"><?= htmlspecialchars($updateMessage) ?></p>
    <?php endif; ?>

    <?php if ($allocationMessage): ?>
        <p style="color:blue;"><?= htmlspecialchars($allocationMessage) ?></p>
    <?php endif; ?>

    <?php if ($appActionMessage): ?>
        <p style="color:orange;"><?= htmlspecialchars($appActionMessage) ?></p>
    <?php endif; ?>

    <h3>All Reported Issues</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="issue_id" value="<?= $row['id'] ?>">
                    <select name="status" required>
                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $row['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= $row['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Pending Room Applications</h2>

    <p><em>Pending applications count: <?= $pending_apps->num_rows ?></em></p>

    <?php if ($pending_apps->num_rows > 0): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Student</th>
            <th>Preferred Hostel</th>
            <th>Room Type</th>
            <th>Applied At</th>
            <th>Action</th>
        </tr>
        <?php while ($app = $pending_apps->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($app['student_name']) ?></td>
            <td><?= htmlspecialchars($app['preferred_hostel']) ?></td>
            <td><?= htmlspecialchars($app['preferred_room_type']) ?></td>
            <td><?= htmlspecialchars($app['applied_at']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                    <button type="submit" name="action" value="approve" style="background:green;color:white;">Approve</button>
                    <button type="submit" name="action" value="reject" style="background:red;color:white;">Reject</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No pending applications.</p>
    <?php endif; ?>

    <h2>Approved Applications (Assign Room)</h2>
    <?php if ($approved->num_rows > 0): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Student</th>
            <th>Preferred Hostel</th>
            <th>Room Type</th>
            <th>Applied At</th>
            <th>Assign Room</th>
        </tr>
        <?php while ($app = $approved->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($app['student_name']) ?></td>
            <td><?= htmlspecialchars($app['preferred_hostel']) ?></td>
            <td><?= htmlspecialchars($app['preferred_room_type']) ?></td>
            <td><?= htmlspecialchars($app['applied_at']) ?></td>
            <td>
                <?php if (count($room_options) > 0): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="allocate_student_id" value="<?= $app['student_id']; ?>">
                    <select name="room_id" required>
                        <option value="" disabled selected>Select room</option>
                        <?php foreach ($room_options as $r): ?>
                            <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="allocate_room" style="background:blue;color:white;">Assign</button>
                </form>
                <?php else: ?>
                    <em>No rooms available</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p><em>No approved applications awaiting allocation.</em></p>
    <?php endif; ?>
</body>
</html>
