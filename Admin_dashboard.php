<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

// ✅ Fetch admin details
$adminStmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$adminStmt->execute([$_SESSION['admin_id']]);
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminName = $admin ? htmlspecialchars($admin['username']) : "Admin";

// ✅ Initialize User Class
$user = new User($pdo);
$users = $user->getAllUsers(); // Fetch all registered users

$deleteMessage = "";

// ✅ Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userIdToDelete = $_POST['delete_user_id'];
    if ($user->deleteUserById($userIdToDelete)) {
        $deleteMessage = "User deleted successfully.";
        // Refresh users list after deletion
        $users = $user->getAllUsers();
    } else {
        $deleteMessage = "❌ Error: Failed to delete user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function confirmDeletion(userId) {
            return confirm("Are you sure you want to delete User ID: " + userId + "? This action cannot be undone.");
        }
    </script>
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
                    <li class="nav-item"><a class="nav-link" href="admin_home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Manage Bookings</a></li>
                    <li class="nav-item"><a class="nav-link active" href="Admin_dashboard.php">Admin Dashboard</a></li>
                    <li class="nav-item">
                        <form method="POST" action="admin_logout.php">
                            <button type="submit" name="logout" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ✅ Admin Welcome Message -->
    <div class="container text-center mt-5">
        <h1>Welcome, <?= $adminName; ?>!</h1>
        <p class="lead">Manage users, bookings, and more.</p>
    </div>

    <!-- ✅ User Management Section -->
    <div class="container mt-5">
        <h2>Registered Users</h2>

        <!-- ✅ Show delete success/error message -->
        <?php if (!empty($deleteMessage)): ?>
            <div class="alert <?= strpos($deleteMessage, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>">
                <?= htmlspecialchars($deleteMessage) ?>
            </div>
        <?php endif; ?>

        <!-- ✅ User Count -->
        <div class="alert alert-info">
            <strong>Total Users:</strong> <?= count($users); ?>
        </div>

        <!-- ✅ User Table -->
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
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
                        <td><?= htmlspecialchars($userRow['id']); ?></td>
                        <td><?= htmlspecialchars($userRow['username']); ?></td>
                        <td><?= htmlspecialchars($userRow['email']); ?></td>
                        <td>
                            <form method="POST" action="" onsubmit="return confirmDeletion(<?= $userRow['id']; ?>)">
                                <input type="hidden" name="delete_user_id" value="<?= $userRow['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
