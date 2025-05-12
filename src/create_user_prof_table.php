<?php
// Database connection
$host = 'localhost';
$db   = 'db_usg_main';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Create user_prof table
    $sql = "CREATE TABLE IF NOT EXISTS user_prof (
        user_id VARCHAR(50) PRIMARY KEY,
        user_fullname VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(user_id)
    )";
    
    $pdo->exec($sql);
    echo "Table user_prof created successfully";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 