<?php
require_once 'database.php';
require_once 'User.php';
require_once 'Booking.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied: Admin login required.");
}

$pdo = (new Database())->getConnection();
$userObj = new User($pdo);
$bookingObj = new Booking($pdo);

// Pagination setup
$limit = 20; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch bookings with users
$bookings = $bookingObj->getAllBookings($limit, $offset);
$totalBookings = $bookingObj->getTotalBookings();
$totalPages = ceil($totalBookings / $limit);

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $bookingObj->deleteBooking($_POST['booking_id']);
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Feel Fresh Resort - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_setting.php">Manage seasonal rate</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_reservations.php">Manage Reservations</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin.php">Manage Bookings</a></li>
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

    <!-- Content Section -->
    <div class="container mt-5">
        <h2>Manage Bookings</h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Room Type</th>
                    <th>Reservation Date</th>
                    <th>Days</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['id']) ?></td>
                        <td><?= htmlspecialchars($booking['username']) ?></td>
                        <td><?= htmlspecialchars($booking['email']) ?></td>
                        <td><?= htmlspecialchars($booking['room_type']) ?></td>
                        <td><?= htmlspecialchars($booking['reservation_date']) ?></td>
                        <td><?= htmlspecialchars($booking['days']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <button type="submit" name="delete_booking" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                            <a href="edit_booking.php?id=<?= $booking['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="admin.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

</body>
</html>
