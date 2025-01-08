<?php
require_once 'vendor/autoload.php';  
require_once 'Database.php';
require_once 'User.php';
require_once 'mailer.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please provide a valid email address.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $errorMessage = "Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.";
    } else {
        try {
            $database = new Database();
            $pdo = $database->connect();
            $user = new User($pdo);
            $result = $user->register($username, $email, $password);

            if ($result === "Registration successful.") {
                $twoFACode = rand(100000, 999999);
                session_start();
                $_SESSION['2fa_code'] = $twoFACode;
                $_SESSION['email'] = $email;

                if (send2FACode($email, $twoFACode)) {
                    header("Location: verify2fa.php");
                    exit();
                } else {
                    $errorMessage = "Failed to send 2FA code.";
                }
            } else {
                $errorMessage = implode('<br>', $result);
            }
        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Register</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Register</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php elseif (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>
