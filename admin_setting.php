<?php
session_start();
require_once 'database.php';

// Ensure only admins can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$pdo = (new Database())->connect();

// ✅ Fetch Current Rate
$rateStmt = $pdo->query("SELECT seasonal_rate FROM admin_settings LIMIT 1");
$rateRow = $rateStmt->fetch(PDO::FETCH_ASSOC);
$currentRate = $rateRow ? (float)$rateRow['seasonal_rate'] : 1.00;

// ✅ Handle Rate Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newRate = isset($_POST['seasonal_rate']) ? (float)$_POST['seasonal_rate'] : 1.00;
    
    $stmt = $pdo->prepare("UPDATE admin_settings SET seasonal_rate = ? WHERE id = 1");
    if ($stmt->execute([$newRate])) {
        $success = "Seasonal rate updated successfully!";
        $currentRate = $newRate; // Update displayed value
    } else {
        $error = "Failed to update seasonal rate.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Seasonal Rate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Admin: Adjust Seasonal Pricing</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="seasonal_rate" class="form-label">Set Seasonal Rate (e.g., 1.2 for +20%, 0.8 for -20%)</label>
                <input type="number" step="0.1" name="seasonal_rate" id="seasonal_rate" class="form-control" 
                       value="<?= htmlspecialchars($currentRate) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Rate</button>
        </form>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
