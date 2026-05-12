<?php
session_start();
include 'config.php';

// 🔐 must be logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// fetch all elections
$sql = "SELECT * FROM elections ORDER BY start_date DESC, start_time DESC";
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
    <title>Manage Elections - VoteEase</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* General page layout */
        :root {
            --primary: #4a6cf7;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --bg-body: #f5f6fa;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --text-color: #111827;
            --text-muted: #6b7280;
        }

        body {
            margin: 0;
            background-color: var(--bg-body);
            font-family: "Inter", sans-serif;
            font-size: 14px;
            color: var(--text-color);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
        }
        
        /* Main content area */
        .main-content {
            flex-grow: 1;
            padding: 3rem 2rem;
        }
        
        .main-content h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
            padding: 2rem;
        }

        /* Button styling */
        .btn-primary {
            background-color: var(--primary);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #3b59d8;
            transform: translateY(-1px);
        }
        .btn-outline-primary, .btn-outline-danger {
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            color: var(--card-bg);
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
        }
        .btn-outline-danger:hover {
            color: var(--card-bg);
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        /* Table styling */
        .table {
            border-radius: 1rem;
            overflow: hidden;
        }
        .table thead th {
            font-weight: 600;
            color: var(--text-muted);
            background-color: #f8fafc;
        }
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Badge styling */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Alert styling */
        .alert {
            border: none;
            border-radius: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php 
include 'sidebar.php'; 
?>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Elections Management</h2>
        <div>
            <a href="add_election.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Election</a>
        </div>
    </div>

    <?= $msg ?>

    <div class="card p-4">
        <table class="table align-middle">
            <thead>
                <tr class="table-light">
                    <th>ID</th>
                    <th>Title</th>
                    <th>Start Date & Time</th>
                    <th>End Date & Time</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['election_id'] ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['start_date'])) ?> at <?= date('h:i A', strtotime($row['start_time'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['end_date'])) ?> at <?= date('h:i A', strtotime($row['end_time'])) ?></td>
                            <td>
                                <?php if ($row['status'] == 'Ongoing'): ?>
                                    <span class="badge bg-success">Ongoing</span>
                                <?php elseif ($row['status'] == 'Upcoming'): ?>
                                    <span class="badge bg-info text-dark">Upcoming</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="edit_election.php?id=<?= $row['election_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="delete_election.php?id=<?= $row['election_id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this election?')" 
                                   class="btn btn-sm btn-outline-danger">
                                   <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No elections found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
