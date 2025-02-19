<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center text-success">Booking Confirmed</h2>
        <p class="text-center">Thank you for booking with Feel Fresh Resort! Your reservation has been finalized.</p>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-success">Go to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>
