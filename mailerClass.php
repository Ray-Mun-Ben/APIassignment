<?php
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            $this->mail->Username = 'raymond.mungai@strathmore.edu'; // Replace with your email
            $this->mail->Password = 'avhw lrmj piyo vakz'; // Use your app password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port = 465;
            $this->mail->setFrom('raymond.mungai@strathmore.edu', 'Feel Fresh Resort');
        } catch (Exception $e) {
            throw new Exception("Mailer setup failed: " . $this->mail->ErrorInfo);
        }
    }

    // General email sending method
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
            $this->mail->AltBody = strip_tags($message);
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: " . $this->mail->ErrorInfo);
        }
    }

    // Method for sending 2FA codes
    public function send2FACode($recipientEmail, $code) {
        $subject = "Your 2FA Code";
        $message = "Your 2FA code is: <strong>$code</strong>";
        return $this->sendMail($recipientEmail, $subject, $message);
    }

    // Method for sending reservation acceptance email
    public function sendReservationAcceptance($recipientEmail, $username) {
        $subject = "Your Reservation Has Been Accepted!";
        $message = "
            Dear $username,<br><br>
            Your reservation at <strong>Feel Fresh Resort</strong> has been successfully accepted!<br>
            Please make full payment within **5 hours** to secure your booking.<br>
            Failure to pay on time will result in automatic cancellation.<br><br>
            **Repeated non-payments will result in a ban.**<br>
            Kindly make your payment at the reception or via online transfer.<br><br>
            Best regards,<br>
            <strong>Feel Fresh Resort Team</strong>
        ";
        return $this->sendMail($recipientEmail, $subject, $message);
    }
}
