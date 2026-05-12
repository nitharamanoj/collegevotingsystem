<?php
// ============================================================
// MAIL CONFIGURATION (PHPMailer + Gmail SMTP)
// Copy this file to mail_config.php and fill in your values
// NEVER commit mail_config.php to version control
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your_email@gmail.com';        // Your Gmail address
    $mail->Password   = 'your_gmail_app_password';     // Gmail App Password (NOT your login password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    return $mail;

} catch (Exception $e) {
    error_log("Failed to initialize PHPMailer: {$e->getMessage()}");
    return null;
}
?>
