<?php
session_start(); // Start the session for session handling
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';


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
        $database = new Database();
        $pdo = $database->connect();
        $user = new User($pdo);

        // Check if user exists and password is correct
        if ($user->login($email, $password)) {
            // Start session and store user info
            $_SESSION['user_email'] = $email; // Or store the user ID or other information
            $_SESSION['logged_in'] = true;

            // Redirect to the dashboard or home page
            header("Location: dashboard.php");
            exit();
        } else {
            $errorMessage = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Login</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
<p><a href="forgot_password.php">Forgot your password?</a></p>

    </div>
</body>
</html>
