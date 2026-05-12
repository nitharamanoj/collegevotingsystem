<?php
session_start();
include 'config.php';

// ✅ Ensure at least one admin exists (with NAME, per your DB)
$checkAdmin = $conn->query("SELECT COUNT(*) AS cnt FROM admins");
$row = $checkAdmin->fetch_assoc();
if ((int)$row['cnt'] === 0) {
    $defaultEmail = "admin@gmail.com";
    $defaultPass  = md5("1234"); // default password = 1234
    $defaultName  = "Super Admin";
    $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $defaultName, $defaultEmail, $defaultPass);
    $stmt->execute();
    $stmt->close();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type  = $_POST['login_type'] ?? '';
    $id_or_email = trim($_POST['id_or_email'] ?? '');
    $password    = trim($_POST['password'] ?? '');

    if ($login_type === "student") {
        $id_int = (int)$id_or_email;
        $stmt = $conn->prepare("SELECT * FROM regestration WHERE student_id = ?");
        $stmt->bind_param("i", $id_int);
    } elseif ($login_type === "admin") {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $id_or_email);
    } else {
        $stmt = null;
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $ok = false;
            
            // Check for password
            // For student login, use password_verify
            if ($login_type === "student") {
                if (password_verify($password, $user['password'])) {
                    $ok = true;
                }
            } 
            // For admin login, check both new and old password methods
            elseif ($login_type === "admin") {
                if (password_verify($password, $user['password'])) {
                    $ok = true;
                }
                // Fallback for old MD5 passwords for convenience
                else if (md5($password) === $user['password']) {
                    $ok = true;
                }
            }

            if ($ok) {
                if ($login_type === "student") {
                    $_SESSION['student_id']   = $user['student_id'];
                    $_SESSION['student_name'] = $user['name'];
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    $_SESSION['admin_id']    = $user['id'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_name']  = $user['name'];
                    header("Location: admin_dashboard.php");
                    exit();
                }
            } else {
                $error = "❌ Incorrect password.";
            }
        } else {
            $error = ucfirst($login_type) . " not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>College Voting System - Login</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: "Segoe UI", sans-serif;
      background: #eef3fc;
      display: flex; justify-content: center; align-items: center;
      min-height: 100vh; margin: 0;
    }
    .container { text-align: center; }
    .form-box {
      background: #fff; padding: 30px; border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.05);
      width: 350px; margin: 0 auto;
    }
    .toggle-buttons { display: flex; justify-content: center; background: #f1f5fb; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
    .toggle-buttons button { flex: 1; padding: 10px; border: none; background: transparent; cursor: pointer; font-weight: 600; }
    .toggle-buttons button.active { background: #fff; border-bottom: 2px solid #4a90e2; }
    .form-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 5px; }
    .form-subtitle { font-size: 0.9rem; color: #666; margin-bottom: 20px; }
    input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; }
    .btn { width: 100%; padding: 12px; background: #0d0d28; color: #fff; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; }
    .admin-btn { background: #d8dbe1; color: #000; }
    .contact { margin-top: 15px; font-size: 0.85rem; color: #777; }
    .error { color: red; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo"><img src="logo.png" height="90" width="90" /></div>
    <h2>College Voting System</h2>
    <p>Secure Digital Elections</p>

    <div class="form-box">
      <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

      <div class="toggle-buttons">
        <button id="studentBtn" class="active" onclick="toggleForm('student')">Student</button>
        <button id="adminBtn" onclick="toggleForm('admin')">Admin</button>
      </div>

      <!-- Student Login -->
      <div id="studentForm">
        <div class="form-title">Student Login</div>
        <div class="form-subtitle">Use your Student ID to access voting</div>
        <form method="post" action="">
          <input type="hidden" name="login_type" value="student">
          <input type="text" name="id_or_email" placeholder="Enter your Student ID" required>
          <input type="password" name="password" placeholder="Enter your Password" required>
          <button type="submit" class="btn">Login to Vote</button>
        </form>
      </div>

      <!-- Admin Login -->
      <div id="adminForm" style="display: none;">
        <div class="form-title">Admin Access</div>
        <div class="form-subtitle">Use your Email to log in</div>
        <form method="post" action="">
          <input type="hidden" name="login_type" value="admin">
          <input type="email" name="id_or_email" placeholder="Admin Email" required>
          <input type="password" name="password" placeholder="Enter Admin Password" required>
          <button type="submit" class="btn admin-btn">Admin Login</button>
        </form>
      </div>

      <p class="contact">Need help? Contact Student Affairs</p>
    </div>
  </div>

  <script>
    function toggleForm(type) {
      const studentForm = document.getElementById('studentForm');
      const adminForm = document.getElementById('adminForm');
      const studentBtn = document.getElementById('studentBtn');
      const adminBtn = document.getElementById('adminBtn');

      if (type === 'student') {
        studentForm.style.display = 'block';
        adminForm.style.display = 'none';
        studentBtn.classList.add('active');
        adminBtn.classList.remove('active');
      } else {
        studentForm.style.display = 'none';
        adminForm.style.display = 'block';
        studentBtn.classList.remove('active');
        adminBtn.classList.add('active');
      }
    }
  </script>
</body>
</html>
