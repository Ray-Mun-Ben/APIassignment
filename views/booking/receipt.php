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

// Fetch accommodation details
$acc_stmt = $pdo->prepare("SELECT room_type, wifi, wifi_price, breakfast, breakfast_price, pool, pool_price FROM accommodations WHERE user_id = :user_id");
$acc_stmt->execute([':user_id' => $user_id]);
$accommodation = $acc_stmt->fetch(PDO::FETCH_ASSOC);

$extras_stmt = $pdo->prepare("SELECT meal_plan, meal_plan_price, gym_activity, gym_activity_price FROM extras WHERE user_id = :user_id");
$extras_stmt->execute([':user_id' => $user_id]);
$extras = $extras_stmt->fetch(PDO::FETCH_ASSOC);

$total_cost = 0;
if ($accommodation['wifi']) $total_cost += $accommodation['wifi_price'];
if ($accommodation['breakfast']) $total_cost += $accommodation['breakfast_price'];
if ($accommodation['pool']) $total_cost += $accommodation['pool_price'];
if ($extras['meal_plan']) $total_cost += $extras['meal_plan_price'];
if ($extras['gym_activity']) $total_cost += $extras['gym_activity_price'];

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
            <tr><td>Room Type</td><td>{$accommodation['room_type']}</td><td>-</td></tr>
            <tr><td>WiFi</td><td>" . ($accommodation['wifi'] ? 'Yes' : 'No') . "</td><td>" . ($accommodation['wifi'] ? '$' . $accommodation['wifi_price'] : '-') . "</td></tr>
            <tr><td>Breakfast</td><td>" . ($accommodation['breakfast'] ? 'Yes' : 'No') . "</td><td>" . ($accommodation['breakfast'] ? '$' . $accommodation['breakfast_price'] : '-') . "</td></tr>
            <tr><td>Pool Access</td><td>" . ($accommodation['pool'] ? 'Yes' : 'No') . "</td><td>" . ($accommodation['pool'] ? '$' . $accommodation['pool_price'] : '-') . "</td></tr>
        </table>

        <h4>Extras</h4>
        <table class='table'>
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Meal Plan</td><td>" . ($extras['meal_plan'] ? 'Yes' : 'No') . "</td><td>" . ($extras['meal_plan'] ? '$' . $extras['meal_plan_price'] : '-') . "</td></tr>
            <tr><td>Gym Activity</td><td>" . ($extras['gym_activity'] ? 'Yes' : 'No') . "</td><td>" . ($extras['gym_activity'] ? '$' . $extras['gym_activity_price'] : '-') . "</td></tr>
        </table>

        <h3 class='text-end'>Total Cost: $$total_cost</h3>
        <hr>
        <p class='text-center'>Thank you for choosing our resort!</p>
    </div>
</body>
</html>";

// Display receipt page
if (!isset($_GET['download'])) {
    echo $html;
    echo "
    <div class='container text-center mt-3'>
        <a href='receipt.php?download=1' class='btn btn-primary'>Download as PDF</a>
    </div>";
} else {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("receipt.pdf", ["Attachment" => true]);
}
?>
