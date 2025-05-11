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
    
    // Get all users from user table
    $stmt = $pdo->query("SELECT user_id FROM user");
    $users = $stmt->fetchAll();
    
    // For each user, create a profile if it doesn't exist
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_prof (user_id, user_fullname) VALUES (?, ?)");
        $stmt->execute([$user['user_id'], 'User ' . $user['user_id']]); // Default name
    }
    
    echo "User profiles created successfully";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 