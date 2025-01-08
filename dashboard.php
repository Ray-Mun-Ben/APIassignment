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

// Fetch all users
$users = $user->getAllUsers();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userIdToDelete = $_POST['delete_user_id'];
    if ($user->deleteUserById($userIdToDelete)) {
        header('Location: dashboard.php'); // Refresh the page
        exit();
    } else {
        $deleteError = "Failed to delete user.";
    }
}

$username = $user->getUsernameById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">MyApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div class="container text-center mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    </div>

    <!-- Users Table -->
    <div class="container mt-5">
        <h2>Registered Users</h2>

        <?php if (isset($deleteError)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($deleteError); ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $userRow): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($userRow['id']); ?></td>
                        <td><?php echo htmlspecialchars($userRow['username']); ?></td>
                        <td><?php echo htmlspecialchars($userRow['email']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="delete_user_id" value="<?php echo htmlspecialchars($userRow['id']); ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
