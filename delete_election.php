<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Election ID is missing.");
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("DELETE FROM elections WHERE election_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin_elections.php?msg=deleted");
    exit();
} else {
    echo "Error deleting election: " . $conn->error;
}
