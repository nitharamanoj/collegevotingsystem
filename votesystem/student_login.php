<?php
session_start();
include 'config.php';

$error = "";

// Check if a student is already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    if (empty($student_id) || empty($password)) {
        $error = "Student ID and password are required.";
    } else {
        // Fetch student record from the database
        $stmt = $conn->prepare("SELECT student_id, password FROM regestration WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            // Verify the password against the stored hash
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['student_id'];
                header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Invalid student ID or password.";
            }
        } else {
            $error = "Invalid student ID or password.";
        }

        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6cf7;
            --bg-body: #f5f6fa;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --text-color: #111827;
        }

        body {
            background-color: var(--bg-body);
            font-family: "Inter", sans-serif;
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
        }

        .login-card h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-color);
            text-align: center;
        }
        
        .form-control {
            border-radius: 0.5rem;
            border-color: var(--border-color);
            transition: all 0.2s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.25);
            border-color: var(--primary);
        }

        .btn-primary {
            width: 100%;
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

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Student Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="student_login.php" method="POST">
            <div class="mb-3">
                <input type="text" name="student_id" class="form-control" placeholder="Student ID" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
    </div>
</body>
</html>
