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

    // ✅ New Pricing for Updated Meal Plan Options
    $meal_plan_prices = [
        'None' => 0,
        'Standard' => 15,
        'Premium' => 25,
        'Custom' => 30
    ];
    $meal_plan_price = $meal_plan_prices[$meal_plan] ?? 0;

    $gym_activity_price = count($_POST['gym_activities'] ?? []) * 25; // ✅ Prevent undefined error

    $extrasObj = new ExtrasM($pdo);
    $extrasObj->saveExtras($user_id, $meal_plan, $meal_plan_price, $gym_access, $gym_activities);
}

$extrasSaved = true; // ✅ Indicate extras were saved successfully.

// ✅ Fetch latest extras for logged-in user
$extraData = $extras->getUserExtras($user_id);

if (!$extraData) {
    $extraData = [
        'wifi' => 0,
        'breakfast' => 0,
        'pool' => 0,
        'gym_activity' => 'None'
    ];
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- ✅ Ensure jQuery is included -->
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
                <li class="nav-item"><a class="nav-link" href="receipt.php">Checkout</a></li>
                <a href="logout.php" class="btn btn-danger">Sign Out</a>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Progress Tracker -->
<div class="container mt-3">
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <ul class="nav nav-pills nav-justified mt-2">
        <li class="nav-item">
            <a class="nav-link" id="step1" href="grid.php">Step 1: Select Room</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step2" href="extras.php">Step 2: Choose Extras</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step3" href="UserAcc.php">Step 3: Review & Reserve</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="step4" href="receipt.php">Step 4: Get Receipt</a>
        </li>
    </ul>
</div>

<!-- ✅ Extras Selection -->
<div class="container mt-5">
    <h2 class="text-center">Select Your Extras</h2>

    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card">
                <img src="images/meal1.jpg" class="card-img-top" alt="Standard Meal Plan" data-bs-toggle="tooltip" title="Balanced nutrition with daily essentials, perfect for a standard diet.">
                <div class="card-body">
                    <h5 class="card-title">Standard Meal Plan</h5>
                    <p class="card-text">A balanced meal plan with essential nutrients.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="images/meal2.jpg" class="card-img-top" alt="Premium Meal Plan" data-bs-toggle="tooltip" title="A luxurious meal experience with gourmet dishes and specialty beverages.">
                <div class="card-body">
                    <h5 class="card-title">Premium Meal Plan</h5>
                    <p class="card-text">An upgraded meal plan with gourmet options.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="images/meal3.jpg" class="card-img-top" alt="Custom Meal Plan" data-bs-toggle="tooltip" title="Tailored meals to fit dietary needs and preferences, fully customizable.">
                <div class="card-body">
                    <h5 class="card-title">Custom Meal Plan</h5>
                    <p class="card-text">Personalized meal options tailored to your needs.</p>
                </div>
            </div>
        </div>
    </div>

    <form id="extrasForm" method="POST" action="extras.php" class="p-3 border rounded shadow-sm bg-light mt-3 mb-5">
        <h4>Extra Amenities</h4>
        
        <!-- ✅ Meal Plan Selection -->
        <div class="mb-2">
            <label for="meal_plan" class="form-label">Meal Plan:</label>
            <select id="meal_plan" name="meal_plan" class="form-select" onchange="updateTotal()">
                <option value="None" data-price="0">No Meal Plan</option>
                <option value="Standard" data-price="15">Standard Meal Plan ($15)</option>
                <option value="Premium" data-price="25">Premium Meal Plan ($25)</option>
                <option value="Custom" data-price="30">Custom Meal Plan ($30)</option>
            </select>
        </div>

        <!-- ✅ Gym Activities Selection -->
        <div class="mb-2">
            <h5>Gym Activities</h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="gym_access" name="gym_access" value="1" data-price="25">
                <label class="form-check-label" for="gym_access">Gym Access ($25)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Yoga" data-price="25">
                <label class="form-check-label">Yoga ($25)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Weightlifting" data-price="25">
                <label class="form-check-label">Weightlifting ($25)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Cardio" data-price="25">
                <label class="form-check-label">Cardio ($25)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="gym_activities[]" value="Pilates" data-price="25">
                <label class="form-check-label">Pilates ($25)</label>
            </div>
        </div>

        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary">Save Extras</button>
            <?php if (isset($extrasSaved) && $extrasSaved): ?>
                <a href="UserAcc.php" class="btn btn-success">Next: Review & Reserve</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ✅ Total Price Display -->
<div class="fixed-bottom text-end p-3 bg-dark text-white fw-bold shadow-sm" style="bottom: 10px;">
    Total: <span id="totalPrice">$0.00</span>
</div>

<!-- ✅ JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    function updateTotal() {
        let total = 0;

        // ✅ Get meal plan price
        const mealPlanSelect = document.getElementById('meal_plan');
        const selectedMealOption = mealPlanSelect.options[mealPlanSelect.selectedIndex];
        const mealPrice = parseFloat(selectedMealOption.getAttribute('data-price')) || 0;
        total += mealPrice;

        // ✅ Get gym activities price
        const gymActivityCheckboxes = document.querySelectorAll('input[name="gym_activities[]"]:checked');
        gymActivityCheckboxes.forEach(box => {
            total += parseFloat(box.dataset.price) || 0;
        });

        // ✅ Get gym access price (if checked)
        const gymAccessCheckbox = document.getElementById('gym_access');
        if (gymAccessCheckbox.checked) {
            total += parseFloat(gymAccessCheckbox.dataset.price) || 0;
        }

        // ✅ Display the updated total
        document.getElementById('totalPrice').textContent = '$' + total.toFixed(2);
    }

    // ✅ Event Listeners
    document.getElementById('meal_plan').addEventListener('change', updateTotal);
    document.querySelectorAll('input[type=checkbox]').forEach(el => {
        el.addEventListener('change', updateTotal);
    });

    // ✅ Initial Calculation on Load
    updateTotal();
});
</script>


<script src="progress.js"></script>

</body>
</html>
