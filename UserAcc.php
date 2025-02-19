<?php
require_once 'database.php';
session_start();

$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// Fetch user details (Explicitly selecting from `users`)
$stmt = $pdo->prepare("SELECT users.id, users.username, users.email FROM users WHERE users.id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: User not found in the database.");
}

// Fetch latest accommodation details (Explicit table reference)
$acc_stmt = $pdo->prepare("SELECT accommodations.room_type, accommodations.room_price, accommodations.wifi, 
                                  accommodations.breakfast, accommodations.pool, accommodations.reservation_date 
                           FROM accommodations 
                           WHERE accommodations.user_id = :user_id 
                           ORDER BY accommodations.id DESC LIMIT 1");
$acc_stmt->execute([':user_id' => $user_id]);
$accommodation = $acc_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch extras details (Explicit table reference)
$extras_stmt = $pdo->prepare("SELECT extras.meal_plan, extras.meal_plan_price, extras.gym_activity, extras.gym_activity_price 
                              FROM extras 
                              WHERE extras.user_id = :user_id");
$extras_stmt->execute([':user_id' => $user_id]);
$extras = $extras_stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user has already reserved a room (Explicit table reference)
$res_stmt = $pdo->prepare("SELECT reservations.id FROM reservations WHERE reservations.user_id = :user_id LIMIT 1");
$res_stmt->execute([':user_id' => $user_id]);
$reservation = $res_stmt->fetch(PDO::FETCH_ASSOC);

// Handle reservation action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reserve'])) {
    if (!$reservation) { // Prevent duplicate reservations
        $insert_stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, room_type, room_price, wifi, breakfast, pool, reservation_date, 
                                      meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            SELECT accommodations.user_id, accommodations.room_type, accommodations.room_price, accommodations.wifi, 
                   accommodations.breakfast, accommodations.pool, accommodations.reservation_date, 
                   extras.meal_plan, extras.meal_plan_price, extras.gym_activity, extras.gym_activity_price
            FROM accommodations 
            LEFT JOIN extras ON accommodations.user_id = extras.user_id
            WHERE accommodations.user_id = :user_id");
        $insert_stmt->execute([':user_id' => $user_id]);
    }
    header("Location: UserAcc.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Summary</title>
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
                <li class="nav-item"><a class="nav-link" href="grid.php">Grid</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="extras.php">Extras</a></li>
                <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Summary</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Sign Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center">Booking Summary</h2>
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
                    <p><strong>Room Price:</strong> <?= isset($accommodation['room_price']) ? "$" . htmlspecialchars($accommodation['room_price']) : '-' ?></p>
                    <p><strong>Reservation Date:</strong> <?= htmlspecialchars($accommodation['reservation_date'] ?? 'N/A') ?></p>
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
                    <p><strong>Meal Plan Price:</strong> <?= isset($extras['meal_plan_price']) ? "$" . htmlspecialchars($extras['meal_plan_price']) : '-' ?></p>
                    <p><strong>Gym Activities:</strong> <?= htmlspecialchars($extras['gym_activity'] ?? 'N/A') ?></p>
                    <p><strong>Gym Activity Price:</strong> <?= isset($extras['gym_activity_price']) ? "$" . htmlspecialchars($extras['gym_activity_price']) : '-' ?></p>
                <?php else: ?>
                    <p class="text-danger">No extras selected.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <!-- Reserve Button -->
            <form method="POST">
                <button type="submit" name="reserve" class="btn btn-warning" <?= $reservation ? 'disabled' : '' ?>>
                    <?= $reservation ? "Reserved" : "Reserve" ?>
                </button>
            </form>

            <!-- Proceed to Receipt -->
            <a href="receipt.php" class="btn btn-success mt-2">Proceed to Receipt</a>

            <!-- Modify Extras -->
            <a href="extras.php" class="btn btn-primary mt-2">Modify Extras</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
