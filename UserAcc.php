<?php
require_once 'database.php';
session_start();

$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// Fetch user details
$stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: User not found in the database.");
}

// Fetch latest accommodation details
$acc_stmt = $pdo->prepare("
    SELECT room_type, room_price, days, wifi, breakfast, pool, reservation_date 
    FROM accommodations 
    WHERE user_id = :user_id 
    ORDER BY id DESC LIMIT 1");
$acc_stmt->execute([':user_id' => $user_id]);
$accommodation = $acc_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch latest extras details
$extras_stmt = $pdo->prepare("
    SELECT meal_plan, meal_plan_price, gym_activity, gym_activity_price 
    FROM extras 
    WHERE user_id = :user_id 
    ORDER BY id DESC LIMIT 1");
$extras_stmt->execute([':user_id' => $user_id]);
$extras = $extras_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch reservation record
$res_stmt = $pdo->prepare("SELECT id FROM reservations WHERE user_id = :user_id LIMIT 1");
$res_stmt->execute([':user_id' => $user_id]);
$reservation = $res_stmt->fetch(PDO::FETCH_ASSOC);

// Handle reservation action
$reservationSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reserve'])) {
    if (!$reservation) {
        // Insert reservation if it doesn't exist
        $insert_stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                      meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            VALUES (:user_id, :room_type, :room_price, :days, :wifi, :breakfast, :pool, :reservation_date, 
                    :meal_plan, :meal_plan_price, :gym_activity, :gym_activity_price)");
    } else {
        // Update reservation if it already exists
        $insert_stmt = $pdo->prepare("
            UPDATE reservations SET 
                room_type = :room_type, room_price = :room_price, days = :days, wifi = :wifi, breakfast = :breakfast, 
                pool = :pool, reservation_date = :reservation_date, 
                meal_plan = :meal_plan, meal_plan_price = :meal_plan_price, 
                gym_activity = :gym_activity, gym_activity_price = :gym_activity_price
            WHERE user_id = :user_id");
    }

    // Execute with latest accommodation & extras
    $insert_stmt->execute([
        ':user_id' => $user_id,
        ':room_type' => $accommodation['room_type'] ?? null,
        ':room_price' => $accommodation['room_price'] ?? null,
        ':days' => $accommodation['days'] ?? 1, // Default to 1 day if missing
        ':wifi' => $accommodation['wifi'] ?? 0,
        ':breakfast' => $accommodation['breakfast'] ?? 0,
        ':pool' => $accommodation['pool'] ?? 0,
        ':reservation_date' => $accommodation['reservation_date'] ?? null,
        ':meal_plan' => $extras['meal_plan'] ?? null,
        ':meal_plan_price' => $extras['meal_plan_price'] ?? null,
        ':gym_activity' => $extras['gym_activity'] ?? null,
        ':gym_activity_price' => $extras['gym_activity_price'] ?? null,
    ]);

    $reservationSuccess = true;
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

        <?php if ($reservationSuccess): ?>
            <div class="alert alert-success text-center">
                Your reservation has been successfully made!
            </div>
        <?php endif; ?>

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
                    <p><strong>Room Price Per Night:</strong> <?= isset($accommodation['room_price']) ? "$" . htmlspecialchars($accommodation['room_price']) : '-' ?></p>
                    <p><strong>Number of Days:</strong> <?= htmlspecialchars($accommodation['days'] ?? 'N/A') ?></p>
                    <p><strong>Total Room Cost:</strong> <?= isset($accommodation['room_price']) ? "$" . ($accommodation['room_price'] * ($accommodation['days'] ?? 1)) : '-' ?></p>
                    <p><strong>Reservation Date:</strong> <?= htmlspecialchars($accommodation['reservation_date'] ?? 'N/A') ?></p>
                    <p><strong>WiFi Access:</strong> <?= $accommodation['wifi'] ? 'Yes' : 'No' ?></p>
                    <p><strong>Breakfast Included:</strong> <?= $accommodation['breakfast'] ? 'Yes' : 'No' ?></p>
                    <p><strong>Pool Access:</strong> <?= $accommodation['pool'] ? 'Yes' : 'No' ?></p>
                <?php else: ?>
                    <p class="text-danger">No accommodation details found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <form method="POST">
                <button type="submit" name="reserve" class="btn btn-warning">
                    <?= $reservation ? "Update Reservation" : "Reserve" ?>
                </button>
            </form>
            <a href="receipt.php" class="btn btn-success mt-2">Proceed to Receipt</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
