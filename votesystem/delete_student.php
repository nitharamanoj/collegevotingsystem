<?php
session_start();
include 'config.php';

// 🔐 Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if student ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_students.php?error=invalidid");
    exit();
}

$id = (int)$_GET['id'];

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM regestration WHERE student_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin_students.php?msg=deleted");
    exit();
} else {
    // This is for debugging purposes, but you'll need to handle it gracefully in production
    die("Error deleting student: " . $conn->error);
}
?>
