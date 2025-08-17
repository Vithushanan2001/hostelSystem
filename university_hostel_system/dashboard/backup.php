<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'your_database_name'; // <-- Change this to your DB name

$backup_file = "backup_" . date("Ymd_His") . ".sql";
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $backup_file);

$command = "mysqldump --user=$user --password=$pass --host=$host $db";
system($command);
exit;
?>