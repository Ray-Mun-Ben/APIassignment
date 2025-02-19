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
    $gym_access = isset($_POST['gym_access']) ? 1 : 0;
    $gym_activities = isset($_POST['gym_activities']) ? implode(", ", $_POST['gym_activities']) : '';
    $meal_plan_price = ($_POST['meal_plan'] != '0') ? 20 : 0;
    $gym_activity_price = count($_POST['gym_activities'] ?? []) * 25;

    $stmt = $pdo->prepare("INSERT INTO extras (user_id, meal_plan, meal_plan_price, gym_access, gym_activity, gym_activity_price) 
        VALUES (:user_id, :meal_plan, :meal_plan_price, :gym_access, :gym_activity, :gym_activity_price) 
        ON DUPLICATE KEY UPDATE meal_plan = :meal_plan, meal_plan_price = :meal_plan_price, gym_access = :gym_access, gym_activity = :gym_activity, gym_activity_price = :gym_activity_price");

    if ($stmt->execute([
        ':user_id' => $user_id,
        ':meal_plan' => $meal_plan,
        ':meal_plan_price' => $meal_plan_price,
        ':gym_access' => $gym_access,
        ':gym_activity' => $gym_activities,
        ':gym_activity_price' => $gym_activity_price
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
    <div class="container mt-5">
        <h2 class="text-center">Select Your Extras</h2>

        <form id="extrasForm" method="POST" action="extras.php" class="p-3 border rounded shadow-sm bg-light mt-3">
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

    <div class="fixed-bottom text-end p-3 bg-dark text-white fw-bold shadow-sm">
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
