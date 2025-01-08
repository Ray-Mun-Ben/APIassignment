<?php

class Database
{
    private $host = 'localhost';
    private $dbname = 'theuserDB'; // Adjust to your database name
    private $username = 'root';
    private $password = '';
    private $pdo;

    public function connect()
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage(), 3, 'error_log.log');
                die("Database connection error. Please try again later.");
            }
        }
        return $this->pdo;
    }
}
?>
