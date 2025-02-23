<?php
require_once 'database.php';

class Booking {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Confirm a booking by copying details from reservations to bookings
    public function confirmBooking($userId) {
        // Ensure the user has only one active booking (delete old ones)
        $this->pdo->prepare("DELETE FROM bookings WHERE user_id = :user_id")->execute([':user_id' => $userId]);
    
        // Insert a new booking based on the latest reservation
        $stmt = $this->pdo->prepare("
            INSERT INTO bookings (user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                                  meal_plan, meal_plan_price, gym_activity, gym_activity_price)
            SELECT user_id, room_type, room_price, days, wifi, breakfast, pool, reservation_date, 
                   meal_plan, meal_plan_price, gym_activity, gym_activity_price
            FROM reservations
            WHERE user_id = :user_id
            ORDER BY id DESC LIMIT 1
        ");
        return $stmt->execute([':user_id' => $userId]);
    }
    

    // ✅ Cancel a booking by deleting from the bookings table
    public function cancelBooking($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }

    // ✅ Retrieve the most recent booking for a specific user
    public function getBooking($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Retrieve a booking by user ID (alternative method)
    public function getBookingByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Fetch all bookings with user details for the admin panel (Pagination Supported)
    public function getAllBookings($limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.room_type, b.reservation_date, b.days, 
                   u.username, u.email
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            ORDER BY b.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Get total number of bookings (for pagination)
    public function getTotalBookings() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM bookings");
        return $stmt->fetchColumn();
    }

    // ✅ Fetch a specific booking by its ID (for editing)
    public function getBookingById($bookingId) {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->execute([':id' => $bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Delete a specific booking by ID
    public function deleteBooking($bookingId) {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE id = :id");
        return $stmt->execute([':id' => $bookingId]);
    }

    // ✅ Update a booking (Admin can modify)
    public function updateBooking($bookingId, $roomType, $reservationDate, $days) {
        $stmt = $this->pdo->prepare("
            UPDATE bookings 
            SET room_type = :room_type, reservation_date = :reservation_date, days = :days 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':room_type' => $roomType,
            ':reservation_date' => $reservationDate,
            ':days' => $days,
            ':id' => $bookingId
        ]);
    }
}
?>
