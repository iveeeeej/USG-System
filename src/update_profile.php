<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

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
    
    // Handle GET request - fetch user data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT full_name, email, profile_picture FROM user_profile WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch();
        
        if ($userData) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'firstName' => $userData['full_name'] ?? '',
                    'email' => $userData['email'] ?? '',
                    'profileImage' => $userData['profile_picture'] ?? null
                ]
            ]);
        } else {
            // If no user data found, return empty values
            echo json_encode([
                'success' => true,
                'data' => [
                    'firstName' => '',
                    'email' => '',
                    'profileImage' => null
                ]
            ]);
        }
        exit();
    }
    
    // Handle POST request - update user data
    $userId = $_SESSION['user_id'];
    $fullName = $_POST['firstName'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    
    // Start building the update query for user_profile
    $updates = [];
    $params = [];
    
    // Update full name if provided
    if (!empty($fullName)) {
        $updates[] = "full_name = ?";
        $params[] = $fullName;
    }
    
    // Update email if provided
    if (!empty($email)) {
        $updates[] = "email = ?";
        $params[] = $email;
    }
    
    // Handle profile image upload
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['profileImage']['tmp_name']);
        $updates[] = "profile_picture = ?";
        $params[] = $imageData;
    }
    
    // If there are updates to make to user_profile
    if (!empty($updates)) {
        $params[] = $userId; // Add user_id for WHERE clause
        
        $sql = "UPDATE user_profile SET " . implode(", ", $updates) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    // Handle password change if provided
    if (!empty($currentPassword) && !empty($newPassword)) {
        // First verify current password from user table
        $stmt = $pdo->prepare("SELECT password FROM user WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Update password in user table
            $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => [
            'fullName' => $fullName,
            'email' => $email
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 