<?php
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send2FACode($recipientEmail, $code) {
    // Check if $recipientEmail is a valid email
    if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid recipient email address.");
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'raymond.mungai@strathmore.edu'; // Replace with your email
        $mail->Password = 'avhw lrmj piyo vakz'; // Replace with your app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Your App Name');
        $mail->addAddress($recipientEmail); // Dynamic recipient email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your 2FA Code';
        $mail->Body = "Your 2FA code is: <strong>$code</strong>";
        $mail->AltBody = "Your 2FA code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
