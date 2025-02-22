<?php
require_once 'database.php';

class Reservation {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createReservation($userId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservations (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                      meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            SELECT accommodations.user_id, accommodations.room_type, accommodations.room_price, accommodations.days, 
                   accommodations.wifi, accommodations.breakfast, accommodations.pool, accommodations.reservation_date, 
                   extras.meal_plan, extras.meal_plan_price, extras.gym_activity, extras.gym_activity_price
            FROM accommodations
            LEFT JOIN extras ON accommodations.user_id = extras.user_id
            WHERE accommodations.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
    }

    public function getReservation($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
