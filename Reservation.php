<?php
require_once 'database.php';

class Reservation {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Fetch the latest reservation for a user
    public function getLatestReservation($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE user_id = :user_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Create a new reservation (Avoids duplication)
    public function createReservation($userId, $accommodation, $extras) {
        // Delete any previous reservations to prevent duplicates
        $this->pdo->prepare("DELETE FROM reservations WHERE user_id = :user_id")->execute([':user_id' => $userId]);
    
        // Insert a new reservation using the latest accommodations and extras
        $stmt = $this->pdo->prepare("
            INSERT INTO reservations (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                      meal_plan, meal_plan_price, gym_activity, gym_activity_price, status)
            VALUES (:user_id, :room_type, :room_price, :days, :wifi, :breakfast, :pool, :reservation_date, 
                    :meal_plan, :meal_plan_price, :gym_activity, :gym_activity_price, 'pending')
        ");
    
        return $stmt->execute([
            ':user_id' => $userId,
            ':room_type' => $accommodation['room_type'] ?? null,
            ':room_price' => $accommodation['room_price'] ?? null,
            ':days' => $accommodation['days'] ?? 1,
            ':wifi' => $accommodation['wifi'] ?? 0,
            ':breakfast' => $accommodation['breakfast'] ?? 0,
            ':pool' => $accommodation['pool'] ?? 0,
            ':reservation_date' => $accommodation['reservation_date'] ?? null,
            ':meal_plan' => $extras['meal_plan'] ?? null,
            ':meal_plan_price' => $extras['meal_plan_price'] ?? null,
            ':gym_activity' => $extras['gym_activity'] ?? null,
            ':gym_activity_price' => $extras['gym_activity_price'] ?? null
        ]);
    }
    

    // ✅ Update an existing reservation
    public function updateReservation($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE reservations SET 
                room_type = (SELECT room_type FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                room_price = (SELECT room_price FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                days = (SELECT days FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                wifi = (SELECT wifi FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                breakfast = (SELECT breakfast FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                pool = (SELECT pool FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                reservation_date = (SELECT reservation_date FROM accommodations WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                meal_plan = (SELECT meal_plan FROM extras WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                meal_plan_price = (SELECT meal_plan_price FROM extras WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                gym_activity = (SELECT gym_activity FROM extras WHERE user_id = :user_id ORDER BY id DESC LIMIT 1),
                gym_activity_price = (SELECT gym_activity_price FROM extras WHERE user_id = :user_id ORDER BY id DESC LIMIT 1)
            WHERE user_id = :user_id
        ");
        return $stmt->execute([':user_id' => $userId]);
    }

    // ✅ Fetch all pending reservations
    public function getAllPendingReservations() {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.id, 
                r.user_id, 
                r.room_type, 
                r.days, 
                r.reservation_date, 
                u.username AS username,  -- ✅ Fixed: Using correct column name
                u.email 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    
    public function markAsBooked($reservationId) {
        $sql = "UPDATE reservations SET status = :status WHERE id = :reservation_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => 'confirmed', // ✅ Use 'confirmed' if ENUM does not support 'booked'
            ':reservation_id' => $reservationId
        ]);
    }
    
    
    // ✅ Fetch reservations by status (pending, accepted, rejected)
    public function getReservationsByStatus($status) {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.user_id, r.room_type, r.days, r.reservation_date, u.username, u.email, r.status
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = :status
        ");
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Approve a reservation
    public function approveReservation($reservationId) {
        $stmt = $this->pdo->prepare("
            UPDATE reservations 
            SET status = 'accepted' 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $reservationId]);
    }

    // ✅ Reject a reservation
    public function rejectReservation($reservationId) {
        $stmt = $this->pdo->prepare("
            UPDATE reservations 
            SET status = 'rejected' 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $reservationId]);
    }
}
