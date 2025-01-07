public function register($username, $email, $password) {
    $errors = [];

    // Trim inputs
    $username = trim($username);
    $email = trim($email);

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Valid email is required.";
    }
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    if (!empty($errors)) {
        return $errors;
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $query = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);

        return "Registration successful.";
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry
            return ["email" => "Email is already registered."];
        }
        return ["error" => "An error occurred: " . $e->getMessage()];
    }
}
