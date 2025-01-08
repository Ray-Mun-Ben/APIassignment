<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

$errors = [];
$successMessage = "";

// Ensure the user is coming from the registration flow
if (!isset($_SESSION['email']) || !isset($_SESSION['2fa_code'])) {
    header('Location: register.php'); // Redirect to registration if session is invalid
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);

    if (empty($code)) {
        $errors[] = "Verification code is required.";
    } elseif ($code !== $_SESSION['2fa_code']) {
        $errors[] = "Invalid verification code.";
    } else {
        // Verification successful
        $successMessage = "2FA verified successfully!";

        // Optional: Clear 2FA session variables
        unset($_SESSION['2fa_code']);

        // Redirect to the dashboard
        header('Location: dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>2FA Verification</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Verify 2FA Code</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="code" class="form-label">Verification Code</label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </div>
</body>
</html>
