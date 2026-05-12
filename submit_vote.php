<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Invalid request method.";
    exit();
}

$student_id = (int)$_SESSION['student_id'];
$election_id = isset($_POST['election_id']) ? (int)$_POST['election_id'] : 0;
$votes = isset($_POST['vote']) ? $_POST['vote'] : [];

// Check if the student has already voted in this election
$voted_stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE student_id = ? AND election_id = ?");
$voted_stmt->bind_param("ii", $student_id, $election_id);
$voted_stmt->execute();
$has_voted = $voted_stmt->get_result()->fetch_row()[0] > 0;
$voted_stmt->close();

if ($has_voted) {
    echo "Error: You have already voted in this election.";
    exit();
}

// Check if votes and election ID are valid
if (empty($votes) || $election_id <= 0) {
    echo "Error: Invalid vote submission.";
    exit();
}

// Begin a transaction to ensure atomicity
$conn->begin_transaction();
$success = true;

// Prepare the SQL INSERT statement
$insert_stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id, election_id) VALUES (?, ?, ?)");

if ($insert_stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Loop through the submitted votes and insert them
foreach ($votes as $position_id => $candidate_id) {
    // Bind parameters and execute
    $insert_stmt->bind_param("iii", $student_id, $candidate_id, $election_id);
    if (!$insert_stmt->execute()) {
        $success = false;
        break; // Exit the loop on the first failure
    }
}

// Close the prepared statement
$insert_stmt->close();

// Commit the transaction if successful, or roll back if an error occurred
if ($success) {
    $conn->commit();
    // Start of the success interface HTML from vote_success.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Confirmed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family:"Inter",sans-serif; background:#f5f7fb; margin:0; }
        .container { 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 30px; 
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        h2 {
            font-weight: 700;
            color: #111827;
        }
        .btn-primary {
            background-color: #4a6cf7;
            border: none;
        }
        .btn-primary:hover {
            background-color: #3b59d8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="success-icon">&check;</div>
    <h2>Vote Cast Successfully!</h2>
    <p class="text-muted mb-4">Thank you for participating in the election.</p>
    <a href="student_dashboard.php" class="btn btn-primary btn-lg">Return to Dashboard</a>
</div>

</body>
</html>
<?php
    // End of the success interface HTML
} else {
    $conn->rollback();
    echo "Error: Failed to submit your vote. Please try again.";
}

// Close the database connection
$conn->close();

?>