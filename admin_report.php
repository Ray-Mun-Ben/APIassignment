<?php
require_once 'database.php';
$pdo = (new Database())->connect();

// 🟢 1. Get total reservations per room type
$roomTypeStmt = $pdo->query("
    SELECT room_type, COUNT(*) AS total 
    FROM reservations 
    GROUP BY room_type
");
$roomTypeData = $roomTypeStmt->fetchAll(PDO::FETCH_ASSOC);

// 🟢 2. Get reservations status count
$statusStmt = $pdo->query("
    SELECT status, COUNT(*) AS total 
    FROM reservations 
    GROUP BY status
");
$statusData = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

// 🟢 3. Get revenue from extras
$extrasStmt = $pdo->query("
    SELECT 
        SUM(meal_plan_price) AS meals, 
        SUM(gym_activity_price) AS gym, 
        SUM(pool) * 20 AS pool,
        SUM(wifi) * 10 AS wifi
    FROM reservations
");
$extrasData = $extrasStmt->fetch(PDO::FETCH_ASSOC);

// 🟢 4. Get user registrations per month
$userStmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total 
    FROM users 
    GROUP BY month 
    ORDER BY month ASC
");
$userData = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// 🟢 Return JSON response
echo json_encode([
    'room_types' => $roomTypeData,
    'statuses' => $statusData,
    'extras' => $extrasData,
    'users' => $userData
]);
?>
