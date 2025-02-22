<?php
// Ensure required data is available
if (!isset($user, $reservation, $total_cost, $gym_activities_display)) {
    die("Error: Missing receipt data.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #007bff;
            font-weight: bold;
        }
        .summary {
            margin-bottom: 15px;
        }
        .summary p {
            margin-bottom: 5px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            text-align: left;
            padding: 10px;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .total-cost {
            font-size: 1.3rem;
            font-weight: bold;
            text-align: right;
            color: #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h2>Booking Receipt</h2>
            <p>Feel Fresh Resort - Thank you for your reservation!</p>
        </div>

        <div class="summary">
            <h4>User Details</h4>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <h4>Accommodation Details</h4>
        <table class="table">
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Room Type</td><td><?= htmlspecialchars($reservation['room_type']) ?></td>
                <td>$<?= number_format($reservation['room_price'], 2) ?></td></tr>
            <tr><td>Days</td><td><?= $reservation['days'] ?></td><td>-</td></tr>
            <tr><td>Reservation Date</td><td><?= htmlspecialchars($reservation['reservation_date']) ?></td><td>-</td></tr>
            <tr><td>WiFi</td><td><?= $reservation['wifi'] ? 'Yes' : 'No' ?></td>
                <td><?= $reservation['wifi'] ? '$' . number_format($reservation['wifi_price'] * $reservation['days'], 2) : '-' ?></td></tr>
            <tr><td>Breakfast</td><td><?= $reservation['breakfast'] ? 'Yes' : 'No' ?></td>
                <td><?= $reservation['breakfast'] ? '$' . number_format($reservation['breakfast_price'] * $reservation['days'], 2) : '-' ?></td></tr>
            <tr><td>Pool Access</td><td><?= $reservation['pool'] ? 'Yes' : 'No' ?></td>
                <td><?= $reservation['pool'] ? '$' . number_format($reservation['pool_price'] * $reservation['days'], 2) : '-' ?></td></tr>
        </table>

        <h4>Extras</h4>
        <table class="table">
            <tr><th>Feature</th><th>Selected</th><th>Price</th></tr>
            <tr><td>Meal Plan</td><td><?= htmlspecialchars($reservation['meal_plan']) ?></td>
                <td><?= $reservation['meal_plan_price'] ? '$' . number_format($reservation['meal_plan_price'], 2) : '-' ?></td></tr>
            <tr><td>Gym Activities</td><td><?= $gym_activities_display ?></td>
                <td><?= $reservation['gym_activity_price'] ? '$' . number_format($reservation['gym_activity_price'], 2) : '-' ?></td></tr>
        </table>

        <p class="total-cost">Total Cost: $<?= number_format($total_cost, 2) ?></p>

        <div class="footer">
            <p>&copy; <?= date("Y") ?> Feel Fresh Resort. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
