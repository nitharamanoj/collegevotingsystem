<?php
// admin_elections.php
session_start();
include 'config.php';

// 🔐 must be logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// fetch all elections
$sql = "SELECT * FROM elections ORDER BY start_date DESC";
$result = $conn->query($sql);

$msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "deleted") {
        $msg = "<div class='alert alert-success'>Election deleted successfully.</div>";
    } elseif ($_GET['msg'] == "added") {
        $msg = "<div class='alert alert-success'>Election added successfully.</div>";
    } elseif ($_GET['msg'] == "updated") {
        $msg = "<div class='alert alert-success'>Election updated successfully.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Elections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Elections Management</h2>

    <?= $msg ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="add_election.php" class="btn btn-success">+ Add Election</a>
        <a href="admin_dashboard.php" class="btn btn-secondary"> Go Back</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['election_id'] ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= $row['start_date'] ?></td>
                        <td><?= $row['end_date'] ?></td>
                        <td>
                            <?php if ($row['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_election.php?id=<?= $row['election_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_election.php?id=<?= $row['election_id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this election?')" 
                               class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No elections found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
