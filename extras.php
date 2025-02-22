<?php
require_once 'database.php';
require_once 'User.php';
require_once 'ExtrasM.php';

session_start();

$database = new Database();
$pdo = $database->connect();

$user = new User($pdo);
$extras = new ExtrasM($pdo);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User ID not found in session.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_plan = $_POST['meal_plan'] ?? '';
    $gym_access = isset($_POST['gym_access']) ? 1 : 0;
    $gym_activities = isset($_POST['gym_activities']) ? implode(", ", $_POST['gym_activities']) : '';
    $meal_plan_price = ($_POST['meal_plan'] != '0') ? 20 : 0;
    $gym_activity_price = count($_POST['gym_activities'] ?? []) * 25;

    $extras->saveExtras($user_id, $meal_plan, $meal_plan_price, $gym_access, $gym_activities, $gym_activity_price);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extras Selection</title>
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Feel Fresh Resort</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="grid.php">Reservation</a></li>
                    <li class="nav-item"><a class="nav-link" href="extras.php">Extra Amenities</a></li>
                    <li class="nav-item"><a class="nav-link" href="UserAcc.php">User Details</a></li>
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

        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card">
                    <img src="images/meal1.jpg" class="card-img-top" alt="Standard Meal Plan">
                    <div class="card-body">
                        <h5 class="card-title">Standard Meal Plan</h5>
                        <p class="card-text">A balanced meal plan with essential nutrients.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="images/meal2.jpg" class="card-img-top" alt="Premium Meal Plan">
                    <div class="card-body">
                        <h5 class="card-title">Premium Meal Plan</h5>
                        <p class="card-text">An upgraded meal plan with gourmet options.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="images/meal3.jpg" class="card-img-top" alt="Custom Meal Plan">
                    <div class="card-body">
                        <h5 class="card-title">Custom Meal Plan</h5>
                        <p class="card-text">Personalized meal options tailored to your needs.</p>
                    </div>
                </div>
            </div>
        </div>

        <form id="extrasForm" method="POST" action="extras.php" class="p-3 border rounded shadow-sm bg-light mt-3 mb-5">
            <h4>Extra Amenities</h4>
            <div class="mb-2">
                <label for="meal_plan" class="form-label">Meal Plan:</label>
                <select id="meal_plan" name="meal_plan" class="form-select" data-price="20">
                    <option value="0">No Meal Plan</option>
                    <option value="Vegan">Vegan Meal Plan ($20)</option>
                    <option value="Keto">Keto Meal Plan ($20)</option>
                </select>
            </div>

            <div class="mb-2">
                <h5>Gym Activities</h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gym_access" name="gym_access" value="1" data-price="25">
                    <label class="form-check-label" for="gym_access">Gym Access ($25)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="yoga" name="gym_activities[]" value="Yoga" data-price="25">
                    <label class="form-check-label" for="yoga">Yoga ($25)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="weightlifting" name="gym_activities[]" value="Weightlifting" data-price="25">
                    <label class="form-check-label" for="weightlifting">Weightlifting ($25)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cardio" name="gym_activities[]" value="Cardio" data-price="25">
                    <label class="form-check-label" for="cardio">Cardio ($25)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pilates" name="gym_activities[]" value="Pilates" data-price="25">
                    <label class="form-check-label" for="pilates">Pilates ($25)</label>
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">Save Extras</button>
        </form>
    </div>

    <div class="fixed-bottom text-end p-3 bg-dark text-white fw-bold shadow-sm" style="bottom: 10px;">
        Total: <span id="totalPrice">$0.00</span>
    </div>

    <script>
        function updateTotal() {
            let total = 0;
            document.querySelectorAll('input[type=checkbox]:checked, select').forEach(el => {
                if (el.type === 'checkbox' && el.checked) {
                    total += parseFloat(el.dataset.price);
                } else if (el.tagName === 'SELECT' && el.value !== '0') {
                    total += parseFloat(el.dataset.price);
                }
            });
            document.getElementById('totalPrice').textContent = '$' + total.toFixed(2);
        }
        
        document.querySelectorAll('input[type=checkbox], select').forEach(el => {
            el.addEventListener('change', updateTotal);
        });

        document.addEventListener("DOMContentLoaded", updateTotal);
    </script>
</body>
</html>
