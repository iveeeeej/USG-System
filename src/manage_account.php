<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle GET request to fetch user data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $con = getDatabaseConnection();
        
        // Get user profile data
        $stmt = $con->prepare("SELECT user_fullname, user_mail, department, user_img FROM user_prof WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Split fullname into first and last name
            $name_parts = explode(' ', $row['user_fullname']);
            $firstName = $name_parts[0];
            $lastName = isset($name_parts[1]) ? $name_parts[1] : '';
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $row['user_mail'],
                    'department' => $row['department'],
                    'profileImage' => $row['user_img'] ? base64_encode($row['user_img']) : null
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        
        $stmt->close();
        $con->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handle POST request to update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $con = getDatabaseConnection();
        
        // Get form data
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $department = $_POST['department'] ?? '';
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        
        // Combine first and last name
        $fullname = trim($firstName . ' ' . $lastName);
        
        // Start transaction
        $con->begin_transaction();
        
        // Update user profile
        $stmt = $con->prepare("UPDATE user_prof SET user_fullname = ?, user_mail = ?, department = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $fullname, $email, $department, $user_id);
        $stmt->execute();
        
        // Handle profile image upload
        $imageData = null;
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profileImage']['type'], $allowedTypes)) {
                throw new Exception('Invalid image type. Please upload a JPEG, PNG, or GIF image.');
            }
            if ($_FILES['profileImage']['size'] > $maxSize) {
                throw new Exception('Image size too large. Maximum size is 5MB.');
            }

            $imageData = file_get_contents($_FILES['profileImage']['tmp_name']);
            
            // Debug: Check if image data was read successfully
            if ($imageData === false) {
                throw new Exception('Failed to read image file');
            }
            
            // Debug: Check image data size
            $imageSize = strlen($imageData);
            if ($imageSize === 0) {
                throw new Exception('Image data is empty');
            }
            
            $stmt = $con->prepare("UPDATE user_prof SET user_img = ? WHERE user_id = ?");
            $stmt->bind_param("bi", $imageData, $user_id);
            $result = $stmt->execute();
            
            // Debug: Check if update was successful
            if (!$result) {
                throw new Exception('Failed to update image in database: ' . $stmt->error);
            }
        }
        
        // Handle password change if provided
        if ($currentPassword && $newPassword) {
            // Verify current password
            $stmt = $con->prepare("SELECT acc_pass FROM user_acc WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if ($row['acc_pass'] === $currentPassword) {
                    // Update password
                    $stmt = $con->prepare("UPDATE user_acc SET acc_pass = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $newPassword, $user_id);
                    $stmt->execute();
                } else {
                    throw new Exception('Current password is incorrect');
                }
            }
        }
        
        // Commit transaction
        $con->commit();
        
        // Get updated user data including the new image
        $stmt = $con->prepare("SELECT user_fullname, user_mail, department, user_img FROM user_prof WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Debug: Check if we got the updated data
        if (!$row) {
            throw new Exception('Failed to retrieve updated user data');
        }
        
        // Split fullname into first and last name
        $name_parts = explode(' ', $row['user_fullname']);
        $firstName = $name_parts[0];
        $lastName = isset($name_parts[1]) ? $name_parts[1] : '';
        
        // Debug: Check if image data exists
        $hasImage = !empty($row['user_img']);
        
        $response = [
            'success' => true, 
            'message' => 'Profile updated successfully',
            'data' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $row['user_mail'],
                'department' => $row['department'],
                'profileImage' => $row['user_img'] ? base64_encode($row['user_img']) : null,
                'debug' => [
                    'hasImage' => $hasImage,
                    'imageSize' => $hasImage ? strlen($row['user_img']) : 0
                ]
            ]
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($con)) {
            $con->rollback();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($con)) {
            $con->close();
        }
    }
}
?> 