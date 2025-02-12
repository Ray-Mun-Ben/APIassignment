<?php
require_once 'database.php';
session_start();

$pdo = (new Database())->getConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User ID not found in session.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_plan = $_POST['meal_plan'] ?? '';
    $gym_activity = isset($_POST['gym_activities']) ? implode(", ", $_POST['gym_activities']) : '';

    $stmt = $pdo->prepare("INSERT INTO extras (user_id, meal_plan, gym_activity) 
        VALUES (:user_id, :meal_plan, :gym_activity) 
        ON DUPLICATE KEY UPDATE meal_plan = :meal_plan, gym_activity = :gym_activity");

    if ($stmt->execute([
        ':user_id' => $user_id,
        ':meal_plan' => $meal_plan,
        ':gym_activity' => $gym_activity
    ])) {
        echo "Extras saved successfully!";
    } else {
        echo "Error: " . implode(" ", $stmt->errorInfo());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extras Selection</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Feel Fresh Resort</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="grid.php">GridTest</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">ExistingUsers</a></li>
                    <li class="nav-item"><a class="nav-link" href="extras.php">Extra Amenities</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Sign In</a></li>
                    <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Accommodation</a></li>
                    <li class="nav-item">
                        <form method="POST" action="">
                            <button type="submit" name="sign_out" class="btn btn-danger">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Select Your Extras</h2>
        <form method="POST" class="mt-4">
            <h3>Meal Plans</h3>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="meal_plan" value="Standard" id="standard">
                <label class="form-check-label" for="standard">Standard</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="meal_plan" value="Vegan" id="vegan">
                <label class="form-check-label" for="vegan">Vegan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="meal_plan" value="Keto" id="keto">
                <label class="form-check-label" for="keto">Keto</label>
            </div>
            
            <h3 class="mt-3">Gym Activities</h3>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Yoga" id="yoga">
                <label class="form-check-label" for="yoga">Yoga</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Weightlifting" id="weightlifting">
                <label class="form-check-label" for="weightlifting">Weightlifting</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Cardio" id="cardio">
                <label class="form-check-label" for="cardio">Cardio</label>
            </div>
            
            <button type="submit" class="btn btn-primary mt-4">Save Preferences</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
