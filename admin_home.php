<?php
session_start();
require_once 'database.php';

$pdo = (new Database())->getConnection();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch key statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalReservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    
    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- ✅ Chart.js & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ Custom Styles -->
</head>
<body>

    <!-- ✅ Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Feel Fresh Resort - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="admin_home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Manage Bookings</a></li>
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

    <!-- ✅ Dashboard Overview -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Welcome, Admin!</h1>
        <p class="text-center">Use the navigation bar to manage user bookings and account details.</p>

        <div class="row text-center">
            <!-- 🟢 Users Card -->
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x"></i>
                        <h4 class="card-title mt-3">Total Users</h4>
                        <p class="card-text fs-4"><?= $totalUsers ?></p>
                    </div>
                </div>
            </div>

            <!-- 🟢 Bookings Card -->
            <div class="col-md-4">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-3x"></i>
                        <h4 class="card-title mt-3">Total Bookings</h4>
                        <p class="card-text fs-4"><?= $totalBookings ?></p>
                    </div>
                </div>
            </div>

            <!-- 🟢 Reservations Card -->
            <div class="col-md-4">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-bed fa-3x"></i>
                        <h4 class="card-title mt-3">Total Reservations</h4>
                        <p class="card-text fs-4"><?= $totalReservations ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Reports Section -->
        <div class="mt-5">
            <h3 class="text-center">Reports & Statistics</h3>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="roomChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ✅ Quick Links -->
        <div class="mt-5 text-center">
            <a href="admin.php" class="btn btn-outline-primary m-2">Manage Bookings</a>
            <a href="Admin_dashboard.php" class="btn btn-outline-success m-2">Admin Dashboard</a>
        </div>
    </div>

    <!-- ✅ Fetch Report Data -->
    <script>
    $(document).ready(function () {
        $.ajax({
            url: "admin_report.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
                let roomLabels = data.room_types.map(item => item.room_type);
                let roomCounts = data.room_types.map(item => item.total);
                new Chart(document.getElementById('roomChart'), {
                    type: 'pie',
                    data: { labels: roomLabels, datasets: [{ data: roomCounts, backgroundColor: ['#007bff', '#28a745', '#ffc107'] }] }
                });

                let statusLabels = data.statuses.map(item => item.status);
                let statusCounts = data.statuses.map(item => item.total);
                new Chart(document.getElementById('statusChart'), {
                    type: 'bar',
                    data: { labels: statusLabels, datasets: [{ label: 'Reservations', data: statusCounts, backgroundColor: ['#007bff', '#ffc107', '#dc3545'] }] }
                });

                let revenueLabels = ['Meal Plans', 'Gym', 'Pool', 'WiFi'];
                let revenueValues = [data.extras.meals || 0, data.extras.gym || 0, data.extras.pool || 0, data.extras.wifi || 0];
                new Chart(document.getElementById('revenueChart'), {
                    type: 'doughnut',
                    data: { labels: revenueLabels, datasets: [{ data: revenueValues, backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0'] }] }
                });

                let userLabels = data.users.map(item => item.month);
                let userCounts = data.users.map(item => item.total);
                new Chart(document.getElementById('userChart'), {
                    type: 'line',
                    data: { labels: userLabels, datasets: [{ label: 'New Users', data: userCounts, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 2 }] }
                });
            }
        });
    });
    </script>

</body>
</html>
