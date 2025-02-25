<?php
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';



class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP() {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'raymond.mungai@strathmore.edu'; // Your email
            $this->mail->Password = 'avhw lrmj piyo vakz'; // App-specific password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port = 465;

            // ✅ Use valid sender address
            $this->mail->setFrom('raymond.mungai@strathmore.edu', 'Feel Fresh Resort', false);
        } catch (Exception $e) {
            throw new Exception("Mailer setup failed: " . $this->mail->ErrorInfo);
        }
    }

    public function sendMail($recipientEmail, $subject, $message) {
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid recipient email address.");
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($recipientEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->AltBody = "If you can't view this email, your reset link is: $message";

            // ✅ Enable debugging for logs
            $this->mail->SMTPDebug = 2;
            $this->mail->Debugoutput = 'html';

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: " . $this->mail->ErrorInfo);
        }
    }

    public function send2FACode($recipientEmail, $code) {
        $subject = "Your 2FA Code";
        $message = "Your 2FA code is: <strong>$code</strong>";
        return $this->sendMail($recipientEmail, $subject, $message);
    }

    public function sendPasswordReset($recipientEmail, $resetLink) {
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";
        return $this->sendMail($recipientEmail, $subject, $message);
    }
}

?>
