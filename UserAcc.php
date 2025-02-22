<?php
require_once 'database.php';
require_once 'User.php';
require_once 'ExtrasM.php';
require_once 'Accommodation.php';

session_start();

$database = new Database();
$pdo = $database->connect();

$user = new User($pdo);
$extras = new ExtrasM($pdo);
$accommodation = new Accommodation($pdo);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

$user_details = $user->getUserById($user_id);
$accommodation_details = $accommodation->getLatestAccommodation($user_id);
$extras_details = $extras->getUserExtras($user_id);

// Fetch reservation record
$res_stmt = $pdo->prepare("SELECT id FROM reservations WHERE user_id = :user_id LIMIT 1");
$res_stmt->execute([':user_id' => $user_id]);
$reservation = $res_stmt->fetch(PDO::FETCH_ASSOC);

$reservationSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reserve'])) {
    if (!$reservation) {
        // Insert new reservation
        $insert_stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                      meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            VALUES (:user_id, :room_type, :room_price, :days, :wifi, :breakfast, :pool, :reservation_date, 
                    :meal_plan, :meal_plan_price, :gym_activity, :gym_activity_price)");
    } else {
        // Update existing reservation
        $insert_stmt = $pdo->prepare("
            UPDATE reservations SET 
                room_type = :room_type, room_price = :room_price, days = :days, wifi = :wifi, breakfast = :breakfast, 
                pool = :pool, reservation_date = :reservation_date, 
                meal_plan = :meal_plan, meal_plan_price = :meal_plan_price, 
                gym_activity = :gym_activity, gym_activity_price = :gym_activity_price
            WHERE user_id = :user_id");
    }

    $insert_stmt->execute([
        ':user_id' => $user_id,
        ':room_type' => $accommodation_details['room_type'] ?? null,
        ':room_price' => $accommodation_details['room_price'] ?? null,
        ':days' => $accommodation_details['days'] ?? 1,
        ':wifi' => $accommodation_details['wifi'] ?? 0,
        ':breakfast' => $accommodation_details['breakfast'] ?? 0,
        ':pool' => $accommodation_details['pool'] ?? 0,
        ':reservation_date' => $accommodation_details['reservation_date'] ?? null,
        ':meal_plan' => $extras_details['meal_plan'] ?? null,
        ':meal_plan_price' => $extras_details['meal_plan_price'] ?? null,
        ':gym_activity' => $extras_details['gym_activity'] ?? null,
        ':gym_activity_price' => $extras_details['gym_activity_price'] ?? null,
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
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Feel Fresh Resort</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="grid.php">Reservation</a></li>
                    <li class="nav-item"><a class="nav-link" href="extras.php">Extra Amenities</a></li>
                    <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Details</a></li>
                    <li class="nav-item">
                        <form method="POST" action="">
                            <button type="submit" name="sign_out" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
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
                <p><strong>Username:</strong> <?= htmlspecialchars($user_details['name'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_details['email'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
                <h4>Accommodation Details</h4>
                <p><strong>Room Type:</strong> <?= htmlspecialchars($accommodation_details['room_type'] ?? 'N/A') ?></p>
                <p><strong>Room Price Per Night:</strong> <?= isset($accommodation_details['room_price']) ? "$" . htmlspecialchars($accommodation_details['room_price']) : '-' ?></p>
                <p><strong>Number of Days:</strong> <?= htmlspecialchars($accommodation_details['days'] ?? 'N/A') ?></p>
                <p><strong>Total Room Cost:</strong> <?= isset($accommodation_details['room_price']) ? "$" . ($accommodation_details['room_price'] * ($accommodation_details['days'] ?? 1)) : '-' ?></p>
                <p><strong>WiFi Access:</strong> <?= $accommodation_details['wifi'] ? 'Yes' : 'No' ?></p>
                <p><strong>Breakfast Included:</strong> <?= $accommodation_details['breakfast'] ? 'Yes' : 'No' ?></p>
                <p><strong>Pool Access:</strong> <?= $accommodation_details['pool'] ? 'Yes' : 'No' ?></p>
            </div>
        </div>

        <!-- ✅ Extras Section Added Here -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Extras Selected</h4>
                <?php if ($extras_details): ?>
                    <p><strong>Meal Plan:</strong> <?= htmlspecialchars($extras_details['meal_plan'] ?? 'N/A') ?></p>
                    <p><strong>Meal Plan Price:</strong> <?= isset($extras_details['meal_plan_price']) ? "$" . htmlspecialchars($extras_details['meal_plan_price']) : '-' ?></p>
                    <p><strong>Gym Activities:</strong> <?= htmlspecialchars($extras_details['gym_activity'] ?? 'N/A') ?></p>
                    <p><strong>Gym Activity Price:</strong> <?= isset($extras_details['gym_activity_price']) ? "$" . htmlspecialchars($extras_details['gym_activity_price']) : '-' ?></p>
                <?php else: ?>
                    <p class="text-danger">No extras selected.</p>
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
</body>
</html>
