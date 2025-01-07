<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Function to send 2FA code via email
function send2FACode($recipientEmail, $recipientName, $twoFACode)
{
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;        // Disable verbose debug output for production
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'raymond.mungai@strathmore.edu';   // Fetch email username from environment variables
        $mail->Password   = 'avhw lrmj piyo vakz';   // Fetch app password from environment variables
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
        $mail->Port       = 465;                   // SMTP port for SSL

        // Recipients
        $mail->setFrom('raymond.mungai@strathmore.edu', 'AssignmentApp');
        $mail->addAddress($recipientEmail, $recipientName);

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = 'Your 2FA Code';
        $mail->Body    = "<p>Your 2FA code is: <b>$twoFACode</b></p>";
        $mail->AltBody = "Your 2FA code is: $twoFACode";

        // Send the email
        $mail->send();
        return "2FA code sent successfully!";
    } catch (Exception $e) {
        return "Error: {$mail->ErrorInfo}";
    }
}

// Example usage
$recipientEmail = 'recipient_email@example.com';
$recipientName = 'Recipient Name';
$twoFACode = random_int(100000, 999999); // Generate a 6-digit random 2FA code

echo send2FACode($recipientEmail, $recipientName, $twoFACode);
?>
