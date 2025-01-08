<?php
require_once 'database.php';

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Method to register a new user
    public function register($username, $email, $password) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT)
        ]);
    
        // Get the user ID of the newly registered user
        $userId = $this->pdo->lastInsertId();
        
        // Return the user ID for session
        return $userId;
    }
    

    // Method to login user
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }

        return false;
    }

    // Save OTP to the database
    public function saveOTP($userId, $otpCode) {
        $stmt = $this->pdo->prepare("INSERT INTO otps (user_id, otp_code, expiration_time) VALUES (:user_id, :otp_code, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $stmt->execute([
            ':user_id' => $userId,
            ':otp_code' => $otpCode
        ]);
    }

    // Verify OTP from the database
    public function verifyOTP($userId, $otpCode) {
        $stmt = $this->pdo->prepare("SELECT * FROM otps WHERE user_id = :user_id AND otp_code = :otp_code AND is_used = FALSE AND expiration_time > NOW()");
        $stmt->execute([
            ':user_id' => $userId,
            ':otp_code' => $otpCode
        ]);

        $otp = $stmt->fetch();

        if ($otp) {
            // Mark the OTP as used
            $stmt = $this->pdo->prepare("UPDATE otps SET is_used = TRUE WHERE id = :id");
            $stmt->execute([':id' => $otp['id']]);
            return true;
        }

        return false;
    }
    
    // Get user ID (for OTP saving and verification)
    public function getUserId() {
        return $_SESSION['user_id'];
    }
}
?>
