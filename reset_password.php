<?php
session_start();
require_once 'database.php';
require_once 'user.php';

$error = "";
$success = "";

if (!$_SESSION['verified_reset']) {
    header('Location: forgot_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];

    if (empty($password) || !preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters, with uppercase, number, and special character.";
    } else {
        $database = new Database();
        $pdo = $database->connect();
        $user = new User($pdo);
        $email = $_SESSION['reset_email'];

        if ($user->updatePassword($user->getUserIdByEmail($email), $password)) {
            unset($_SESSION['reset_email'], $_SESSION['verified_reset']);
            $success = "Password updated successfully! Redirecting to login...";
            header("refresh:3;url=login.php");
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>

<!-- HTML Form -->
<form method="POST" action="">
    <label for="password">Enter your new password:</label>
    <input type="password" name="password" required>
    <button type="submit">Reset Password</button>
</form>
<?php if ($error): ?><p><?php echo $error; ?></p><?php endif; ?>
<?php if ($success): ?><p><?php echo $success; ?></p><?php endif; ?>
