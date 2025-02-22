<?php
require_once 'database.php';

class ExtrasM {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function saveExtras($userId, $mealPlan, $mealPlanPrice, $gymActivity, $gymActivityPrice) {
        $stmt = $this->pdo->prepare("
            INSERT INTO extras (user_id, meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            VALUES (:user_id, :meal_plan, :meal_plan_price, :gym_activity, :gym_activity_price)
            ON DUPLICATE KEY UPDATE
            meal_plan = VALUES(meal_plan), meal_plan_price = VALUES(meal_plan_price),
            gym_activity = VALUES(gym_activity), gym_activity_price = VALUES(gym_activity_price)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':meal_plan' => $mealPlan,
            ':meal_plan_price' => $mealPlanPrice,
            ':gym_activity' => $gymActivity,
            ':gym_activity_price' => $gymActivityPrice
        ]);
    }

    public function getExtras($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM extras WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getUserExtras($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT meal_plan, meal_plan_price, gym_activity, gym_activity_price 
            FROM extras 
            WHERE user_id = :user_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
?>
