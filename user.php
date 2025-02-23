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
    
        return $this->pdo->lastInsertId(); // Return the user ID for session
    }

    // Check if email exists
    public function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    // Get user ID by email
    public function getUserIdByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    // Get all users
    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT id, username, email FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete user by ID
    public function deleteUserById($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // Get username by ID
    public function getUsernameById($userId) {
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch();
        return $result ? $result['username'] : 'Guest';
    }

    // Method to login user
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
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
            $stmt = $this->pdo->prepare("UPDATE otps SET is_used = TRUE WHERE id = :id");
            $stmt->execute([':id' => $otp['id']]);
            return true;
        }

        return false;
    }

    public function updatePassword($userId, $newPassword)
{
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :user_id");
    $stmt->execute([
        ':password_hash' => $passwordHash,
        ':user_id' => $userId
    ]);
    
    return $stmt->rowCount() > 0;
}

    

    // Get user ID (for OTP saving and verification)
    public function getUserId() {
        return $_SESSION['user_id'];
    }

// Save user's accommodation preferences
public function storeAccommodation($userId, $roomType, $wifi, $breakfast, $pool) {
    $stmt = $this->pdo->prepare("INSERT INTO accommodations (user_id, room_type, wifi, breakfast, pool)
                                 VALUES (:user_id, :room_type, :wifi, :breakfast, :pool)
                                 ON DUPLICATE KEY UPDATE 
                                 room_type = VALUES(room_type), wifi = VALUES(wifi), breakfast = VALUES(breakfast), pool = VALUES(pool)");
    $stmt->execute([
        ':user_id' => $userId,
        ':room_type' => $roomType,
        ':wifi' => $wifi,
        ':breakfast' => $breakfast,
        ':pool' => $pool
    ]);
}

// Get the user's accommodation preferences
public function getAccommodation($userId) {
    $stmt = $this->pdo->prepare("SELECT * FROM accommodations WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Save meal plan and gym activity choices
// Insert or update user's extras (meal plan & gym activity)
public function saveUserExtras($userId, $mealPlan, $gymActivity) {
    $stmt = $this->pdo->prepare("
        INSERT INTO extras (user_id, meal_plan, gym_activity) 
        VALUES (:user_id, :meal_plan, :gym_activity)
        ON DUPLICATE KEY UPDATE meal_plan = VALUES(meal_plan), gym_activity = VALUES(gym_activity)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':meal_plan' => $mealPlan,
        ':gym_activity' => $gymActivity
    ]);
}

// Fetch user's selected extras
public function getUserExtras($userId) {
    $stmt = $this->pdo->prepare("SELECT meal_plan, gym_activity FROM extras WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['meal_plan' => null, 'gym_activity' => null];
}

public function getUserById($userId) {
    $stmt = $this->pdo->prepare("SELECT id, username AS name, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function incrementUnpaidCount($userId) {
    $stmt = $this->pdo->prepare("UPDATE users SET unpaid_reservations = unpaid_reservations + 1 WHERE id = ?");
    $stmt->execute([$userId]);
}

public function shouldBanUser($userId) {
    $stmt = $this->pdo->prepare("SELECT unpaid_reservations FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() >= 3; // Ban after 3 unpaid reservations
}

public function banUser($userId) {
    $stmt = $this->pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
    $stmt->execute([$userId]);
}



}
