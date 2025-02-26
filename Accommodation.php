<?php
require_once 'database.php';

class Accommodation {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Save or update accommodation details
    public function saveAccommodation($userId, $roomType, $roomPrice, $days, $wifi, $breakfast, $pool, $reservationDate) {
        $stmt = $this->pdo->prepare("
            INSERT INTO accommodations (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date) 
            VALUES (:user_id, :room_type, :room_price, :days, :wifi, :breakfast, :pool, :reservation_date)
            ON DUPLICATE KEY UPDATE
            room_type = VALUES(room_type), 
            room_price = VALUES(room_price), 
            days = VALUES(days), 
            wifi = VALUES(wifi), 
            breakfast = VALUES(breakfast), 
            pool = VALUES(pool), 
            reservation_date = VALUES(reservation_date)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':room_type' => $roomType,
            ':room_price' => $roomPrice,  // ✅ Ensure this is passed correctly
            ':days' => $days,
            ':wifi' => $wifi,
            ':breakfast' => $breakfast,
            ':pool' => $pool,
            ':reservation_date' => $reservationDate
        ]);
    }
    
    // Get latest accommodation details
    public function getAccommodation($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get latest accommodation for a user
    public function getLatestAccommodation($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM accommodations 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
}

// ✅ **Added Missing Closing Brace**
?>
