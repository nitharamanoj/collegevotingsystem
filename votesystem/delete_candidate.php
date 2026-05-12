<?php
session_start();
include 'config.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// ✅ Check if candidate ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_candidates.php?error=invalidid");
    exit();
}

$id = intval($_GET['id']);

// ✅ Delete candidate (votes will auto-delete due to ON DELETE CASCADE)
$stmt = $conn->prepare("DELETE FROM candidates WHERE candidate_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin_candidates.php?deleted=1");
    exit();
} else {
    header("Location: admin_candidates.php?error=deletefail");
    exit();
}
?>
