<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = (new Database())->getConnection();
$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_type = $_POST['room_type'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO accommodations (user_id, room_type, wifi, breakfast, pool) 
                           VALUES (:user_id, :room_type, :wifi, :breakfast, :pool)
                           ON DUPLICATE KEY UPDATE 
                           room_type = VALUES(room_type), wifi = VALUES(wifi), breakfast = VALUES(breakfast), pool = VALUES(pool)");
    $stmt->execute([
        ':user_id' => $userId,
        ':room_type' => $room_type,
        ':wifi' => $wifi,
        ':breakfast' => $breakfast,
        ':pool' => $pool
    ]);
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Accommodation</title>
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
                </ul>
            </div>
        </div>
    </nav>

<div class="container mt-4">
    <h2>Select Your Accommodation</h2>
    <form method="post">
        <label for="room_type">Choose Room Type:</label>
        <select name="room_type" id="room_type" class="form-select" required>
            <option value="Standard">Standard</option>
            <option value="Deluxe">Deluxe</option>
            <option value="Suite">Suite</option>
        </select>

        <div class="form-check mt-3">
            <input type="checkbox" class="form-check-input" id="wifi" name="wifi">
            <label for="wifi" class="form-check-label">WiFi</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="breakfast" name="breakfast">
            <label for="breakfast" class="form-check-label">Breakfast</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="pool" name="pool">
            <label for="pool" class="form-check-label">Pool Access</label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Preferences</button>
    </form>
</div>
</body>
</html>
