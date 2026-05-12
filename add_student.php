<?php
session_start();
include 'config.php';

// 🔐 Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// --- PHPMailer and FPDF Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load FPDF
require 'fpdf/fpdf.php'; 
// PHPMailer files will be loaded via the mail_config.php file

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Get the plain-text password from the form
    $department = trim($_POST['department']);
    $yearr = (int)$_POST['yearr'];

    if (empty($student_id) || empty($name) || empty($email) || empty($password) || empty($department) || empty($yearr)) {
        $message = "<div class='alert alert-warning'>All fields are required.</div>";
    } else {
        // Check if student ID or email already exists
        $check_stmt = $conn->prepare("SELECT student_id FROM regestration WHERE student_id = ? OR email = ?");
        $check_stmt->bind_param("is", $student_id, $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $message = "<div class='alert alert-warning'>A student with this ID or email already exists.</div>";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new student
            $insert_stmt = $conn->prepare("INSERT INTO regestration (student_id, name, email, password, department, yearr) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("issssi", $student_id, $name, $email, $hashed_password, $department, $yearr);

            if ($insert_stmt->execute()) {
                // --- PDF and Email Logic ---
                try {
                    // 1. Create the PDF Invoice
                    $pdf = new FPDF();
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', 'B', 16);
                    
                    // Add College Logo
                    $pdf->Image('logo.png', 10, 6, 30);
                    $pdf->Cell(80);
                    
                    // Title
                    $pdf->Cell(30, 10, 'Student Registration Confirmation', 0, 1, 'C');
                    $pdf->Ln(20);

                    // Details
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, 'Registration Date: ' . date('d-m-Y'), 0, 1);
                    $pdf->Ln(10);

                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Student ID:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $student_id, 1, 1);

                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Full Name:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $name, 1, 1);
                    
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Email:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $email, 1, 1);

                    // Add the password row
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Password:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $password, 1, 1); // Use the plain text password here
                    
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Department:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $department, 1, 1);

                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(50, 10, 'Year:', 1, 0);
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(0, 10, $yearr, 1, 1);

                    $pdf->Ln(10);
                    $pdf->MultiCell(0, 10, 'Welcome to the College Voting System! Your account has been successfully created. Your password is included in this document. Please keep it safe.', 0, 'L');
                    
                    // Save PDF to a temporary file
                    $pdf_path = 'invoices/student_' . $student_id . '.pdf';
                    if (!is_dir('invoices')) {
                        mkdir('invoices', 0777, true);
                    }
                    $pdf->Output('F', $pdf_path);

                    // 2. Send the Email with PDF attachment
                    
                    // Load the mail configuration from the separate file
                    $mail = require 'mail_config.php';

                    if ($mail === null) {
                        throw new Exception('Could not initialize mailer configuration.');
                    }

                    // Recipients
                    $mail->setFrom('adithyan.bca23@stcp.ac.in', 'College Admin'); // Set From
                    $mail->addAddress($email, $name); // Add a recipient (the new student)

                    // Attachments
                    $mail->addAttachment($pdf_path);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to the College Voting System!';
                    $mail->Body    = "Hello " . htmlspecialchars($name) . ",<br><br>Your registration is complete. Please find your account details attached in the PDF.<br><br>Thanks,<br>College Administration";
                    
                    $mail->send();
                    
                    // Clean up
                    unlink($pdf_path);

                    $message = "<div class='alert alert-success'>Student added successfully! A confirmation email has been sent.</div>";

                } catch (Exception $e) {
                    $message = "<div class='alert alert-warning'>Student added successfully, but the confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Error: " . $insert_stmt->error . "</div>";
            }
            $insert_stmt->close();
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
    <title>Add Student - VoteEase</title>
    
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

        /* --- Loading Overlay --- */
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 500;
            text-align: center;
        }

        .loader {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid var(--primary); /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* --- End Loading Overlay --- */
    </style>
</head>
<body>

<!-- ==== LOADING OVERLAY ==== -->
<div id="loading-overlay">
    <div class="loader"></div>
    <p>Adding student and sending email... <br> This may take a moment, please wait.</p>
</div>
<!-- ======================= -->

<?php 
include 'sidebar.php'; 
?>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-plus-lg"></i> Add New Student</h2>
    </div>

    <?= $message; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" id="add-student-form">
                <div class="mb-3">
                    <label class="form-label fw-bold">Student ID</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Department</label>
                    <select name="department" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        <?php while ($dept = $departments_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($dept['dept_name']); ?>">
                                <?= htmlspecialchars($dept['dept_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Year</label>
                    <input type="number" name="yearr" class="form-control" min="1" max="10" required>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-success">Add Student</button>
                    <a href="admin_students.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('add-student-form').addEventListener('submit', function() {
    // Show the loading overlay when the form is submitted
    document.getElementById('loading-overlay').style.display = 'flex';
});
</script>

</body>
</html>