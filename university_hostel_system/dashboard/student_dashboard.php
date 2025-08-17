<?php
session_start();
include '../includes/db.php';

// Only allow students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$message = "";

// Handle room application submission
if (isset($_POST['apply_room'])) {
    $preferred_hostel = $conn->real_escape_string($_POST['preferred_hostel']);
    $preferred_room_type = $conn->real_escape_string($_POST['preferred_room_type']);
    $hostel_reason = $conn->real_escape_string($_POST['hostel_reason']);
    $medical_needs = $conn->real_escape_string($_POST['medical_needs']);
    $roommate_preference = $conn->real_escape_string($_POST['roommate_preference']);

    // Check if already applied and pending
    $check = $conn->query("SELECT * FROM room_applications WHERE student_id = $student_id AND status = 'pending'");
    if ($check->num_rows > 0) {
        $message = "You already have a pending application.";
    } else {
        $conn->query("INSERT INTO room_applications 
            (student_id, preferred_hostel, preferred_room_type, hostel_reason, medical_needs, roommate_preference) 
            VALUES 
            ($student_id, '$preferred_hostel', '$preferred_room_type', '$hostel_reason', '$medical_needs', '$roommate_preference')");
        $message = "Application submitted!";
    }
}

// Fetch latest application
$app = $conn->query("SELECT * FROM room_applications WHERE student_id = $student_id ORDER BY applied_at DESC LIMIT 1");

// Fetch allocated room for this student
$allocation = $conn->query("
    SELECT h.name AS hostel_name, r.room_no
    FROM allocations a
    JOIN rooms r ON a.room_id = r.id
    JOIN hostels h ON r.hostel_id = h.id
    WHERE a.student_id = $student_id
    LIMIT 1
");

// Handle issue reporting
if (isset($_POST['report_issue'], $_POST['issue'])) {
    $desc = $conn->real_escape_string($_POST['issue']);
    $conn->query("INSERT INTO issues (student_id, description, status, created_at) VALUES ($student_id, '$desc', 'pending', NOW())");
    header("Location: student_dashboard.php");
    exit();
}

// Fetch reported issues
$result = $conn->query("SELECT * FROM issues WHERE student_id = $student_id ORDER BY reported_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>

    <!-- Reported Issues -->
    <h3>Your Reported Issues</h3>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($issue = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($issue['id']) ?></td>
                        <td><?= htmlspecialchars($issue['description']) ?></td>
                        <td><?= htmlspecialchars($issue['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No issues reported yet.</p>
    <?php endif; ?>

    <!-- Room Application -->
    <h2>Apply for Hostel Room</h2>
    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="preferred_hostel">Preferred Hostel:</label>
        <input type="text" name="preferred_hostel" id="preferred_hostel" required><br><br>

        <label for="preferred_room_type">Preferred Room Type:</label>
        <select name="preferred_room_type" id="preferred_room_type" required>
            <option value="" disabled selected>Select room type</option>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Triple">Triple</option>
        </select><br><br>

        <label for="hostel_reason">Reason for hostel stay:</label>
        <textarea name="hostel_reason" id="hostel_reason" rows="3" required></textarea><br><br>

        <label for="medical_needs">Do you have any medical needs? (optional)</label>
        <input type="text" name="medical_needs" id="medical_needs"><br><br>

        <label for="roommate_preference">Roommate preference (optional):</label>
        <input type="text" name="roommate_preference" id="roommate_preference"><br><br>

        <button type="submit" name="apply_room">Apply</button>
    </form>

    <!-- Latest Application -->
    <?php if ($app && $app->num_rows > 0): $row = $app->fetch_assoc(); ?>
        <h3>Your Latest Room Application</h3>
        <ul>
            <li><strong>Preferred Hostel:</strong> <?= htmlspecialchars($row['preferred_hostel']) ?></li>
            <li><strong>Room Type:</strong> <?= htmlspecialchars($row['preferred_room_type']) ?></li>
            <li><strong>Reason:</strong> <?= htmlspecialchars($row['hostel_reason']) ?></li>
            <li><strong>Medical Needs:</strong> <?= htmlspecialchars($row['medical_needs']) ?></li>
            <li><strong>Roommate Preference:</strong> <?= htmlspecialchars($row['roommate_preference']) ?></li>
            <li><strong>Status:</strong> <?= ucfirst($row['status']) ?></li>
            <li><strong>Applied At:</strong> <?= htmlspecialchars($row['applied_at']) ?></li>
        </ul>
    <?php endif; ?>

    <!-- Allocated Room -->
    <?php if ($allocation && $allocation->num_rows > 0): $room = $allocation->fetch_assoc(); ?>
        <h3>Your Allocated Room</h3>
        <ul>
            <li><strong>Hostel:</strong> <?= htmlspecialchars($room['hostel_name']) ?></li>
            <li><strong>Room Number:</strong> <?= htmlspecialchars($room['room_no']) ?></li>
        </ul>
    <?php else: ?>
        <p>You have not been allocated a room yet.</p>
    <?php endif; ?>

    <!-- Report Issue -->
    <h2>Report a Maintenance Issue</h2>
    <form method="post">
        <label for="issue">Issue Description:</label>
        <input type="text" name="issue" id="issue" required>
        <button type="submit" name="report_issue">Report</button>
    </form>

    <p><a href="../logout.php">Logout</a></p>
</body>
</html>

<?php
$conn->close();
?>
