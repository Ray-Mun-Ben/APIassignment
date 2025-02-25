<?php
session_start();
require_once 'database.php';
require_once 'user.php';

$error = "";
$success = "";
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $token = $_POST['token'];

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
        $error = "Password must have at least 8 characters, 1 uppercase letter, 1 number, and 1 special character.";
    } else {
        $database = new Database();
        $pdo = $database->connect();
        $user = new User($pdo);

        // Verify token
        $userData = $user->verifyPasswordResetToken($token);
        
        if ($userData) {
            $userId = $userData['id'];
            
            // Check if token is expired (assuming `expires_at` column exists)
            if (strtotime($userData['expires_at']) < time()) {
                $error = "Reset token has expired. Request a new one.";
            } else {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($user->updatePassword($userId, $newPasswordHash)) {
                    $success = "Password reset successfully. <a href='login.php'>Login</a>";
                    
                    // Invalidate the token after successful password reset
                    $user->invalidateResetToken($token);
                } else {
                    $error = "Failed to update password.";
                }
            }
        } else {
            $error = "Invalid or expired reset token.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Reset Password</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('new_password')">👁</button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')">👁</button>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Reset Password</button>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            var input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
