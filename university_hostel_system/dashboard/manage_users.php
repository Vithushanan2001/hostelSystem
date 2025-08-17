<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$addMessage = '';
$deleteMessage = '';

// Handle add user
if (isset($_POST['add_user'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($name) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $addMessage = "❌ Email already registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            if ($stmt->execute()) {
                $addMessage = "✅ User added successfully.";
            } else {
                $addMessage = "❌ Failed to add user.";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $addMessage = "❌ Invalid input.";
    }
}

// Handle delete user (POST)
if (isset($_POST['delete_user'])) {
    $id = intval($_POST['delete_user']);

    // Optional: Prevent admin from deleting self
    if ($id === intval($_SESSION['user_id'])) {
        $deleteMessage = "❌ You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $deleteMessage = "✅ User deleted successfully.";
        } else {
            $deleteMessage = "❌ Failed to delete user.";
        }
        $stmt->close();
    }
}

// Fetch users
$users = $conn->query("SELECT id, name, email, role FROM users ORDER BY role, name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        form { margin-bottom: 20px; }
        input, select { padding: 6px; margin-right: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { padding: 6px 12px; }
    </style>
    <script>
        function confirmDelete(name) {
            return confirm("Are you sure you want to delete user '" + name + "'?");
        }
    </script>
</head>
<body>
    <h2>Manage Users</h2>
    <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>

    <?php if ($addMessage): ?>
        <p><?= htmlspecialchars($addMessage) ?></p>
    <?php endif; ?>

    <?php if ($deleteMessage): ?>
        <p><?= htmlspecialchars($deleteMessage) ?></p>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('Add this user?');">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="student">Student</option>
            <option value="warden">Warden</option>
            <option value="admin">Admin</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <h3>Existing Users</h3>
    <?php if ($users->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                <td>
                    <form method="post" style="display:inline;" 
                          onsubmit="return confirmDelete('<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>');">
                        <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                        <button type="submit" style="background:#c00; color:#fff; border:none; padding:4px 8px; cursor:pointer;">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</body>
</html>
