<?php
// Database connection info
$host    = 'localhost';
$db      = 'db_usg_main';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

// Set up DSN and options
$dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Get form data
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $position = $_POST['position'] ?? '';
        $department = $_POST['department'] ?? '';
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        
        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || empty($position) || empty($department)) {
            throw new Exception('All fields are required');
        }
        
        // Handle profile image upload
        $profileImage = null;
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['profileImage']['type'], $allowedTypes)) {
                throw new Exception('Invalid image type. Please upload a JPEG, PNG, or GIF image.');
            }
            
            if ($_FILES['profileImage']['size'] > $maxSize) {
                throw new Exception('Image size too large. Maximum size is 5MB.');
            }
            
            $profileImage = file_get_contents($_FILES['profileImage']['tmp_name']);
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Update user information
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                position = ?, 
                department = ?";
        
        $params = [$firstName, $lastName, $email, $position, $department];
        
        // Add profile image if uploaded
        if ($profileImage) {
            $sql .= ", profile_image = ?";
            $params[] = $profileImage;
        }
        
        // Add password update if provided
        if (!empty($currentPassword) && !empty($newPassword)) {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            $sql .= ", password = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Commit transaction
        $pdo->commit();
        
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully';
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if (!$userData) {
        throw new Exception('User not found');
    }
    
    // Send user data as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'firstName' => $userData['first_name'],
            'lastName' => $userData['last_name'],
            'email' => $userData['email'],
            'position' => $userData['position'],
            'department' => $userData['department'],
            'profileImage' => $userData['profile_image'] ? base64_encode($userData['profile_image']) : null
        ]
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 