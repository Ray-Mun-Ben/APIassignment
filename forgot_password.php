<?php
session_start();
require_once 'database.php';
require_once 'user.php';
require_once 'mailer.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        $database = new Database();
        $pdo = $database->connect();
        $user = new User($pdo);

        if ($user->emailExists($email)) {
            $userId = $user->getUserIdByEmail($email);

            // Generate a reset code and save it in the database
            $resetCode = bin2hex(random_bytes(16)); // Generate a secure random code
            $user->saveOTP($userId, $resetCode);

            // Create the reset link
            $resetLink = "http://mysite.local/assignment/reset_password.php?code=$resetCode";

            // Send the reset link via email
            if (send2FACode($email, "Click the following link to reset your password: $resetLink")) {
                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "Failed to send the reset link. Please try again.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>

<!-- HTML Form -->
<form method="POST" action="">
    <label for="email">Enter your email address:</label>
    <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
</form>
<?php if ($error): ?><p style="color: red;"><?php echo $error; ?></p><?php endif; ?>
<?php if ($success): ?><p style="color: green;"><?php echo $success; ?></p><?php endif; ?>
