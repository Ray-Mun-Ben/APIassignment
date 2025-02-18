<?php
require_once 'database.php';
require_once 'user.php';

session_start();

// Get the database connection
$database = new Database();
$pdo = $database->getConnection();
$user = new User($pdo);

$code = $_GET['code'] ?? null;
$email = $_GET['email'] ?? null;

$userId = null; // Define $userId outside the conditions to prevent "undefined" errors

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    $newPassword = $_POST['new_password'] ?? null;
    
    if ($newPassword) {
        if ($user->updatePassword($userId, $newPassword)) {
            echo "Password has been reset successfully. Redirecting to login page...";
            
            // Redirect to index.php after a short delay
            header("refresh:3;url=index.php");
            exit; // Make sure the script stops after the redirect
        } else {
            echo "Failed to reset password. Please try again.";
        }
    }
}
?>
