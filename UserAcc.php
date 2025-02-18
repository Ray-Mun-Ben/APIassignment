<?php
require_once 'database.php';
session_start();

$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: User not found in the database.");
}

// Fetch accommodation details
$acc_stmt = $pdo->prepare("SELECT room_type, wifi, breakfast, pool FROM accommodations WHERE user_id = :user_id");
$acc_stmt->execute([':user_id' => $user_id]);
$accommodation = $acc_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch extras details with debugging
$extras_stmt = $pdo->prepare("SELECT meal_plan, gym_activity FROM extras WHERE user_id = :user_id");
$extras_stmt->execute([':user_id' => $user_id]);
$extras = $extras_stmt->fetch(PDO::FETCH_ASSOC);

// Debugging output
if (!$extras) {
    echo "<p style='color:red;'>Debug: No extras found for user_id $user_id</p>";
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                        <a class="nav-link" href="grid.php">GridTest</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">ExistingUsers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="extras.php">Extra Amenities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UserAcc.php">UserAccomodation</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="">
                            <button type="submit" name="sign_out" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="receipt.php">Checkout</a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>User Accommodation & Extras</h2>
        
        <h3>User Details</h3>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <h3>Accommodation Details</h3>
        <?php if ($accommodation): ?>
            <p><strong>Room Type:</strong> <?= htmlspecialchars($accommodation['room_type'] ?? 'N/A') ?></p>
            <p><strong>WiFi Access:</strong> <?= $accommodation['wifi'] ? 'Yes' : 'No' ?></p>
            <p><strong>Breakfast Included:</strong> <?= $accommodation['breakfast'] ? 'Yes' : 'No' ?></p>
            <p><strong>Pool Access:</strong> <?= $accommodation['pool'] ? 'Yes' : 'No' ?></p>
        <?php else: ?>
            <p>No accommodation details found.</p>
        <?php endif; ?>

        <h3>Extras Selected</h3>
        <?php if ($extras): ?>
            <p><strong>Meal Plan:</strong> <?= htmlspecialchars($extras['meal_plan'] ?? 'N/A') ?></p>
            <p><strong>Gym Activities:</strong> <?= htmlspecialchars($extras['gym_activity'] ?? 'N/A') ?></p>
        <?php else: ?>
            <p>No extras selected.</p>
        <?php endif; ?>

        <a href="extras.php" class="btn btn-primary mt-3">Go Back to Extras</a>
    </div>
</body>
</html>
