<?php
require_once 'Database.php'; // Include the Database class
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Login method
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Generate and send the 2FA code
            $this->send2FACode($email);
            return "Login successful! A 2FA code has been sent to your email.";
        } else {
            return ["Invalid email or password."];
        }
    }

    // Send 2FA code via email
    public function send2FACode($email) {
        // Generate a random 6-digit code
        $code = mt_rand(100000, 999999);

        // Store the code temporarily in the session (or database for security)
        session_start();
        $_SESSION['2fa_code'] = $code;
        $_SESSION['2fa_email'] = $email;

        // Send the code via email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Use your email provider's SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com'; // Your email address
            $mail->Password = 'your_email_password'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('your_email@gmail.com', 'Your Name');
            $mail->addAddress($email); // Recipient's email address

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your 2FA Code';
            $mail->Body    = "Your 2FA code is: <strong>$code</strong>";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>
