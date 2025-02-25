<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'database.php';
require_once 'user.php';
require_once 'mailer.php';

$errorMessage = "";
$successMessage = "";

// Generate CSRF Token if it's not set already
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $csrf_token = $_POST['csrf_token'];

    // CSRF Token Validation
    if ($csrf_token !== $_SESSION['csrf_token']) {
        $errorMessage = "Invalid CSRF token!";
    } elseif (empty($username) || empty($email) || empty($password)) {
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
            
            // ✅ Check if email is already registered
            if ($user->userExists($email)) {
                $errorMessage = "An account with this email already exists.";
            } else {
                // Register the user and get the user_id
                $userId = $user->register($username, $email, $password);

                if ($userId) {
                    $_SESSION['user_id'] = $userId;

                    // ✅ Generate & store 2FA code securely
                    $twoFACode = random_int(100000, 999999);
                    $_SESSION['2fa_code'] = $twoFACode;
                    $_SESSION['email'] = $email;

                    // ✅ Save OTP to the database
                    $user->saveOTP($userId, $twoFACode);

                    // ✅ Send 2FA code via email
                    if (send2FACode($email, $twoFACode)) {
                        header("Location: verify2fa.php");
                        exit();
                    } else {
                        $errorMessage = "Failed to send 2FA code.";
                    }
                } else {
                    $errorMessage = "Registration failed.";
                }
            }
        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">

    <script>
        function validatePassword() {
            let password = document.getElementById("password").value;
            let errorDiv = document.getElementById("passwordError");

            // ✅ **Password validation pattern**
            let pattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!pattern.test(password)) {
                errorDiv.textContent = "Password must have at least 1 uppercase letter, 1 number, 1 special character, and be at least 8 characters long.";
                errorDiv.style.color = "red";
                return false;
            } else {
                errorDiv.textContent = "";
                return true;
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php elseif (!empty($successMessage)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" onsubmit="return validatePassword()">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
                <input type="password" id="password" name="password" class="form-control" required onkeyup="validatePassword()">
                <div id="passwordError" class="small mt-1"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="mt-3 text-center"><a href="login.php">Already have an account? Login</a></p>
    </div>
</body>
</html>
