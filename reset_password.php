<?php
require_once 'database.php';
require_once 'user.php';

session_start();
$pdo = Database::getConnection();
$user = new User($pdo);

$code = $_GET['code'] ?? null;
$email = $_GET['email'] ?? null;

// Check if the code and email are valid
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $code && $email) {
    $userId = $user->getUserIdByEmail($email);
    
    if ($userId && $user->verifyOTP($userId, $code)) {
        // Show the reset password form
        echo '
            <form method="POST">
                <label>New Password:</label>
                <input type="password" name="new_password" required>
                <button type="submit">Reset Password</button>
            </form>
        ';
    } else {
        echo "Invalid or expired reset link.";
    }
}

// Handle the password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? null;
    if ($newPassword && $userId) {
        if ($user->updatePassword($userId, $newPassword)) {
            echo "Password has been reset successfully. <a href='login.php'>Login here</a>";
        } else {
            echo "Failed to reset password. Please try again.";
        }
    }
}
?>
