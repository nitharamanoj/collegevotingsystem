<?php
// Set the time zone to ensure accurate comparisons
date_default_timezone_set('Asia/Kolkata'); // Replace 'Asia/Kolkata' with your server's timezone

// Get a fresh connection if one isn't available
if (!isset($conn) || $conn->connect_error) {
    include 'config.php';
}

$current_datetime = date('Y-m-d H:i:s');

// Update elections that should be starting
$start_sql = "UPDATE elections SET status = 'Ongoing' WHERE CONCAT(start_date, ' ', start_time) <= ? AND status = 'Upcoming'";
$start_stmt = $conn->prepare($start_sql);
if ($start_stmt) {
    $start_stmt->bind_param("s", $current_datetime);
    $start_stmt->execute();
    $start_stmt->close();
}


// Update elections that should be ending
$end_sql = "UPDATE elections SET status = 'Completed' WHERE CONCAT(end_date, ' ', end_time) <= ? AND status = 'Ongoing'";
$end_stmt = $conn->prepare($end_sql);
if ($end_stmt) {
    $end_stmt->bind_param("s", $current_datetime);
    $end_stmt->execute();
    $end_stmt->close();
}

// Do not close the connection here, as this script is included in other files.
// The main script will handle closing the connection at the end of its execution.
?>
