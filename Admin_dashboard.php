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

// Fetch admin username from database
$adminStmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$adminStmt->execute([$_SESSION['admin_id']]);
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminName = $admin ? htmlspecialchars($admin['username']) : "Admin";

// Fetch all users
$user = new User($pdo);
$users = $user->getAllUsers();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userIdToDelete = $_POST['delete_user_id'];
    if ($user->deleteUserById($userIdToDelete)) {
        header('Location: Admin_dashboard.php'); // Refresh the page
        exit();
    } else {
        $deleteError = "Failed to delete user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Feel Fresh Resort - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                        <a class="nav-link" href="admin_home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Manage Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Admin_dashboard.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="admin_logout.php">
                            <button type="submit" name="logout" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Welcome Message -->
    <div class="container text-center mt-5">
        <h1>Welcome, <?php echo $adminName; ?>!</h1>
    </div>

    <!-- Users Table -->
    <div class="container mt-5">
        <h2>Registered Users</h2>

        <?php if (isset($deleteError)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($deleteError); ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
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
