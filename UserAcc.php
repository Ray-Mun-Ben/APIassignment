<?php
require_once 'database.php';
require_once 'user.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

$db = new Database();
$user = new User($db->getConnection());
$userId = $_SESSION['user_id'];

// Get user details from grid.php
$userDetails = $user->getUserById($userId);

// Get extras (meal plan & gym activities) from extras.php
$userExtras = $user->getUserExtras($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Feel Fresh Resort</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="grid.php">Grid</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="extras.php">Extras</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Sign Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center">User Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>User Details</h4>
                <p><strong>Name:</strong> <?= isset($userDetails['name']) ? htmlspecialchars($userDetails['name']) : 'N/A' ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($userDetails['email']) ?></p>
            </div>
            <div class="col-md-6">
                <h4>Selected Extras</h4>
                <p><strong>Meal Plan:</strong> <?= !empty($userExtras['meal_plan']) ? htmlspecialchars($userExtras['meal_plan']) : 'None' ?></p>
                <p><strong>Gym Activity:</strong> <?= !empty($userExtras['gym_activity']) ? htmlspecialchars($userExtras['gym_activity']) : 'None' ?></p>
            </div>
        </div>
        <a href="extras.php" class="btn btn-primary mt-3">Update Extras</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

