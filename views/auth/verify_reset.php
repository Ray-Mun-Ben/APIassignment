<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';


$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $email = $_SESSION['reset_email'] ?? '';

    if (empty($email)) {
        header('Location: forgot_password.php');
        exit();
    }

    if (empty($code)) {
        $error = "Code is required.";
    } else {
        $database = new Database();
        $pdo = $database->connect();
        $user = new User($pdo);

        if ($user->verifyOTP($user->getUserIdByEmail($email), $code)) {
            $_SESSION['verified_reset'] = true;
            header('Location: reset_password.php');
            exit();
        } else {
            $error = "Invalid code.";
        }
    }
}
?>

<!-- HTML Form -->
<form method="POST" action="">
    <label for="code">Enter the reset code sent to your email:</label>
    <input type="text" name="code" required>
    <button type="submit">Verify Code</button>
</form>
<?php if ($error): ?><p><?php echo $error; ?></p><?php endif; ?>
