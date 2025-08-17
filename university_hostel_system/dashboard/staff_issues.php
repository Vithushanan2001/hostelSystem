<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$message = "";

// Handle status/remarks update
if (isset($_POST['update_issue'], $_POST['issue_id'], $_POST['status'])) {
    $issue_id = intval($_POST['issue_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    if ($conn->query("UPDATE issues SET status='$status', remarks='$remarks' WHERE id=$issue_id")) {
        $message = "Issue updated successfully.";
    } else {
        $message = "Failed to update issue.";
    }
}

// Fetch assigned issues
$issues = $conn->query("
    SELECT i.*, u.name AS student_name, u.registration_no
    FROM issues i
    JOIN users u ON i.student_id = u.id
    WHERE i.staff_id = $staff_id
    ORDER BY i.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff - Issue Tracker</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Assigned Issues</h2>
    <a href="../logout.php">Logout</a>
    <?php if ($message): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>
    <hr>

    <?php if ($issues->num_rows > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Room No</th>
                <th>Description</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Reported At</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $issues->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['room_no']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="issue_id" value="<?= $row['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in progress" <?= $row['status'] == 'in progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="resolved" <?= $row['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        </select>
                        <input type="text" name="remarks" value="<?= htmlspecialchars($row['remarks']) ?>" placeholder="Remarks">
                        <button type="submit" name="update_issue">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No issues assigned to you.</p>
    <?php endif; ?>
</body>
</html>
