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

// ✅ Initialize classes
$userObj = new User($pdo);
$reservationObj = new Reservation($pdo);
$bookingObj = new Booking($pdo);

// ✅ Fetch user details
$user = $userObj->getUserById($user_id);

// ✅ Fetch latest reservation
$reservation = $reservationObj->getLatestReservation($user_id);
if (!$reservation) {
    die("Error: No reservation found.");
}

// ✅ Fetch existing booking
$booking = $bookingObj->getBookingByUser($user_id);

// ✅ Convert gym activities to readable format
$gym_activities_display = (!empty($reservation['gym_activity'])) 
    ? nl2br(htmlspecialchars($reservation['gym_activity'])) 
    : '-';

// ✅ Calculate total cost
$total_cost = $reservation['room_price'] * $reservation['days'] +
    ($reservation['wifi'] ? $reservation['wifi_price'] * $reservation['days'] : 0) +
    ($reservation['breakfast'] ? $reservation['breakfast_price'] * $reservation['days'] : 0) +
    ($reservation['pool'] ? $reservation['pool_price'] * $reservation['days'] : 0) +
    $reservation['meal_plan_price'] + $reservation['gym_activity_price'];

// ✅ Handle PDF Export
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['export_pdf'])) {
    ob_start();
    include 'receipt_template.php'; // ✅ Load template content
    $pdf_html = ob_get_clean();

    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($pdf_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Booking_Receipt.pdf", ["Attachment" => true]);
    exit();
}
?>

<!-- ✅ Display the receipt template directly on receipt.php -->
<?php include 'receipt_template.php'; ?>

<div class="text-center mt-3">
    <form method="POST">
        <button type="submit" name="export_pdf" class="btn btn-primary">Download PDF</button>
    </form>
</div>
