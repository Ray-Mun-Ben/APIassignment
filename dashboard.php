<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'Database.php';
require_once 'User.php';

$database = new Database();
$pdo = $database->connect();
$user = new User($pdo);

// Fetch username from the database
$userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $userData ? htmlspecialchars($userData['username']) : "Guest";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feel Fresh Resort - User Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="main-layout">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Feel Fresh Resort</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="grid.php">GridTest</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Accommodation</a></li>
                    <li class="nav-item">
                        <form method="POST" action="logout.php">
                            <button type="submit" name="logout" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div class="container text-center mt-5">
        <h1>Welcome, <?php echo $username; ?>!</h1>
    </div>

    <!-- Book Now Button -->
    <div class="container text-center mt-4">
        <a href="grid.php" class="btn btn-success btn-lg">Book Now</a>
    </div>

    <!-- User Options -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <a href="UserAcc.php" class="btn btn-primary w-100">Manage My Booking</a>
            </div>
            <div class="col-md-6">
                <a href="extras.php" class="btn btn-secondary w-100">View Extras</a>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>