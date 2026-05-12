<?php
session_start();
include 'config.php';

// 🔐 Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "added") {
        $message = "<div class='alert alert-success'>Student registered successfully.</div>";
    }
}

// Fetch all students
$sql = "SELECT student_id, name, department, yearr FROM regestration ORDER BY name ASC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - VoteEase</title>
    
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
        <h2 class="fw-bold">Students Management</h2>
        <a href="add_student.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Student</a>
    </div>

    <?= $message; ?>

    <div class="card p-4">
        <table class="table align-middle">
            <thead>
                <tr class="table-light">
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['yearr']) ?></td>
                            <td class="text-end">
                                <a href="edit_student.php?id=<?= $row['student_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="delete_student.php?id=<?= $row['student_id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this student?')"
                                   class="btn btn-sm btn-outline-danger">
                                   <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
