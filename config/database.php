<?php
// config/database.php - Simple database connection
$host = 'localhost';
$dbname = 'atspro';
$username = 'root';
$password = 'harry';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Simple functions for common operations
function executeQuery($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

function getUserByEmail($pdo, $email) {
    $query = "SELECT u.*, a.first_name, a.last_name FROM users u 
              LEFT JOIN alumni a ON u.id = a.user_id 
              WHERE u.email = ?";
    $stmt = executeQuery($pdo, $query, [$email]);
    return $stmt->fetch();
}

function createUser($pdo, $email, $password, $role, $status = 'pending') {
    $query = "INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    executeQuery($pdo, $query, [$email, $hashed_password, $role, $status]);
    return $pdo->lastInsertId();
}
?>