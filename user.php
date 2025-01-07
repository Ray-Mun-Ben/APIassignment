<?php
require_once 'Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function register($username, $email, $password)
    {
        $errors = [];

        // Validate inputs
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into the database
        try {
            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();

            return "Registration successful!";
        } catch (PDOException $e) {
            return ["Registration failed: " . $e->getMessage()];
        }
    }
}
?>
