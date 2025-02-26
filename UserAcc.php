<?php
require_once 'database.php';
require_once 'User.php';
require_once 'ExtrasM.php';
require_once 'Accommodation.php';
require_once 'Reservation.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$pdo = $database->connect();

// ✅ Fetch seasonal rate from the admin_settings table
$rateStmt = $pdo->query("SELECT seasonal_rate FROM admin_settings ORDER BY id DESC LIMIT 1");
$rateRow = $rateStmt->fetch(PDO::FETCH_ASSOC);
$seasonalRate = $rateRow ? (float)$rateRow['seasonal_rate'] : 1.00; // Default to 1.0 if not set

$user = new User($pdo);
$extras = new ExtrasM($pdo);
$accommodation = new Accommodation($pdo);
$reservationObj = new Reservation($pdo);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// ✅ Fetch user details
$user_details = $user->getUserById($user_id) ?? ['name' => 'N/A', 'email' => 'N/A'];

// ✅ Fetch the latest accommodation details
$accommodation_details = $accommodation->getLatestAccommodation($user_id) ?? [
    'room_type' => 'Not Selected',
    'room_price' => 0,
    'days' => 1,
    'wifi' => 0,
    'breakfast' => 0,
    'pool' => 0,
    'reservation_date' => 'Not Set'
];

// ✅ Debug: Check if accommodation data is fetched properly
var_dump($accommodation_details); exit();


// ✅ Fetch the latest extras details
$extras_details = $extras->getUserExtras($user_id) ?? [
    'meal_plan' => 'None',
    'meal_plan_price' => 0,
    'gym_activity' => 'None',
    'gym_activity_price' => 0
];

// ✅ Fetch the most recent reservation for the user
$reservation = $reservationObj->getLatestReservation($user_id);
$reservationStatus = $reservation['status'] ?? 'No Reservation Made'; 

$reservationSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reserve'])) {
    // Always create a new reservation instead of updating
    $reservationObj->createReservation($user_id, $accommodation_details, $extras_details);
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
                <li class="nav-item"><a class="nav-link" href="receipt.php">Checkout</a></li>
                <a href="logout.php" class="btn btn-danger">Sign Out</a>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Progress Tracker -->
<div class="container mt-3">
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <ul class="nav nav-pills nav-justified mt-2">
        <li class="nav-item">
            <a class="nav-link" id="step1" href="grid.php">Step 1: Select Room</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step2" href="extras.php">Step 2: Choose Extras</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step3" href="UserAcc.php">Step 3: Review & Reserve</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step4" href="receipt.php">Step 4: Get Receipt</a>
        </li>
    </ul>
</div>

<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center">Booking Summary</h2>

        <?php if ($reservationSuccess): ?>
            <div class="alert alert-success text-center">
                Your reservation has been successfully made!
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Reservation Status</h4>
                <p class="<?= ($reservationStatus === 'accepted') ? 'text-success' : (($reservationStatus === 'rejected') ? 'text-danger' : 'text-warning') ?>">
                    <strong>Status:</strong> <?= ucfirst($reservationStatus) ?>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4>User Details</h4>
                <p><strong>Username:</strong> <?= htmlspecialchars($user_details['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_details['email']) ?></p>
            </div>
            <div class="col-md-6">
                <h4>Accommodation Details</h4>
                <p><strong>Room Type:</strong> <?= htmlspecialchars($accommodation_details['room_type']) ?></p>
                <p><strong>Room Price Per Night:</strong> $<?= htmlspecialchars($accommodation_details['room_price']) ?></p>
                <p><strong>Number of Days:</strong> <?= htmlspecialchars($accommodation_details['days']) ?></p>
                <p><strong>Total Room Cost:</strong> 
    $<?= number_format(($accommodation_details['room_price'] * $accommodation_details['days']) * $seasonalRate, 2) ?>
</p>
                <p><strong>WiFi Access:</strong> <?= $accommodation_details['wifi'] ? 'Yes' : 'No' ?></p>
                <p><strong>Breakfast Included:</strong> <?= $accommodation_details['breakfast'] ? 'Yes' : 'No' ?></p>
                <p><strong>Pool Access:</strong> <?= $accommodation_details['pool'] ? 'Yes' : 'No' ?></p>
            </div>
        </div>

        <!-- ✅ Extras Section -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Extras Selected</h4>
                <p><strong>Meal Plan:</strong> <?= htmlspecialchars($extras_details['meal_plan']) ?></p>
                <p><strong>Meal Plan Price:</strong> $<?= htmlspecialchars($extras_details['meal_plan_price']) ?></p>
                <p><strong>Gym Activities:</strong> <?= htmlspecialchars($extras_details['gym_activity']) ?></p>
                <p><strong>Gym Activity Price:</strong> $<?= htmlspecialchars($extras_details['gym_activity_price']) ?></p>
            </div>
        </div>

        <div class="text-center mt-4">
            <form method="POST">
                <button type="submit" name="reserve" class="btn btn-warning">
                    <?= $reservation ? "Update Reservation" : "Reserve" ?>
                </button>
            </form>

            <a href="receipt.php" class="btn btn-success mt-2">Proceed to Receipt</a>
            
            <!-- ✅ Refresh Button -->
            <button class="btn btn-info mt-2" onclick="refreshPage()">Refresh</button>
        </div>

    </div>
</div>

<script>
    function refreshPage() {
        window.location.href = "UserAcc.php"; // ✅ Ensures proper refresh
    }
</script>


<script src="progress.js"></script>

</body>
</html>
