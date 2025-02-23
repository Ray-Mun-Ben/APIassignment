<?php
require_once 'database.php';
require_once 'Booking.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$pdo = (new Database())->getConnection();
$bookingObj = new Booking($pdo);

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Booking ID is required.");
}

$bookingId = $_GET['id'];
$booking = $bookingObj->getBookingById($bookingId);

if (!$booking) {
    die("Error: Booking not found.");
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    $roomType = $_POST['room_type'];
    $reservationDate = $_POST['reservation_date'];
    $days = $_POST['days'];

    if ($bookingObj->updateBooking($bookingId, $roomType, $reservationDate, $days)) {
        header("Location: admin.php?success=Booking updated successfully");
        exit();
    } else {
        $error = "Error updating booking.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<!-- Admin Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Feel Fresh Resort - Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Manage Bookings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Admin_dashboard.php">Admin Dashboard</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="admin_logout.php">
                        <button type="submit" name="logout" class="btn btn-danger">Sign Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Edit Booking Form -->
<div class="container mt-5">
    <h2>Edit Booking</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Room Type</label>
            <select name="room_type" class="form-select" required>
                <option value="standard" <?= $booking['room_type'] === 'standard' ? 'selected' : '' ?>>Standard</option>
                <option value="deluxe" <?= $booking['room_type'] === 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
                <option value="suite" <?= $booking['room_type'] === 'suite' ? 'selected' : '' ?>>Suite</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Reservation Date</label>
            <input type="date" name="reservation_date" class="form-control" value="<?= htmlspecialchars($booking['reservation_date']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Number of Days</label>
            <input type="number" name="days" class="form-control" value="<?= htmlspecialchars($booking['days']) ?>" min="1" required>
        </div>

        <button type="submit" name="update_booking" class="btn btn-primary">Update Booking</button>
        <a href="admin.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
