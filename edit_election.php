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
$message = "";

// Fetch election
$stmt = $conn->prepare("SELECT * FROM elections WHERE election_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election) {
    die("Election not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE elections SET title=?, start_date=?, end_date=?, status=? WHERE election_id=?");
    $update->bind_param("ssssi", $title, $start_date, $end_date, $status, $id);

    if ($update->execute()) {
        $message = "<div class='alert alert-success'>Election updated successfully!</div>";
        // refresh election details
        $stmt->execute();
        $election = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Election - VoteEase</title>
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
        
        /* Form styling */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: var(--border-color);
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.25);
            border-color: var(--primary);
        }
        
        /* Button styling */
        .btn-success {
            background-color: var(--success);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }
        .btn-secondary {
            border-radius: 0.5rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            transform: translateY(-1px);
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
        <h2 class="fw-bold"><i class="bi bi-pencil"></i> Edit Election</h2>
    </div>

    <?= $message ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($election['title']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label fw-bold">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $election['start_date'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label fw-bold">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $election['end_date'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" <?= $election['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $election['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="admin_elections.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
