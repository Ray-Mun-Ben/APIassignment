<?php
session_start();
require_once 'database.php';
require_once 'User.php';
require_once 'MailerClass.php'; // ✅ Include the updated mailer

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
        $mailer = new Mailer(); // ✅ Use Mailer Class

        if ($user->emailExists($email)) {
            $user->savePasswordResetToken($email, $resetCode);
            $resetCode = bin2hex(random_bytes(16)); // ✅ Secure token
            $user->saveOTP($userId, $resetCode);

            // ✅ Create reset link
            $resetLink = "http://localhost/assignment/reset_password.php?email=$email&code=$resetCode";

            // ✅ Send reset email
            if ($mailer->sendPasswordReset($email, $resetLink)) {
                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "Failed to send reset link.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Forgot Password</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Enter your email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
