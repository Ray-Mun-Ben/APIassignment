<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Ensure admin is logged in (Modify this based on your authentication system)
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied: Admin login required.");
}

$pdo = (new Database())->getConnection();

// Fetch all users with accommodations and extras
$query = "SELECT u.id AS user_id, u.username, u.email, 
                 a.room_type, a.wifi, a.breakfast, a.pool, 
                 e.meal_plan, e.gym_activity
          FROM users u
          LEFT JOIN accommodations a ON u.id = a.user_id
          LEFT JOIN extras e ON u.id = e.user_id";

$stmt = $pdo->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $room_type = $_POST['room_type'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $pool = isset($_POST['pool']) ? 1 : 0;
    $meal_plan = $_POST['meal_plan'];
    $gym_activity = $_POST['gym_activity'];

    // Update accommodations
    $acc_stmt = $pdo->prepare("UPDATE accommodations SET room_type = ?, wifi = ?, breakfast = ?, pool = ? WHERE user_id = ?");
    $acc_stmt->execute([$room_type, $wifi, $breakfast, $pool, $user_id]);

    // Update extras
    $extras_stmt = $pdo->prepare("UPDATE extras SET meal_plan = ?, gym_activity = ? WHERE user_id = ?");
    $extras_stmt->execute([$meal_plan, $gym_activity, $user_id]);

    header("Location: admin.php");
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resort Clerk Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage User Bookings</h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Room Type</th>
                    <th>WiFi</th>
                    <th>Breakfast</th>
                    <th>Pool</th>
                    <th>Meal Plan</th>
                    <th>Gym Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="POST">
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <input type="text" name="room_type" value="<?= htmlspecialchars($user['room_type']) ?>" class="form-control">
                            </td>
                            <td>
                                <input type="checkbox" name="wifi" <?= $user['wifi'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="checkbox" name="breakfast" <?= $user['breakfast'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="checkbox" name="pool" <?= $user['pool'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="text" name="meal_plan" value="<?= htmlspecialchars($user['meal_plan']) ?>" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="gym_activity" value="<?= htmlspecialchars($user['gym_activity']) ?>" class="form-control">
                            </td>
                            <td>
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" name="update_user" class="btn btn-success btn-sm">Update</button>
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
