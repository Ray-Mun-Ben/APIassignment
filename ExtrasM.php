<?php
require_once 'database.php';

class ExtrasM {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Save user extras
    public function saveExtras($userId, $mealPlan, $mealPlanPrice, $gymAccess, $gymActivities) {
        $gymActivityPrice = count(explode(", ", $gymActivities)) * 25; // ✅ Calculate activity price
    
        $stmt = $this->pdo->prepare("
            INSERT INTO extras (user_id, meal_plan, meal_plan_price, gym_access, gym_activity, gym_activity_price) 
            VALUES (:user_id, :meal_plan, :meal_plan_price, :gym_access, :gym_activity, :gym_activity_price) 
            ON DUPLICATE KEY UPDATE 
            meal_plan = VALUES(meal_plan), meal_plan_price = VALUES(meal_plan_price), 
            gym_access = VALUES(gym_access), gym_activity = VALUES(gym_activity), 
            gym_activity_price = VALUES(gym_activity_price)
        ");
    
        $stmt->execute([
            ':user_id' => $userId,
            ':meal_plan' => $mealPlan,
            ':meal_plan_price' => $mealPlanPrice,
            ':gym_access' => $gymAccess,
            ':gym_activity' => $gymActivities,
            ':gym_activity_price' => $gymActivityPrice
        ]);
    }
    
    // ✅ Get the latest extras for a user (Fix)
    public function getLatestExtras($userId) {
        $stmt = $this->pdo->prepare("
            SELECT meal_plan, meal_plan_price, gym_access, gym_activity, gym_activity_price 
            FROM extras 
            WHERE user_id = :user_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // ✅ Get all extras for a user (Alternative)
    public function getUserExtras($userId) {
        $stmt = $this->pdo->prepare("
            SELECT meal_plan, meal_plan_price, gym_activity, gym_activity_price 
            FROM extras 
            WHERE user_id = :user_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
?>
