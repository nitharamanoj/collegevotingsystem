<?php
session_start();
include 'config.php';

// ✅ Only allow logged-in admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// ✅ Check if id is passed
if (!isset($_GET['id'])) {
    header("Location: admin_candidates.php");
    exit();
}

$id = intval($_GET['id']);

// ✅ Fetch candidate details
$stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$candidate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$candidate) {
    header("Location: admin_candidates.php?error=notfound");
    exit();
}

$message = "";

// ✅ Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $department = $_POST['department'];
    $year = $_POST['year'];
    $position_id = $_POST['position_id'];
    $election_id = $_POST['election_id'];
    $image = $candidate['image']; // Default to current image

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            // Delete old image if it exists
            if (!empty($candidate['image']) && file_exists($candidate['image'])) {
                unlink($candidate['image']);
            }
            $image = $file_path;
        }
    }

    $update = $conn->prepare("UPDATE candidates SET name=?, department=?, year=?, image=?, position_id=?, election_id=? WHERE candidate_id=?");
    $update->bind_param("ssisiii", $name, $department, $year, $image, $position_id, $election_id, $id);

    if ($update->execute()) {
        $message = "<div class='alert alert-success'>Candidate updated successfully!</div>";
        // Refresh candidate data after update
        $candidate['name'] = $name;
        $candidate['department'] = $department;
        $candidate['year'] = $year;
        $candidate['image'] = $image;
        $candidate['position_id'] = $position_id;
        $candidate['election_id'] = $election_id;

    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// ✅ Fetch positions
$positions = $conn->query("SELECT position_id, position_name FROM positions");

// ✅ Fetch elections
$elections = $conn->query("SELECT election_id, title FROM elections");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Candidate - VoteEase</title>
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
        <h2 class="fw-bold"><i class="bi bi-pencil"></i> Edit Candidate</h2>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Candidate Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($candidate['name']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($candidate['department']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Year</label>
                    <input type="number" name="year" value="<?= htmlspecialchars($candidate['year']) ?>" class="form-control" min="1" max="10" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Image</label>
                    <div>
                        <?php if ($candidate['image'] && file_exists($candidate['image'])): ?>
                            <img src="<?= htmlspecialchars($candidate['image']) ?>" alt="Current Candidate Image" style="max-width: 150px; border-radius: 8px;">
                        <?php else: ?>
                            <p class="text-muted">No image uploaded.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Upload New Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="form-text text-muted">Leave blank to keep the current image.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Position</label>
                    <select name="position_id" class="form-select" required>
                        <?php while($p = $positions->fetch_assoc()): ?>
                            <option value="<?= $p['position_id'] ?>" 
                                <?= ($candidate['position_id'] == $p['position_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['position_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Election</label>
                    <select name="election_id" class="form-select" required>
                        <?php while($e = $elections->fetch_assoc()): ?>
                            <option value="<?= $e['election_id'] ?>" 
                                <?= ($candidate['election_id'] == $e['election_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['title']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Update Candidate</button>
                    <a href="admin_candidates.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
