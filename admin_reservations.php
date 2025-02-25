<?php
require_once 'database.php';
require_once 'Reservation.php';
require_once 'User.php';
require_once 'mailerClass.php'; // ✅ Ensure this is correct
require_once 'Booking.php'; // ✅ Ensure Booking class is included

session_start();

// ✅ Ensure Admin is Logged In
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$pdo = (new Database())->getConnection();
$reservationObj = new Reservation($pdo);
$userObj = new User($pdo);
$mailer = new Mailer(); 
$bookingObj = new Booking($pdo); 

// ✅ Fetch all pending reservations (Fix for Undefined Variable)
$reservations = $reservationObj->getAllPendingReservations();

// ✅ Ensure $reservations is always an array
if (!is_array($reservations)) {
    $reservations = []; // ✅ Ensure no undefined variable errors
}

// ✅ Handle reservation approval
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["approve_reservation"])) {
    $reservationId = $_POST["reservation_id"];
    $userId = $_POST["user_id"];

    if ($reservationObj->approveReservation($reservationId)) {
        $user = $userObj->getUserById($userId);

        // ✅ Automatically create a booking after approval
        $bookingObj->confirmBooking($userId);

        // ✅ Send confirmation email
        $subject = "Your Reservation Has Been Accepted!";
        $message = "
            Dear {$user['name']},<br><br>
            Your reservation at Feel Fresh Resort has been successfully accepted!<br>
            Please make full payment within **5 hours** to secure your booking.<br>
            Failure to pay on time will result in automatic cancellation.<br><br>
            **Repeated non-payments will result in a ban.**<br>
            Kindly make your payment at the reception or via online transfer.<br><br>
            Best regards,<br>
            Feel Fresh Resort Team
        ";

        $mailer->sendMail($user['email'], $subject, $message);
        header("Location: admin_reservations.php?success=Reservation accepted and email sent.");
        exit();
    } else {
        $error = "Failed to approve reservation.";
    }
}

// ✅ Handle reservation rejection
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reject_reservation"])) {
    $reservationId = $_POST["reservation_id"];
    $userId = $_POST["user_id"];

    // ✅ Increase user's unpaid reservation count
    $userObj->incrementUnpaidCount($userId);
    
    // ✅ Check if the user should be banned
    if ($userObj->shouldBanUser($userId)) {
        $userObj->banUser($userId);
    }

    // ✅ Delete the reservation
    if ($reservationObj->rejectReservation($reservationId)) {
        header("Location: admin_reservations.php?success=Reservation rejected.");
        exit();
    } else {
        $error = "Failed to reject reservation.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<!-- ✅ Admin Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Feel Fresh Resort - Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php">Manage Bookings</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_reservations.php">Manage Reservations</a></li>
                <li class="nav-item"><a class="nav-link" href="Admin_dashboard.php">Admin Dashboard</a></li>
                <li class="nav-item">
                    <form method="POST" action="admin_logout.php">
                        <button type="submit" name="logout" class="btn btn-danger">Sign Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Reservations Table -->
<div class="container mt-5">
    <h2>Pending Reservations</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Room Type</th>
                <th>Days</th>
                <th>Reservation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($reservations as $res) : ?>
        <tr>
            <td><?= htmlspecialchars($res['username'] ?? 'Unknown') ?></td>  <!-- ✅ Fixed key -->
            <td><?= htmlspecialchars($res['email'] ?? 'No Email') ?></td>
            <td><?= htmlspecialchars($res['room_type'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($res['days'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($res['reservation_date'] ?? 'N/A') ?></td>
            <td>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($res['id']) ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($res['user_id']) ?>">
                    <button type="submit" name="approve_reservation" class="btn btn-success btn-sm">Accept</button>
                </form>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($res['id']) ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($res['user_id']) ?>">
                    <button type="submit" name="reject_reservation" class="btn btn-danger btn-sm">Reject</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>


    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
