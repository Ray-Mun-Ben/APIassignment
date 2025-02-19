<?php
require_once 'vendor/autoload.php';
require_once 'database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User ID not found in session.");
}

// Fetch user details
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$user_stmt->execute([':user_id' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch latest reservation details
$res_stmt = $pdo->prepare("
    SELECT room_type, room_price, wifi, wifi_price, breakfast, breakfast_price, 
           pool, pool_price, reservation_date, meal_plan, meal_plan_price, 
           gym_activity, gym_activity_price 
    FROM reservations 
    WHERE user_id = :user_id 
    ORDER BY id DESC LIMIT 1");
$res_stmt->execute([':user_id' => $user_id]);
$reservation = $res_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch existing booking
$booking_stmt = $pdo->prepare("
    SELECT id FROM bookings WHERE user_id = :user_id LIMIT 1");
$booking_stmt->execute([':user_id' => $user_id]);
$booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);

// Convert gym activities to readable format
$gym_activities_list = $reservation['gym_activity'] ? explode(", ", $reservation['gym_activity']) : [];
$gym_activities_display = empty($gym_activities_list) ? '-' : implode("<br>", $gym_activities_list);

$total_cost = 0;
if ($reservation) {
    if ($reservation['room_price']) $total_cost += $reservation['room_price'];
    if ($reservation['wifi']) $total_cost += $reservation['wifi_price'];
    if ($reservation['breakfast']) $total_cost += $reservation['breakfast_price'];
    if ($reservation['pool']) $total_cost += $reservation['pool_price'];
    if ($reservation['meal_plan_price']) $total_cost += $reservation['meal_plan_price'];
    if ($reservation['gym_activity_price']) $total_cost += $reservation['gym_activity_price'];
}

// Handle confirming a booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_booking'])) {
    if (!$booking) { // If no booking exists, create one
        $insert_stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, room_type, room_price, wifi, breakfast, pool, reservation_date, 
                                  meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            SELECT user_id, room_type, room_price, wifi, breakfast, pool, reservation_date, 
                   meal_plan, meal_plan_price, gym_activity, gym_activity_price
            FROM reservations 
            WHERE user_id = :user_id");
        $insert_stmt->execute([':user_id' => $user_id]);
    } else { // If booking exists, update it
        $update_stmt = $pdo->prepare("
            UPDATE bookings 
            SET room_type = :room_type, room_price = :room_price, wifi = :wifi, 
                breakfast = :breakfast, pool = :pool, reservation_date = :reservation_date, 
                meal_plan = :meal_plan, meal_plan_price = :meal_plan_price, 
                gym_activity = :gym_activity, gym_activity_price = :gym_activity_price
            WHERE user_id = :user_id");
        $update_stmt->execute([
            ':room_type' => $reservation['room_type'],
            ':room_price' => $reservation['room_price'],
            ':wifi' => $reservation['wifi'],
            ':breakfast' => $reservation['breakfast'],
            ':pool' => $reservation['pool'],
            ':reservation_date' => $reservation['reservation_date'],
            ':meal_plan' => $reservation['meal_plan'],
            ':meal_plan_price' => $reservation['meal_plan_price'],
            ':gym_activity' => $reservation['gym_activity'],
            ':gym_activity_price' => $reservation['gym_activity_price'],
            ':user_id' => $user_id
        ]);
    }
    header("Location: receipt.php");
    exit();
}

// Handle canceling a booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_booking'])) {
    $delete_stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = :user_id");
    $delete_stmt->execute([':user_id' => $user_id]);
    header("Location: receipt.php");
    exit();
}

// Generate receipt HTML
$html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Receipt</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-4'>
        <h2 class='text-center'>Booking Receipt</h2>
        <hr>
        <h4>User Details</h4>
        <p><strong>Username:</strong> {$user['username']}</p>
        <p><strong>Email:</strong> {$user['email']}</p>

        <h4>Accommodation Details</h4>
        <table class='table'>
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Room Type</td><td>{$reservation['room_type']}</td><td>$" . number_format($reservation['room_price'], 2) . "</td></tr>
            <tr><td>Reservation Date</td><td>{$reservation['reservation_date']}</td><td>-</td></tr>
            <tr><td>WiFi</td><td>" . ($reservation['wifi'] ? 'Yes' : 'No') . "</td><td>" . ($reservation['wifi'] ? '$' . number_format($reservation['wifi_price'], 2) : '-') . "</td></tr>
            <tr><td>Breakfast</td><td>" . ($reservation['breakfast'] ? 'Yes' : 'No') . "</td><td>" . ($reservation['breakfast'] ? '$' . number_format($reservation['breakfast_price'], 2) : '-') . "</td></tr>
            <tr><td>Pool Access</td><td>" . ($reservation['pool'] ? 'Yes' : 'No') . "</td><td>" . ($reservation['pool'] ? '$' . number_format($reservation['pool_price'], 2) : '-') . "</td></tr>
        </table>

        <h4>Extras</h4>
        <table class='table'>
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Meal Plan</td><td>{$reservation['meal_plan']}</td><td>" . ($reservation['meal_plan_price'] ? '$' . number_format($reservation['meal_plan_price'], 2) : '-') . "</td></tr>
            <tr><td>Gym Activities</td><td>{$gym_activities_display}</td><td>" . ($reservation['gym_activity_price'] ? '$' . number_format($reservation['gym_activity_price'], 2) : '-') . "</td></tr>
        </table>

        <h3 class='text-end'>Total Cost: $" . number_format($total_cost, 2) . "</h3>
        <hr>
        <p class='text-center'>Thank you for choosing our resort!</p>

        <div class='text-center mt-3'>
            <form method='POST'>
                <button type='submit' name='confirm_booking' class='btn btn-success'>Confirm Booking</button>
                <button type='submit' name='cancel_booking' class='btn btn-danger'>Cancel Booking</button>
                <a href='UserAcc.php' class='btn btn-warning'>Modify Reservation</a>
            </form>
        </div>
    </div>
</body>
</html>";
echo $html;
?>
