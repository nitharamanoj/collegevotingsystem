<?php
session_start();
include 'config.php';

// 🔐 Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if student ID is passed
if (!isset($_GET['id'])) {
    header("Location: admin_students.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";

// Fetch student details
$stmt = $conn->prepare("SELECT student_id, name, email, department, yearr FROM regestration WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $yearr = (int)$_POST['yearr'];
    $password = $_POST['password'];

    // Check if email already exists for another student
    $check_email = $conn->prepare("SELECT COUNT(*) FROM regestration WHERE email = ? AND student_id != ?");
    $check_email->bind_param("si", $email, $id);
    $check_email->execute();
    $email_exists = $check_email->get_result()->fetch_row()[0] > 0;
    $check_email->close();

    if ($email_exists) {
        $message = "<div class='alert alert-warning'>This email is already used by another student.</div>";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE regestration SET name=?, email=?, password=?, department=?, yearr=? WHERE student_id=?");
            $update_stmt->bind_param("ssssii", $name, $email, $hashed_password, $department, $yearr, $id);
        } else {
            $update_stmt = $conn->prepare("UPDATE regestration SET name=?, email=?, department=?, yearr=? WHERE student_id=?");
            $update_stmt->bind_param("sssii", $name, $email, $department, $yearr, $id);
        }

        if ($update_stmt->execute()) {
            $message = "<div class='alert alert-success'>Student updated successfully!</div>";
            // Refresh details after update
            $stmt = $conn->prepare("SELECT student_id, name, email, department, yearr FROM regestration WHERE student_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// Fetch departments for dropdown
$departments_result = $conn->query("SELECT dept_name FROM departments ORDER BY dept_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - VoteEase</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
        .main-content {
            flex-grow: 1;
            padding: 3rem 2rem;
        }
        .main-content h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-color);
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
            padding: 2rem;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: var(--border-color);
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.25);
            border-color: var(--primary);
        }
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
        .alert {
            border: none;
            border-radius: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-person-fill-gear"></i> Edit Student</h2>
    </div>

    <?= $message ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Student ID</label>
                    <input type="text" name="student_id" class="form-control" value="<?= htmlspecialchars($student['student_id']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">New Password (optional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Department</label>
                    <select name="department" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($dept['dept_name']); ?>" <?= $student['department'] == $dept['dept_name'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dept['dept_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Year</label>
                    <input type="number" name="yearr" class="form-control" value="<?= htmlspecialchars($student['yearr']); ?>" min="1" max="10" required>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-success">Update Student</button>
                    <a href="admin_students.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
