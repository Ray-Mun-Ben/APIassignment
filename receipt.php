<?php
require_once 'vendor/autoload.php';
require_once 'database.php';
require_once 'User.php';
require_once 'Reservation.php';
require_once 'Booking.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// ✅ Use the Reservation class to fetch the latest reservation
$reservationObj = new Reservation($pdo);
$reservation = $reservationObj->getLatestReservation($user_id);

if (!$reservation) {
    die("Error: No reservation found.");
}

// Initialize classes
$userObj = new User($pdo);
$reservationObj = new Reservation($pdo);
$bookingObj = new Booking($pdo);
$booking = $bookingObj->getBookingByUser($user_id);

// Fetch user details
$user = $userObj->getUserById($user_id);

// Fetch latest reservation
$reservation = $reservationObj->getLatestReservation($user_id);

// Fetch existing booking
$booking = $bookingObj->getBookingByUser($user_id);

// Convert gym activities to readable format
$gym_activities_display = (!empty($reservation['gym_activity'])) 
    ? str_replace(", ", "<br>", $reservation['gym_activity']) 
    : '-';

// Calculate total cost including days
$total_cost = 0;
$days = isset($reservation['days']) ? (int)$reservation['days'] : 1;
$room_total = $reservation['room_price'] * $days;
$total_cost += $room_total;

if (!empty($reservation['wifi'])) $total_cost += ($reservation['wifi_price'] * $days);
if (!empty($reservation['breakfast'])) $total_cost += ($reservation['breakfast_price'] * $days);
if (!empty($reservation['pool'])) $total_cost += ($reservation['pool_price'] * $days);
if (!empty($reservation['meal_plan_price'])) $total_cost += $reservation['meal_plan_price'];
if (!empty($reservation['gym_activity_price'])) $total_cost += $reservation['gym_activity_price'];

// Handle confirming a booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_booking'])) {
    $bookingObj->confirmBooking($user_id, $reservation);
    header("Location: receipt.php");
    exit();
}

// Handle canceling a booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_booking'])) {
    $bookingObj->cancelBooking($user_id);
    header("Location: receipt.php");
    exit();
}

// Handle PDF export
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_pdf'])) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml(ob_get_clean());  // Load page content for export
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output PDF
    $dompdf->stream("Booking_Receipt.pdf", ["Attachment" => false]);
    exit();
}

// Generate receipt HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Booking Receipt</h2>
        <hr>
        <h4>User Details</h4>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <h4>Accommodation Details</h4>
        <table class="table">
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Room Type</td><td><?= htmlspecialchars($reservation['room_type']) ?></td><td>$<?= number_format($reservation['room_price'], 2) ?></td></tr>
            <tr><td>Days</td><td><?= $reservation['days'] ?></td><td>-</td></tr>
            <tr><td>Reservation Date</td><td><?= htmlspecialchars($reservation['reservation_date']) ?></td><td>-</td></tr>
            <tr><td>WiFi</td><td><?= $reservation['wifi'] ? 'Yes' : 'No' ?></td><td><?= $reservation['wifi'] ? '$' . number_format($reservation['wifi_price'] * $reservation['days'], 2) : '-' ?></td></tr>
            <tr><td>Breakfast</td><td><?= $reservation['breakfast'] ? 'Yes' : 'No' ?></td><td><?= $reservation['breakfast'] ? '$' . number_format($reservation['breakfast_price'] * $reservation['days'], 2) : '-' ?></td></tr>
            <tr><td>Pool Access</td><td><?= $reservation['pool'] ? 'Yes' : 'No' ?></td><td><?= $reservation['pool'] ? '$' . number_format($reservation['pool_price'] * $reservation['days'], 2) : '-' ?></td></tr>
        </table>

        <h4>Extras</h4>
        <table class="table">
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Meal Plan</td><td><?= htmlspecialchars($reservation['meal_plan']) ?></td><td><?= $reservation['meal_plan_price'] ? '$' . number_format($reservation['meal_plan_price'], 2) : '-' ?></td></tr>
            <tr><td>Gym Activities</td><td><?= $gym_activities_display ?></td><td><?= $reservation['gym_activity_price'] ? '$' . number_format($reservation['gym_activity_price'], 2) : '-' ?></td></tr>
        </table>

        <h3 class="text-end">Total Cost: $<?= number_format($total_cost, 2) ?></h3>
        <hr>
        <p class="text-center">Thank you for choosing our resort!</p>

        <div class="text-center mt-3">
            <form method="POST">
                <button type="submit" name="confirm_booking" class="btn btn-success">Confirm Booking</button>
                <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel Booking</button>
                <a href="UserAcc.php" class="btn btn-warning">Modify Reservation</a>
                <button type="submit" name="export_pdf" class="btn btn-primary">Download PDF</button>
            </form>
        </div>
    </div>
</body>
</html>
