<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'Database.php';

$database = new Database();
$pdo = $database->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;
    $reservation_date = $_POST['reservation_date'];

    $stmt = $pdo->prepare("INSERT INTO accommodations (user_id, room_type, room_price, wifi, breakfast, pool, reservation_date) VALUES (:user_id, :room_type, :room_price, :wifi, :breakfast, :pool, :reservation_date) ON DUPLICATE KEY UPDATE room_type = VALUES(room_type), room_price = VALUES(room_price), wifi = VALUES(wifi), breakfast = VALUES(breakfast), pool = VALUES(pool), reservation_date = VALUES(reservation_date)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':room_type' => $room_type,
        ':room_price' => $room_price,
        ':wifi' => $wifi,
        ':breakfast' => $breakfast,
        ':pool' => $pool,
        ':reservation_date' => $reservation_date
    ]);
    header("Location: receipt.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script>
        function updateRoomPrice() {
            const roomSelect = document.getElementById('room_type');
            const roomPriceInput = document.getElementById('room_price');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            roomPriceInput.value = selectedOption.getAttribute('data-price');
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Feel Fresh Resort" height="50">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="extras.php">Extras</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Sign Out</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
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
        <script>
    function updateRoomDetails() {
        const roomSelect = document.getElementById('room_type');
        const roomPriceInput = document.getElementById('room_price');
        const daysInput = document.getElementById('days');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];

        // Set the room price
        roomPriceInput.value = selectedOption.getAttribute('data-price');

        // Set max days limit based on room type
        let maxDays = 10; // Default for Standard
        if (roomSelect.value === "deluxe") {
            maxDays = 20;
        } else if (roomSelect.value === "suite") {
            maxDays = 28;
        }

        daysInput.max = maxDays;
        if (parseInt(daysInput.value) > maxDays) {
            daysInput.value = maxDays;
        }
    }
</script>

<form id="accommodationForm" method="POST" action="grid.php" class="p-3 border rounded shadow-sm bg-light">
    <div class="mb-2">
        <label for="room_type" class="form-label">Room Type:</label>
        <select id="room_type" name="room_type" class="form-select" onchange="updateRoomDetails()">
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

    <div class="mb-2">
        <label for="reservation_date" class="form-label">Reservation Date:</label>
        <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Save Accommodation</button>
</form>

    </div>
    
    <footer class="bg-dark text-white text-center p-3 mt-5">
        <p>&copy; 2025 Feel Fresh Resort. All Rights Reserved.</p>
    </footer>
    
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
