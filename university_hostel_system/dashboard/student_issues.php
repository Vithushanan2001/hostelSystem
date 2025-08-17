<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Get student ID
$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student record not found.");
}

$student_id = $student['id'];

// Handle new issue submission
if (isset($_POST['issue_type'], $_POST['description'])) {
    $type = $_POST['issue_type'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO issues (student_id, issue_type, description, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iss", $student_id, $type, $desc);
    if ($stmt->execute()) {
        echo "<script>alert('Issue reported successfully!'); window.location='student_issues.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Get student's issues
$stmt = $conn->prepare("SELECT issue_type, description, status, created_at FROM issues WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$issues = $stmt->get_result();
$stmt->close();
?>

<h2>Report an Issue</h2>
<form method="post" action="">
    <label>Issue Type:</label>
    <select name="issue_type" required>
        <option value="Water Leakage">Water Leakage</option>
        <option value="Electricity">Electricity</option>
        <option value="Furniture">Furniture</option>
        <option value="Bathroom">Bathroom</option>
        <option value="Others">Others</option>
    </select><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="4" cols="50" required></textarea><br><br>

    <button type="submit">Submit Issue</button>
</form>

<hr>

<h2>My Reported Issues</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Type</th>
        <th>Description</th>
        <th>Status</th>
        <th>Reported On</th>
    </tr>
    <?php while ($i = $issues->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($i['issue_type']) ?></td>
            <td><?= htmlspecialchars($i['description']) ?></td>
            <td><?= htmlspecialchars(ucfirst($i['status'])) ?></td>
            <td><?= htmlspecialchars($i['created_at']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>
