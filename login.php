<?php
require_once 'Database.php';
require_once 'User.php';

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $database = new Database();
    $pdo = $database->connect();

    $user = new User($pdo);
    if ($user->login($email, $password)) {
        echo "Login successful!";
        // Redirect or start session
    } else {
        $errorMessage = "Invalid email or password.";
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
    </div>
</body>
</html>
