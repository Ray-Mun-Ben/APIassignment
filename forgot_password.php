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
            $resetCode = random_int(100000, 999999);
            $_SESSION['reset_email'] = $email;

            $user->saveOTP($user->getUserIdByEmail($email), $resetCode);

            if (send2FACode($email, $resetCode)) {
                $success = "A reset code has been sent to your email.";
            } else {
                $error = "Failed to send reset code.";
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
    <button type="submit">Send Reset Code</button>
</form>
<?php if ($error): ?><p><?php echo $error; ?></p><?php endif; ?>
<?php if ($success): ?><p><?php echo $success; ?></p><?php endif; ?>
