<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// Fetch user details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: User not found in the database.");
}

// Fetch accommodation details
$acc_stmt = $pdo->prepare("SELECT * FROM accommodations WHERE user_id = :user_id");
$acc_stmt->execute([':user_id' => $user_id]);
$accommodation = $acc_stmt->fetch(PDO::FETCH_ASSOC);

if (!$accommodation) {
    error_log("Debug: No accommodations found for user_id $user_id", 0);
}

// Fetch extras details
$extras_stmt = $pdo->prepare("SELECT meal_plan, gym_activity FROM extras WHERE user_id = :user_id");
$extras_stmt->execute([':user_id' => $user_id]);
$extras = $extras_stmt->fetch(PDO::FETCH_ASSOC);

if (!$extras) {
    error_log("Debug: No extras found for user_id $user_id", 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accommodation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Feel Fresh Resort</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="grid.php">GridTest</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">ExistingUsers</a></li>
                <li class="nav-item"><a class="nav-link" href="extras.php">Extra Amenities</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Sign In</a></li>
                <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Accommodation</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Sign Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center">User Accommodation & Extras</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>User Details</h4>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
                <h4>Accommodation Details</h4>
                <?php if ($accommodation): ?>
                    <p><strong>Room Type:</strong> <?= htmlspecialchars($accommodation['room_type'] ?? 'N/A') ?></p>
                    <p><strong>WiFi Access:</strong> <?= $accommodation['wifi'] ? 'Yes' : 'No' ?></p>
                    <p><strong>Breakfast Included:</strong> <?= $accommodation['breakfast'] ? 'Yes' : 'No' ?></p>
                    <p><strong>Pool Access:</strong> <?= $accommodation['pool'] ? 'Yes' : 'No' ?></p>
                <?php else: ?>
                    <p class="text-danger">No accommodation details found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Extras Selected</h4>
                <?php if ($extras): ?>
                    <p><strong>Meal Plan:</strong> <?= htmlspecialchars($extras['meal_plan'] ?? 'N/A') ?></p>
                    <p><strong>Gym Activities:</strong> <?= htmlspecialchars($extras['gym_activity'] ?? 'N/A') ?></p>
                <?php else: ?>
                    <p class="text-danger">No extras selected.</p>
                <?php endif; ?>
            </div>
        </div>

        <a href="extras.php" class="btn btn-primary mt-3">Go Back to Extras</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
