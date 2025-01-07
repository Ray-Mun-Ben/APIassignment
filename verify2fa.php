<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'User.php';

$errors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $user = new User();
    
    if ($user->verify2FACode($code)) {
        $successMessage = "2FA verified successfully!";
        header('Location: dashboard.php'); // Redirect to the dashboard after success
        exit();
    } else {
        $errors[] = "Invalid verification code.";
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
