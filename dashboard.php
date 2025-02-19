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
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;
    $reservation_date = $_POST['reservation_date'];

    $stmt = $pdo->prepare("INSERT INTO accommodations (user_id, room_type, wifi, breakfast, pool, reservation_date) VALUES (:user_id, :room_type, :wifi, :breakfast, :pool, :reservation_date) ON DUPLICATE KEY UPDATE room_type = VALUES(room_type), wifi = VALUES(wifi), breakfast = VALUES(breakfast), pool = VALUES(pool), reservation_date = VALUES(reservation_date)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':room_type' => $room_type,
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
        <form id="accommodationForm" method="POST" action="grid.php" class="p-3 border rounded shadow-sm bg-light">
            <div class="mb-2">
                <label for="room_type" class="form-label">Room Type:</label>
                <select id="room_type" name="room_type" class="form-select">
                    <option value="standard">Standard ($50)</option>
                    <option value="deluxe">Deluxe ($100)</option>
                    <option value="suite">Suite ($150)</option>
                </select>
            </div>
            <div class="mb-2">
                <label for="reservation_date" class="form-label">Reservation Date:</label>
                <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="wifi" name="wifi">
                <label class="form-check-label" for="wifi">WiFi ($10)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="breakfast" name="breakfast">
                <label class="form-check-label" for="breakfast">Breakfast ($15)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pool" name="pool">
                <label class="form-check-label" for="pool">Pool Access ($20)</label>
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
