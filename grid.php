<?php
require_once 'database.php';
session_start();

$database = new Database();
$pdo = $database->getConnection();

// Fetch accommodation options (This will later be dynamic based on DB entries)
$accommodations = [
    ['id' => 1, 'name' => 'Deluxe Room', 'image' => 'deluxe.jpg'],
    ['id' => 2, 'name' => 'Suite', 'image' => 'suite.jpg'],
    ['id' => 3, 'name' => 'Standard Room', 'image' => 'standard.jpg']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    $selectedRoom = $_POST['room_type'] ?? null;
    $amenities = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';

    if ($userId && $selectedRoom) {
        $stmt = $pdo->prepare("INSERT INTO user_accommodation (user_id, room_type, amenities) VALUES (:user_id, :room_type, :amenities)");
        $stmt->execute([
            ':user_id' => $userId,
            ':room_type' => $selectedRoom,
            ':amenities' => $amenities
        ]);
        echo "<p class='text-success'>Selection saved successfully!</p>";
    } else {
        echo "<p class='text-danger'>Please select a room type.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2 class="mb-4">Choose Your Accommodation</h2>
    <form method="POST" class="row g-3">
        <?php foreach ($accommodations as $acc): ?>
            <div class="col-md-4">
                <div class="card">
                    <img src="images/<?= $acc['image'] ?>" class="card-img-top" alt="<?= $acc['name'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $acc['name'] ?></h5>
                        <input type="radio" name="room_type" value="<?= $acc['name'] ?>"> Select
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12">
            <h4>Select Amenities</h4>
            <label><input type="checkbox" name="amenities[]" value="WiFi"> WiFi</label>
            <label><input type="checkbox" name="amenities[]" value="Breakfast"> Breakfast</label>
            <label><input type="checkbox" name="amenities[]" value="Pool Access"> Pool Access</label>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Confirm Selection</button>
        </div>
    </form>
</body>
</html>
