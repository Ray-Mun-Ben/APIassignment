<?php
require_once 'database.php';

class Booking {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function confirmBooking($userId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO bookings (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                  meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            SELECT user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                   meal_plan, meal_plan_price, gym_activity, gym_activity_price
            FROM reservations 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
    }

    public function cancelBooking($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }

    public function getBooking($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
