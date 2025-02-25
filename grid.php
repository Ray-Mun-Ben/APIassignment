<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'database.php';
require_once 'User.php';
require_once 'Accommodation.php';

$database = new Database();
$pdo = $database->connect();

$user = new User($pdo);
$accommodation = new Accommodation($pdo);

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $days = $_POST['days'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;
    $reservation_date = $_POST['reservation_date'];

    // Save accommodation using the class
    $accommodation->saveAccommodation($user_id, $room_type, $room_price, $days, $wifi, $breakfast, $pool, $reservation_date);

    header("Location: extras.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay</title>

    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<!-- ✅ Vanta Background -->

<!-- ✅ Content Wrapper to Ensure Scrolling -->
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
                        <form method="POST" action="">
                            <button type="submit" name="sign_out" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
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


    <!-- ✅ Accommodation Selection -->
    <div class="container mt-4">
        <h2 class="text-center">Select Your Accommodation</h2>
        
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/room1.jpg" class="card-img-top" alt="Standard Room">
                    <div class="card-body">
                        <h5 class="card-title">Standard Room</h5>
                        <p class="card-text">A cozy and affordable stay with essential amenities.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/room2.jpg" class="card-img-top" alt="Deluxe Room">
                    <div class="card-body">
                        <h5 class="card-title">Deluxe Room</h5>
                        <p class="card-text">Experience comfort with a spacious deluxe room.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/room3.jpg" class="card-img-top" alt="Suite Room">
                    <div class="card-body">
                        <h5 class="card-title">Suite Room</h5>
                        <p class="card-text">Luxury and elegance combined in our suite.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Accommodation Form -->
        <form id="accommodationForm" method="POST" action="extras.php" class="p-3 border rounded shadow-sm bg-light">
            <div class="mb-2">
                <label for="reservation_date" class="form-label">Reservation Date:</label>
                <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
            </div>
            <div class="mb-2">
                <label for="room_type" class="form-label">Room Type:</label>
                <select id="room_type" name="room_type" class="form-select" onchange="updateRoomDetails()">
                    <option value="standard" data-price="50">Standard ($50 per night, Max 10 days)</option>
                    <option value="deluxe" data-price="100">Deluxe ($100 per night, Max 20 days)</option>
                    <option value="suite" data-price="150">Suite ($150 per night, Max 28 days)</option>
                </select>
            </div>
            <input type="hidden" id="room_price" name="room_price" value="50">

            <button type="submit" class="btn btn-primary mt-3">Save Accommodation</button>
        </form>
    </div>
    
</div> <!-- ✅ Close Content Wrapper -->

<script src="progress.js"></script>


</body>
</html>
