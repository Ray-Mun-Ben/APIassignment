<?php
session_start();
require_once 'database.php';
require_once 'user.php';

$errorMessage = "";

// Generate CSRF Token if it's not set already
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $csrf_token = $_POST['csrf_token'];

    // CSRF Token Validation
    if ($csrf_token !== $_SESSION['csrf_token']) {
        $errorMessage = "Invalid CSRF token!";
    } else {
        // ✅ **Password validation (Server-side)**
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $errorMessage = "Password must have at least 1 uppercase letter, 1 number, 1 special character, and be at least 8 characters long.";
        } else {
            $database = new Database();
            $pdo = $database->connect();
            $user = new User($pdo);

            // Check if user exists and password is correct
            if ($user->login($email, $password)) {
                $_SESSION['user_email'] = $email;
                $_SESSION['logged_in'] = true;
                header("Location: grid.php");
                exit();
            } else {
                $errorMessage = "Invalid email or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    
    <script>
        function validatePassword() {
            let password = document.getElementById("password").value;
            let errorDiv = document.getElementById("passwordError");
            
            // ✅ **Check the password pattern**
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
        <h2 class="text-center">Login</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" onsubmit="return validatePassword()">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required onkeyup="validatePassword()">
                <div id="passwordError" class="small mt-1"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="mt-3 text-center"><a href="forgot_password.php">Forgot your password?</a></p>
    </div>
</body>
</html>
