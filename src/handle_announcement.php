<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=db_usg_main", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_announcement':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            try {
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$title, $content]);
                echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error creating announcement: ' . $e->getMessage()]);
            }
            break;
            
        case 'update_announcement':
            $id = (int)($_POST['announcement_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
                exit();
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
                $stmt->execute([$title, $content, $id]);
                echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error updating announcement: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 