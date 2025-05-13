<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
$host    = 'localhost';
$db      = 'db_usg_main';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Handle different actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create_announcement':
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Title and content are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO announcements (title, content, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$title, $content]);
            echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating announcement']);
        }
        break;

    case 'edit_announcement':
        $id = (int)($_POST['announcement_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($id <= 0 || empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit();
        }

        try {
            $stmt = $pdo->prepare('UPDATE announcements SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$title, $content, $id]);
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating announcement']);
        }
        break;

    case 'delete_announcement':
        $id = (int)($_POST['announcement_id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
            exit();
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error deleting announcement']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
} 