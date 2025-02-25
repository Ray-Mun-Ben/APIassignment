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
    // ✅ Prevent Undefined Index Errors
    $room_type = isset($_POST['room_type']) ? $_POST['room_type'] : '';
    $room_price = isset($_POST['room_price']) ? $_POST['room_price'] : 0;
    $days = isset($_POST['days']) ? $_POST['days'] : 1;
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;
    $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : '';

    if (!empty($room_type) && !empty($reservation_date)) {
        $accommodation->saveAccommodation($user_id, $room_type, $room_price, $days, $wifi, $breakfast, $pool, $reservation_date);
        header("Location: extras.php");
        exit();
    }
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

<!-- ✅ Navbar -->
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
            </ul>
            <!-- ✅ Move Sign Out Button Here -->
            <form method="POST" action="logout.php" class="d-flex ms-3">
                <button type="submit" class="btn btn-danger">Sign Out</button>
            </form>
        </div>
    </div>
</nav>

<!-- ✅ Progress Tracker -->
<div class="container mt-3">
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <ul class="nav nav-pills nav-justified mt-2">
        <li class="nav-item"><a class="nav-link" id="step1" href="grid.php">Step 1: Select Room</a></li>
        <li class="nav-item"><a class="nav-link" id="step2" href="extras.php">Step 2: Choose Extras</a></li>
        <li class="nav-item"><a class="nav-link" id="step3" href="UserAcc.php">Step 3: Review & Reserve</a></li>
        <li class="nav-item"><a class="nav-link" id="step4" href="receipt.php">Step 4: Get Receipt</a></li>
    </ul>
</div>

<!-- ✅ Accommodation Selection -->
<div class="container mt-4">
    <h2 class="text-center">Select Your Accommodation</h2>

    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card">
                <img src="images/room1.jpg" class="card-img-top" alt="Standard Room">
                <div class="card-body">
                    <h5 class="card-title">Standard Room</h5>
                    <p class="card-text">A cozy and affordable stay with essential amenities.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="images/room2.jpg" class="card-img-top" alt="Deluxe Room">
                <div class="card-body">
                    <h5 class="card-title">Deluxe Room</h5>
                    <p class="card-text">Experience comfort with a spacious deluxe room.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="images/room3.jpg" class="card-img-top" alt="Suite Room">
                <div class="card-body">
                    <h5 class="card-title">Suite Room</h5>
                    <p class="card-text">Luxury and elegance combined in our suite.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Accommodation Form -->
    
    <form id="accommodationForm" method="POST" action="grid.php" class="p-3 border rounded shadow-sm bg-light">
    <div class="mb-2">
        <label for="reservation_date" class="form-label">Reservation Date:</label>
        <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
    </div>

    <div class="mb-2">
        <label for="room_type" class="form-label">Room Type:</label>
        <select id="room_type" name="room_type" class="form-select">
            <option value="standard" data-price="50">Standard ($50 per night, Max 10 days)</option>
            <option value="deluxe" data-price="100">Deluxe ($100 per night, Max 20 days)</option>
            <option value="suite" data-price="150">Suite ($150 per night, Max 28 days)</option>
        </select>
    </div>

    <input type="hidden" id="room_price" name="room_price" value="50">

    <div class="mb-2">
        <label for="days" class="form-label">Number of Days:</label>
        <input type="number" id="days" name="days" class="form-control" min="1" value="1" required>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="wifi" name="wifi" data-price="10">
        <label class="form-check-label" for="wifi">WiFi ($10)</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="breakfast" name="breakfast" data-price="15">
        <label class="form-check-label" for="breakfast">Breakfast ($15)</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="pool" name="pool" data-price="20">
        <label class="form-check-label" for="pool">Pool Access ($20)</label>
    </div>

    <div class="text-end fw-bold mt-3">
        <h5>Total: <span id="totalPrice">$50.00</span></h5>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Save Accommodation</button>
</form>

<!-- ✅ Place JavaScript at the Bottom to Ensure It Runs After Page Loads -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    function updateTotal() {
        let total = 0;
        const roomSelect = document.getElementById('room_type');
        const daysInput = document.getElementById('days');
        const totalPriceDisplay = document.getElementById('totalPrice');

        if (!roomSelect || !daysInput || !totalPriceDisplay) {
            console.error("Missing elements for total calculation!");
            return;
        }

        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const roomPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const days = parseInt(daysInput.value) || 1;

        total += roomPrice * days;

        document.querySelectorAll('input[type=checkbox]:checked').forEach(el => {
            total += (parseFloat(el.dataset.price) || 0) * days;
        });

        totalPriceDisplay.textContent = '$' + total.toFixed(2);
    }

    function updateRoomDetails() {
        const roomSelect = document.getElementById('room_type');
        const daysInput = document.getElementById('days');

        let maxDays = 10;
        if (roomSelect.value === "deluxe") {
            maxDays = 20;
        } else if (roomSelect.value === "suite") {
            maxDays = 28;
        }

        daysInput.max = maxDays;
        if (parseInt(daysInput.value) > maxDays) {
            daysInput.value = maxDays;
        }

        updateTotal();
    }

    document.getElementById("room_type").addEventListener("change", updateRoomDetails);
    document.getElementById("days").addEventListener("input", updateTotal);
    document.querySelectorAll("input[type=checkbox]").forEach(el => {
        el.addEventListener("change", updateTotal);
    });

    updateRoomDetails();
});
</script>
