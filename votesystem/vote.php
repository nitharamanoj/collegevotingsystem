<?php
session_start();
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$election_id = isset($_GET['election_id']) ? (int)$_GET['election_id'] : 0;
$student_id = (int)$_SESSION['student_id'];

$election_info = $conn->prepare("SELECT title FROM elections WHERE election_id = ?");
$election_info->bind_param("i", $election_id);
$election_info->execute();
$election_title = $election_info->get_result()->fetch_assoc()['title'];
$election_info->close();



if (!isset($_SESSION['otp_verified_'.$election_id]) && !isset($_SESSION['otp_sent_'.$election_id])) {

    // Generate new OTP only once
    $otp = rand(100000, 999999);
    $_SESSION['otp_'.$election_id] = $otp;
    $_SESSION['otp_sent_'.$election_id] = true;


    // Fetch student email
    $stmt = $conn->prepare("SELECT email, name FROM regestration WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // ✅ Send OTP Email
    require 'mail_config.php';
   

    try {
        $mail->setFrom('adithyan.bca23@stcp.ac.in', 'VoteEase Verification');
        $mail->addAddress($student['email'], $student['name']);

        $mail->isHTML(true);
        $mail->Subject = "OTP Verification for Voting - {$election_title}";
        $mail->Body = "
            Hello <b>{$student['name']}</b>,<br><br>
            Your OTP for voting in <b>{$election_title}</b> is:<br>
            <h2 style='color:#4a6cf7;'>$otp</h2>
            This OTP is valid for one attempt only.<br><br>
            Regards,<br>VoteEase
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Error sending OTP email: " . $mail->ErrorInfo;
        exit();
    }
}

// ✅ STEP 2: VERIFY OTP (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered = trim($_POST['entered_otp']);
    $saved = $_SESSION['otp_'.$election_id];

    if ($entered == $saved) {
        $_SESSION['otp_verified_'.$election_id] = true; // allow voting
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}

// ✅ STOP PAGE UNTIL OTP VERIFIED
if (!isset($_SESSION['otp_verified_'.$election_id])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>OTP Verification</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container mt-5" style="max-width:500px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center">OTP Verification</h3>
            <p class="text-muted text-center">
                An OTP has been sent to your registered email.
            </p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="verify_otp" value="1">
                <label class="form-label">Enter OTP</label>
                <input type="text" name="entered_otp" class="form-control mb-3" required>

                <button class="btn btn-primary w-100">Verify OTP</button>
            </form>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// ✅ STEP 3: PREVENT MULTIPLE VOTES
$voted_stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE student_id = ? AND election_id = ?");
$voted_stmt->bind_param("ii", $student_id, $election_id);
$voted_stmt->execute();
$has_voted = $voted_stmt->get_result()->fetch_row()[0] > 0;
$voted_stmt->close();

if ($has_voted) {
    echo "You have already voted in this election.";
    exit();
}

// ✅ STEP 4: FETCH POSITIONS + CANDIDATES (same as your original)
$positions = [];
$pos_query = "SELECT p.position_id, p.position_name, c.candidate_id, c.name, c.department, c.year, c.image
              FROM positions p
              JOIN candidates c ON p.position_id = c.position_id
              WHERE c.election_id = ?
              ORDER BY FIELD(p.position_name,
              'Chairman/Chairperson','Vice Chairperson (FE)','General Secretary',
              'UUC - University Union Councillor','Arts Club Secretary','Magazine Editor',
              'Lady Representative','2nd PG Rep','1st PG Rep',
              '3rd UG Rep','2nd UG Rep','1st UG Rep'), c.name";

$pos_stmt = $conn->prepare($pos_query);
$pos_stmt->bind_param("i", $election_id);
$pos_stmt->execute();
$result = $pos_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $positions[$row['position_name']][] = $row;
}

$pos_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote - <?= htmlspecialchars($election_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f5f7fb; font-family: "Inter"; }
        .candidate-card { border:1px solid #ddd; padding:15px; border-radius:10px; cursor:pointer; }
        .candidate-card.selected { border-color:#4a6cf7; background:#eef2ff; }
        .candidate-card img { width:80px; height:80px; border-radius:50%; object-fit:cover; }
    </style>
</head>
<body>

<div class="container mt-4">

    <h2 class="fw-bold mb-4">Vote for <?= htmlspecialchars($election_title); ?></h2>

    <form action="submit_vote.php" method="POST">
        <input type="hidden" name="election_id" value="<?= $election_id ?>">
        <input type="hidden" name="student_id" value="<?= $student_id ?>">

        <?php foreach ($positions as $position_name => $candidates): ?>
            <div class="card p-3 mb-4">
                <h4><?= htmlspecialchars($position_name); ?></h4>

                <div class="row mt-3">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="col-md-6">
                            <label style="width:100%;">

                                <div class="candidate-card" onclick="selectCandidate(this)">
                                    <input type="radio"
                                           name="vote[<?= $candidate['position_id'] ?>]"
                                           value="<?= $candidate['candidate_id'] ?>"
                                           style="display:none;">

                                    <div class="d-flex align-items-center">
                                        <img src="<?= $candidate['image'] ?: 'https://via.placeholder.com/80' ?>">
                                        <div class="ms-3">
                                            <h5><?= htmlspecialchars($candidate['name']); ?></h5>
                                            <p class="text-muted"><?= htmlspecialchars($candidate['department']); ?> (Year <?= htmlspecialchars($candidate['year']); ?>)</p>
                                        </div>
                                    </div>
                                </div>

                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-primary btn-lg mt-3 float-end">Submit Vote</button>
    </form>
</div>

<script>
function selectCandidate(el) {
    let row = el.closest('.row');
    row.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
}
</script>

</body>
</html>
