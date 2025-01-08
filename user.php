<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password)
    {
        $errors = [];

        // Input validation
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        if (empty($password) || strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!empty($errors)) {
            return $errors;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $query = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);
            return "Registration successful.";
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // Duplicate entry
                return ["Email is already registered."];
            }
            return ["An error occurred: " . $e->getMessage()];
        }
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            return true; // Login successful
        }
        return false; // Login failed
    }
}
?>
