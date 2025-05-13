<?php
session_start(); // Add session start at the beginning

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: USG-Login.php");
    exit();
}

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

// Initialize variables
$successMessage = '';
$errors         = [];

// Connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Fetch user's full name and profile image if logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT full_name, profile_picture FROM user_profile WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        if ($result) {
            $userFullname = $result['full_name'];
            $userProfileImage = $result['profile_picture'];
            error_log('User fullname retrieved: ' . $userFullname); // Debug log
        } else {
            error_log('No user found with ID: ' . $_SESSION['user_id']); // Debug log
        }
    } else {
        error_log('No user_id in session'); // Debug log
    }
} catch (\PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    exit('Database connection failed: ' . $e->getMessage());
}

// Handle Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $deleteId = (int) ($_POST['event_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
        $stmt->execute([$deleteId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewEventsSection');
        exit();
    }
}

// Handle Clear All Events
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_events'])) {
    $stmt = $pdo->prepare('DELETE FROM events');
    $stmt->execute();
    $successMessage = 'All events have been cleared successfully.';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewEventsSection');
    exit();
}

// Handle Clear All Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_attendance'])) {
    $stmt = $pdo->prepare('DELETE FROM attendance');
    $stmt->execute();
    $successMessage = 'All attendance records have been cleared successfully.';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewAttendanceSection');
    exit();
}

// Handle Clear All Payments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_payments'])) {
    $stmt = $pdo->prepare('DELETE FROM pay');
    $stmt->execute();
    $successMessage = 'All payment records have been cleared successfully.';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewPaymentsSection');
    exit();
}

// Handle Clear All Lost Items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_items'])) {
    $stmt = $pdo->prepare('DELETE FROM lst_fnd');
    $stmt->execute();
    $successMessage = 'All lost and found items have been cleared successfully.';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewItemsSection');
    exit();
}

// Handle Clear All Feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_feedback'])) {
    $stmt = $pdo->prepare('DELETE FROM feedbk');
    $stmt->execute();
    $successMessage = 'All feedback records have been cleared successfully.';
    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#feedbackSection');
    exit();
}

// Handle Update Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $updateId    = (int) ($_POST['event_id'] ?? 0);
    $eventname   = trim($_POST['eventName'] ?? '');
    $startdate   = $_POST['startDate'] ?? '';
    $enddate     = $_POST['endDate'] ?? '';
    $description = trim($_POST['eventDescription'] ?? '');
    $status      = $_POST['status'] ?? '';

    // Validate input
    if ($eventname === '') {
        $errors[] = 'Event Name is required.';
    }
    if (!$startdate) {
        $errors[] = 'Start Date is required.';
    }
    if (!$enddate) {
        $errors[] = 'End Date is required.';
    }
    if ($startdate && $enddate && strtotime($enddate) < strtotime($startdate)) {
        $errors[] = 'End Date cannot be before Start Date.';
    }

    // Update if no errors
    if (empty($errors) && $updateId > 0) {
        $stmt = $pdo->prepare('UPDATE events SET eventname = ?, startdate = ?, enddate = ?, description = ? WHERE id = ?');
        $stmt->execute([$eventname, $startdate, $enddate, $description, $updateId]);
        $successMessage = 'Event updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewEventsSection');
        exit();
    }
}

// Handle Create Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $eventname   = trim($_POST['eventName'] ?? '');
    $startdate   = $_POST['startDate'] ?? '';
    $enddate     = $_POST['endDate'] ?? '';
    $description = trim($_POST['eventDescription'] ?? '');

        $stmt = $pdo->prepare('INSERT INTO events (eventname, startdate, enddate, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$eventname, $startdate, $enddate, $description]);
        $successMessage = 'Event created successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewEventsSection');
        exit();
}

// Handle Delete attendance request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_attendance'])) {
    $deleteId = (int) ($_POST['attendance_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM attendance WHERE id = ?');
        $stmt->execute([$deleteId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewAttendanceSection');
        exit();
    }
}

// Handle Update attendance request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $updateId = (int) ($_POST['attendance_id'] ?? 0);
    $name     = trim($_POST['attendeeName'] ?? '');
    $date     = $_POST['attDate'] ?? '';
    $time     = $_POST['attTime'] ?? '';
    $event_id = (int) ($_POST['attEvent'] ?? 0);

    // Validate input
    if ($name === '') {
        $errors[] = 'Attendee Name is required.';
    }
    if (!$date) {
        $errors[] = 'Attendance Date is required.';
    }
    if (!$time) {
        $errors[] = 'Attendance Time is required.';
    }
    if ($event_id <= 0) {
        $errors[] = 'Valid Event is required.';
    }

    // Update if no errors
    if (empty($errors) && $updateId > 0) {
        $stmt = $pdo->prepare('UPDATE attendance SET name = ?, date = ?, time = ?, event_id = ? WHERE id = ?');
        $stmt->execute([$name, $date, $time, $event_id, $updateId]);
        $successMessage = 'Attendance updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewAttendanceSection');
        exit();
    }
}

// Handle Create Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_attendance'])) {
    $name     = trim($_POST['attendeeName'] ?? '');
    $date     = $_POST['attDate'] ?? '';
    $time     = $_POST['attTime'] ?? '';
    $event_id = (int) ($_POST['attEvent'] ?? 0);

    // Validate input
    if ($name === '') {
        $errors[] = 'Attendee Name is required';
    }
    if (!$date) {
        $errors[] = 'Date is required';
    }
    if (!$time) {
        $errors[] = 'Time is required';
    }
    if ($event_id <= 0) {
        $errors[] = 'Valid Event is required';
    }

    // Create if no errors
    if (empty($errors)) {
        // Insert into confirm_attendance table first
        $stmt = $pdo->prepare('INSERT INTO confirm_attendance (name, date, time, event_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $date, $time, $event_id]);
        $successMessage = 'Attendance submitted for approval.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#confirmAttendanceSection');
        exit();
    }
}

// Handle Approve Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_attendance'])) {
    $attendance_id = (int) ($_POST['attendance_id'] ?? 0);
    
    if ($attendance_id > 0) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Get the attendance record from confirm_attendance
            $stmt = $pdo->prepare('SELECT * FROM confirm_attendance WHERE id = ?');
            $stmt->execute([$attendance_id]);
            $attendance = $stmt->fetch();
            
            if ($attendance) {
                // Insert into attendance table
                $stmt = $pdo->prepare('INSERT INTO attendance (name, date, time, event_id) VALUES (?, ?, ?, ?)');
                $stmt->execute([$attendance['name'], $attendance['date'], $attendance['time'], $attendance['event_id']]);
                
                // Delete from confirm_attendance
                $stmt = $pdo->prepare('DELETE FROM confirm_attendance WHERE id = ?');
                $stmt->execute([$attendance_id]);
                
                $pdo->commit();
                $successMessage = 'Attendance approved successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewAttendanceSection');
        exit();
            }
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Error approving attendance: ' . $e->getMessage();
        }
    }
}

// Handle Delete payment request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_payment'])) {
    $deleteId = (int) ($_POST['pay_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM pay WHERE pay_id = ?');
        $stmt->execute([$deleteId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewPaymentsSection');
        exit();
    }
}

// Handle Update Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $updateId      = (int) ($_POST['pay_id'] ?? 0);
    $payname       = trim($_POST['PaymentName'] ?? '');
    $amount        = trim($_POST['Amount'] ?? '');
    $pay_startdate = $_POST['startDate'] ?? '';
    $pay_enddate   = $_POST['endDate'] ?? '';
    $pay_description = trim($_POST['eventDescription'] ?? '');

    // Validate input
    if ($payname === '') {
        $errors[] = 'Payment Name is required.';
    }
    if ($amount === '') {
        $errors[] = 'Amount is required.';
    }
    if (!$pay_startdate) {
        $errors[] = 'Start Date is required.';
    }
    if (!$pay_enddate) {
        $errors[] = 'End Date is required.';
    }
    if ($pay_startdate && $pay_enddate && strtotime($pay_enddate) < strtotime($pay_startdate)) {
        $errors[] = 'End Date cannot be before Start Date.';
    }

    // Update if no errors
    if (empty($errors) && $updateId > 0) {
        $stmt = $pdo->prepare('UPDATE pay SET payname = ?, amount = ?, pay_startdate = ?, pay_enddate = ?, pay_description = ? WHERE pay_id = ?');
        $stmt->execute([$payname, $amount, $pay_startdate, $pay_enddate, $pay_description, $updateId]);
        $successMessage = 'Payment updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewPaymentsSection');
        exit();
    }
}

// Handle Create Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
    $payname       = trim($_POST['PaymentName'] ?? '');
    $amount        = trim($_POST['Amount'] ?? '');
    $pay_startdate = $_POST['startDate'] ?? '';
    $pay_enddate   = $_POST['endDate'] ?? '';
    $pay_description = trim($_POST['eventDescription'] ?? '');

    // Validate input
    if ($payname === '') {
        $errors[] = 'Payment Name is required.';
    }
    if ($amount === '') {
        $errors[] = 'Amount is required.';
    }
    if (!$pay_startdate) {
        $errors[] = 'Start Date is required.';
    }
    if (!$pay_enddate) {
        $errors[] = 'End Date is required.';
    }
    if ($pay_startdate && $pay_enddate && strtotime($pay_enddate) < strtotime($pay_startdate)) {
        $errors[] = 'End Date cannot be before Start Date.';
    }

    // Create if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO pay (payname, amount, pay_startdate, pay_enddate, pay_description) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$payname, $amount, $pay_startdate, $pay_enddate, $pay_description]);
        $successMessage = 'Payment created successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewPaymentsSection');
        exit();
    }
}

// Handle Delete Lost and Found Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $deleteId = (int) ($_POST['item_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM lst_fnd WHERE lst_id = ?');
        $stmt->execute([$deleteId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewItemsSection');
        exit();
    }
}

// Handle Delete Feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $deleteId = (int) ($_POST['feedback_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM feedbk WHERE feed_id = ?');
        $stmt->execute([$deleteId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#feedbackSection');
        exit();
    }
}

// Handle Update Lost and Found Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $updateId = (int) ($_POST['item_id'] ?? 0);
    $itemName = trim($_POST['itemName'] ?? '');
    $category = trim($_POST['itemCategory'] ?? '');
    $dateFound = $_POST['dateFound'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Unclaimed';

    // Validate input
    if ($itemName === '') {
        $errors[] = 'Item Name is required.';
    }
    if ($category === '') {
        $errors[] = 'Category is required.';
    }
    if (!$dateFound) {
        $errors[] = 'Date Found is required.';
    }
    if ($location === '') {
        $errors[] = 'Location is required.';
    }

    // Handle image upload
    $imageData = null;
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['itemImage']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image type. Please upload a JPEG, PNG, or GIF image.';
        } elseif ($_FILES['itemImage']['size'] > $maxSize) {
            $errors[] = 'Image size too large. Maximum size is 5MB.';
        } else {
            $imageData = file_get_contents($_FILES['itemImage']['tmp_name']);
        }
    }

    // Update if no errors
    if (empty($errors) && $updateId > 0) {
        if ($imageData) {
            $stmt = $pdo->prepare('UPDATE lst_fnd SET lst_name = ?, category = ?, date_found = ?, location = ?, description = ?, status = ?, lst_img = ? WHERE lst_id = ?');
            $stmt->execute([$itemName, $category, $dateFound, $location, $description, $status, $imageData, $updateId]);
        } else {
            $stmt = $pdo->prepare('UPDATE lst_fnd SET lst_name = ?, category = ?, date_found = ?, location = ?, description = ?, status = ? WHERE lst_id = ?');
            $stmt->execute([$itemName, $category, $dateFound, $location, $description, $status, $updateId]);
        }
        $successMessage = 'Item updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewItemsSection');
        exit();
    }
}

// Handle Create Lost and Found Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_item'])) {
    $itemName = trim($_POST['itemName'] ?? '');
    $category = trim($_POST['itemCategory'] ?? '');
    $dateFound = $_POST['dateFound'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = 'Unclaimed';

    // Validate input
    if ($itemName === '') {
        $errors[] = 'Item Name is required.';
    }
    if ($category === '') {
        $errors[] = 'Category is required.';
    }
    if (!$dateFound) {
        $errors[] = 'Date Found is required.';
    }
    if ($location === '') {
        $errors[] = 'Location is required.';
    }

    // Handle image upload
    $imageData = null;
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['itemImage']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image type. Please upload a JPEG, PNG, or GIF image.';
        } elseif ($_FILES['itemImage']['size'] > $maxSize) {
            $errors[] = 'Image size too large. Maximum size is 5MB.';
        } else {
            $imageData = file_get_contents($_FILES['itemImage']['tmp_name']);
        }
    }

    // Create if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO lst_fnd (lst_name, category, date_found, location, description, status, lst_img) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$itemName, $category, $dateFound, $location, $description, $status, $imageData]);
        $successMessage = 'Item added successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewItemsSection');
        exit();
    }
}

// Fetch events for display
try {
    // Fetch events
    $stmt   = $pdo->query('SELECT * FROM events ORDER BY startdate DESC');
    $events = $stmt->fetchAll();

    // Fetch attendance with event names
    $stmt = $pdo->query('SELECT a.*, e.eventname FROM attendance a JOIN events e ON a.event_id = e.id ORDER BY a.date DESC, a.time DESC');
    $attendances = $stmt->fetchAll();

    // Fetch confirmed attendance with event names
    $stmt = $pdo->query('SELECT ca.*, e.eventname FROM confirm_attendance ca JOIN events e ON ca.event_id = e.id ORDER BY ca.date DESC, ca.time DESC');
    $confirmedAttendances = $stmt->fetchAll();

    // Fetch payments
    $stmt     = $pdo->query('SELECT * FROM pay ORDER BY pay_startdate DESC');
    $payments = $stmt->fetchAll();

    // Fetch lost and found items
    // First, check if the new columns exist
    $checkColumns = $pdo->query("SHOW COLUMNS FROM lst_fnd LIKE 'date_found'");
    $columnsExist = $checkColumns->rowCount() > 0;

    if (!$columnsExist) {
        // If columns don't exist, execute the update script
        $updateSQL = file_get_contents('../database/update_lost_and_found.sql');
        $pdo->exec($updateSQL);
    }

    // Now fetch the items
    $stmt = $pdo->query('SELECT * FROM lst_fnd ORDER BY date_found DESC');
    $lostAndFoundItems = $stmt->fetchAll();
} catch (\PDOException $e) {
    // If there's still an error, try fetching without the new columns
    try {
        $stmt = $pdo->query('SELECT * FROM lst_fnd');
        $lostAndFoundItems = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $lostAndFoundItems = [];
        $errors[] = 'Error loading lost and found items: ' . $e->getMessage();
    }
}

// Calculate event statistics
$totalEvents = count($events);
$now         = new DateTime();
$weekLater   = (new DateTime())->modify('+7 days');
$upcomingCount = 0;

foreach ($events as $event) {
    $eventStart = new DateTime($event['startdate']);
    if ($eventStart >= $now && $eventStart <= $weekLater) {
        $upcomingCount++;
    }
}

// Check for success message from redirect
if (isset($_GET['msg'])) {
    $successMessage = htmlspecialchars($_GET['msg']);
}

// Check edit event or attendance or payment request
$editEvent = null;
if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    $stmt   = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$editId]);
    $editEvent = $stmt->fetch();
}

$editAttendance = null;
if (isset($_GET['edit_att_id'])) {
    $editAttId = (int) $_GET['edit_att_id'];
    $stmt      = $pdo->prepare('SELECT * FROM attendance WHERE id = ?');
    $stmt->execute([$editAttId]);
    $editAttendance = $stmt->fetch();
}

$editPayment = null;
if (isset($_GET['edit_pay_id'])) {
    $editPayId = (int) $_GET['edit_pay_id'];
    $stmt      = $pdo->prepare('SELECT * FROM pay WHERE pay_id = ?');
    $stmt->execute([$editPayId]);
    $editPayment = $stmt->fetch();
}

// Check edit lost and found item request
$editItem = null;
if (isset($_GET['edit_item_id'])) {
    $editItemId = (int) $_GET['edit_item_id'];
    $stmt = $pdo->prepare('SELECT * FROM lst_fnd WHERE lst_id = ?');
    $stmt->execute([$editItemId]);
    $editItem = $stmt->fetch();
}

// Handle Delete Confirm Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_confirm_attendance'])) {
    $deleteId = (int) ($_POST['attendance_id'] ?? 0);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM confirm_attendance WHERE id = ?');
        $stmt->execute([$deleteId]);
        $successMessage = 'Attendance request deleted successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#confirmAttendanceSection');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>USG-Officer_Dashboard</title>
        <link rel="icon" href="../img/USG-Logo.jpg" />

        <link rel="stylesheet" href="main.css" />
        <link rel="stylesheet" href="../node_modules/bootstrap-icons/font/bootstrap-icons.min.css" />
        <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <!-- FullCalendar CSS -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />

        <style>
            body {
                background-color: #f8f9fa;
                overflow-x: hidden;
            }
            .sidebar {
                min-height: calc(100vh - 56px);
                background-color: #343a40;
                color: white;
                transition: all 0.3s;
                position: relative;
            }
            .sidebar.collapsed {
                margin-left: -100%;
            }
            .sidebar-expand-btn {
                position: absolute;
                right: -40px;
                top: 10px;
                background-color: #343a40;
                color: white;
                border: none;
                border-radius: 0 4px 4px 0;
                padding: 8px 12px;
                display: none;
                z-index: 1030;
            }
            .sidebar.collapsed .sidebar-expand-btn {
                display: block;
            }
            .main-content {
                transition: all 0.3s;
                padding: 20px;
            }
            .main-content.expanded {
                margin-left: 0;
                width: 100%;
            }
            .nav-link {
                color: rgba(255, 255, 255, 0.75);
                transition: all 0.3s ease;
                border-radius: 5px;
                margin: 2px 0;
                padding: 10px 15px;
            }
            .nav-link:hover {
                color: white;
                background-color: rgba(255, 255, 255, 0.5);
                transform: translateX(5px);
            }
            .nav-link.active {
                color: white;
                background-color: rgba(255, 255, 255, 0.1);
                border-left: 4px solid #f9a602;
            }
            .nav-link i {
                transition: transform 0.3s ease;
            }
            .nav-link:hover i {
                transform: scale(1.2);
            }
            #sidebarToggle {
                cursor: pointer;
            }
            .section-container {
                padding: 20px;
            }
            .status-Scheduled {
                background-color: #17a2b8 !important;
            }
            .status-Ongoing {
                background-color: #28a745 !important;
            }
            .status-Completed {
                background-color: #6c757d !important;
            }
            .status-Cancelled {
                background-color: #dc3545 !important;
            }
            .admin-logo {
                width: 40px;
                height: 40px;
                background-color: #6c757d;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                overflow: hidden;
            }
            .admin-logo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .admin-logo-text {
                color: white;
                font-weight: bold;
                font-size: 1.2rem;
            }
            .dropdown-menu {
                right: 0;
                left: auto;
                transform: translateX(-100px);
                min-width: 200px;
          /**/  position: absolute;
                margin-top: 0.5rem;
            }
            .dropdown {
                position: relative;
            } /**/
            .card {
                margin-bottom: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
            }
            .card-icon {
                font-size: 2rem;
                color: #0d6efd;
                transition: all 0.3s ease;
            }
            .card:hover .card-icon {
                transform: scale(1.1);
                color: #0a58ca;
            }
            .card-title {
                transition: all 0.3s ease;
            }
            .card:hover .card-title {
                color: #f9a602;
            }
            /* Fix form inline buttons spacing */
            form.inline-form {
                display: inline-block;
                margin: 0;
            }
        </style>

    </head>
<body>
    
    <!-- Header -->

    <header class="navbar navbar-expand-lg navbar-dark bg-dark" style="background: linear-gradient(140deg, rgba(33, 25, 72, 1) 25%, rgba(249, 166, 2, 1) 60%, rgba(187, 201, 189, 1) 80%);">
        <div class="container-fluid">
            <button id="sidebarToggle" class="btn btn-dark me-2" aria-label="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>

            <div class="d-flex align-items-center flex-grow-1">
                <img src="../img/USG-Logo2.png" alt="Company Logo" height="40" class="me-2 d-none d-sm-block" />
                <a class="navbar-brand fw-bold text-truncate" href="#">UNIVERSITY OF STUDENT GOVERNMENT</a>
        </div>

            <div class="dropdown">
                <div class="d-flex align-items-center text-white" role="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-md-inline text-truncate" style="max-width: 150px;"><?= htmlspecialchars($userFullname) ?></span>
                    <div class="admin-logo" aria-label="Admin Panel Logo">
                        <img id="adminLogoImg" src="<?= isset($userProfileImage) ? 'data:image/jpeg;base64,' . base64_encode($userProfileImage) : '../img/Profile.png' ?>" alt="Profile Image" height="40" class="rounded-circle" style="object-fit: cover;">
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="adminDropdown">
                    <li><a class="dropdown-item" href="#" data-section="manageAccountSection"><i class="bi bi-gear me-2"></i>Manage Account</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="USG-Login.php"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Container -->

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->

            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar" aria-label="Sidebar navigation" style="background: linear-gradient(135deg, #211948 0%, #232526 100%);">
                <button id="sidebarExpandBtn" class="sidebar-expand-btn" aria-label="Expand Sidebar">
                    <i class="bi bi-chevron-right"></i>
                </button>

                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">

                        <!-- Home -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" href="#" data-section="dashboardSection" id="navDashboard">
                                <i class="bi bi-house me-2"></i>
                                Home
                            </a>
                        </li>

                        <li class="nav-item fw-bold">
                            <a class="nav-link" href="#" data-section="announcementSection">
                            <i class="bi bi-megaphone me-2"></i>
                                Announcement
                            </a>
                        </li>

                        <!-- Events Menu -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" data-bs-toggle="collapse" href="#eventsSubMenu" role="button" aria-expanded="true" aria-controls="eventsSubMenu">
                                <i class="bi bi-calendar-event me-2"></i>
                                Events
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="eventsSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="createEventSection" id="navCreateEvent">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Create Event
                                        </a>
                                    </li>
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="viewEventsSection" id="navViewEvents">
                                            <i class="bi bi-eye me-2"></i>
                                            Event Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Attendance Menu -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" data-bs-toggle="collapse" href="#attendanceSubMenu" role="button" aria-expanded="false" aria-controls="attendanceSubMenu" id="navAttendanceCollapseBtn">
                                <i class="bi bi-people me-2"></i>
                                Attendance
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="attendanceSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="recordAttendanceSection" id="navRecordAttendance">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Record Attendance
                                        </a>
                                    </li>
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="confirmAttendanceSection" id="navConfirmAttendance">
                                            <i class="bi bi-eye me-2"></i>
                                            Approval Requests
                                        </a>
                                    </li>
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="viewAttendanceSection" id="navViewAttendance">
                                            <i class="bi bi-eye me-2"></i>
                                            Attendance Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Payments Menu -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" data-bs-toggle="collapse" href="#paymentsSubMenu" role="button" aria-expanded="false" aria-controls="paymentsSubMenu" id="navPaymentsCollapseBtn">
                                <i class="bi bi-cash-coin me-2"></i>
                                Payments
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="paymentsSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="createPaymentSection" id="navCreatePayment">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Create Payment
                                        </a>
                                    </li>
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="viewPaymentsSection" id="navViewPayments">
                                            <i class="bi bi-eye me-2"></i>
                                            Payment Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Lost and Found Menu -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" data-bs-toggle="collapse" href="#lostAndFoundSubMenu" role="button" aria-expanded="false" aria-controls="lostAndFoundSubMenu">
                                <i class="bi bi-question-diamond me-2"></i>
                                Lost and Found
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="lostAndFoundSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="addItemSection" id="navAddItem">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Add Item
                                        </a>
                                    </li>
                                    <li class="nav-item fw-bold">
                                        <a class="nav-link" href="#" data-section="viewItemsSection" id="navViewItems">
                                            <i class="bi bi-eye me-2"></i>
                                            Lost Item Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Feedback -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" href="#" data-section="feedbackSection">
                                <i class="bi bi-chat-left-text me-2"></i>
                                Feedback
                            </a>
                        </li>

                        <!-- Generate Report -->
                        <li class="nav-item fw-bold">
                            <a class="nav-link" href="#" data-section="generateReportSection">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                                Generate Report
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main id="content" class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" role="main">

                <!-- Alert Messages -->
                <?php if ($successMessage && isset($_GET['action'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="successAlert">
                        <?= $successMessage ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors) && isset($_GET['action'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="errorAlert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Section -->
                <section id="dashboardSection" class="section-container d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">Dashboard</h1>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Total Events" style="cursor: pointer;" onclick="showSection('viewEventsSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title fw-bold">Events</h5>
                                            <h2 class="mb-0" id="totalEventsCount">
                                                <?= $totalEvents ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2" id="upcomingEventsCount">
                                        Total Events Happening Now
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Attendance" style="cursor: pointer;" onclick="showSection('viewAttendanceSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title fw-bold">Attendance</h5>
                                            <h2 class="mb-0">
                                                <?= count($attendances) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total Attendances Recorded</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Payments" style="cursor: pointer;" onclick="showSection('viewPaymentsSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title fw-bold">Payments</h5>
                                            <h2 class="mb-0">
                                                <?= count($payments) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total Payments Recorded</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Lost and Found" style="cursor: pointer;" onclick="showSection('viewItemsSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title fw-bold">Lost and Found</h5>
                                            <h2 class="mb-0">
                                                <?= count($lostAndFoundItems) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-question-diamond"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total Lost Items Recorded</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Report Card (full width below) -->
                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card" aria-label="General Report">
                                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title fw-bold">General Report</h5>
                                        <p class="card-text text-muted mb-2">Generate and download summary reports for all sections.</p>
                                    </div>
                                    <a href="#generateReportSection" class="btn btn-primary mt-3 mt-md-0" data-section="generateReportSection">
                                        <i class="bi bi-file-earmark-bar-graph me-2"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements Card -->
                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card" aria-label="Announcements">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Announcements</h5>
                                    <button class="btn btn-primary btn-sm" data-section="announcementSection">
                                        <i class="bi bi-plus-circle me-2"></i>New Announcement
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="announcements-list">
                                        <?php
                                        // Fetch announcements from database
                                        try {
                                            $stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5');
                                            $announcements = $stmt->fetchAll();
                                            
                                            if (!empty($announcements)): 
                                                foreach ($announcements as $announcement): ?>
                                                    <div class="announcement-item mb-3 pb-3 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($announcement['title']) ?></h6>
                                                                <p class="mb-1 text-muted small">
                                                                    Posted on <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                                                                </p>
                                                                <p class="mb-0"><?= htmlspecialchars($announcement['content']) ?></p>
                                                            </div>
                                                            <div class="dropdown">
                                                                <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" onclick="editAnnouncement(<?= $announcement['id'] ?>)">
                                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteAnnouncement(<?= $announcement['id'] ?>)">
                                                                            <i class="bi bi-trash me-2"></i>Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach;
                                            else: ?>
                                                <div class="text-center text-muted py-4">
                                                    <i class="bi bi-megaphone display-4"></i>
                                                    <p class="mt-2">No announcements yet</p>
                                                </div>
                                            <?php endif;
                                        } catch (\PDOException $e) {
                                            echo '<div class="alert alert-danger">Error loading announcements: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                        }
                                        ?>
                                    </div>
                                    <?php if (!empty($announcements)): ?>
                                        <div class="text-center mt-3">
                                            <a href="#announcementSection" class="btn btn-outline-primary" data-section="announcementSection">
                                                <i class="bi bi-eye me-2"></i>View All Announcements
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Card (full width below) -->
                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card" aria-label="Calendar">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">Calendar</h5>
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Create Event Section -->
                <section id="createEventSection" class="section-container d-none" aria-label="Create Event Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editEvent ? 'Edit Event' : 'New Event' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="createEventForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editEvent ? 'update_event' : 'create_event' ?>" value="1" />
                                        <?php if ($editEvent): ?>
                                            <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="eventName" class="form-label fw-bold">Event Name</label>
                                            <input type="text" class="form-control" id="eventName" name="eventName" required value="<?= htmlspecialchars($_POST['eventName'] ?? $editEvent['eventname'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="startDate" class="form-label fw-bold">Start Date</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate" required value="<?= htmlspecialchars($_POST['startDate'] ?? ($editEvent ? date('Y-m-d', strtotime($editEvent['startdate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="endDate" class="form-label fw-bold">End Date</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate" required value="<?= htmlspecialchars($_POST['endDate'] ?? ($editEvent ? date('Y-m-d', strtotime($editEvent['enddate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="eventDescription" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="4"><?= htmlspecialchars($_POST['eventDescription'] ?? $editEvent['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" onclick="showSection('viewEventsSection')">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary"><span><i class="bi bi-clipboard-check me-2"></i></span>
                                                <?= $editEvent ? 'Update Event' : 'Add Event' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- View Events Section -->
                <section id="viewEventsSection" class="section-container d-none" aria-label="View Events Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Event Log</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="eventsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Event Name</th>
                                                    <th scope="col">Start Date</th>
                                                    <th scope="col">End Date</th>
                                                    <th scope="col">Description</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($events)): ?>
                                                    <?php foreach ($events as $event): ?>
                                                        <tr>
                                                            <td><?= $event['id'] ?></td>
                                                            <td><?= htmlspecialchars($event['eventname']) ?></td>
                                                            <td><?= date('M d, Y', strtotime($event['startdate'])) ?></td>
                                                            <td><?= date('M d, Y', strtotime($event['enddate'])) ?></td>
                                                            <td><?= htmlspecialchars($event['description']) ?></td>
                                                            <td>
                                                                <a href="?edit_id=<?= $event['id'] ?>#createEventSection" class="btn btn-sm btn-primary me-1" aria-label="Edit Event <?= htmlspecialchars($event['eventname']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this event?');" aria-label="Delete Event <?= htmlspecialchars($event['eventname']) ?>">
                                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
                                                                    <button type="submit" name="delete_event" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No events found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all events? This action cannot be undone.');">
                                            <button type="submit" name="clear_all_events" class="btn btn-danger">
                                            <i class="bi bi-trash me-2"></i>Clear Records
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Record Attendance Section -->
                <section id="recordAttendanceSection" class="section-container d-none" aria-label="Record Attendance Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editAttendance ? 'Edit Attendance' : 'New Attendance' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="recordAttendanceForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editAttendance ? 'update_attendance' : 'create_attendance' ?>" value="1" />
                                        <?php if ($editAttendance): ?>
                                            <input type="hidden" name="attendance_id" value="<?= $editAttendance['id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="attendeeName" class="form-label fw-bold">Attendee Name</label>
                                            <input type="text" class="form-control" id="attendeeName" name="attendeeName" required value="<?= htmlspecialchars($_POST['attendeeName'] ?? $editAttendance['name'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attDate" class="form-label fw-bold">Date</label>
                                            <input type="date" class="form-control" id="attDate" name="attDate" required value="<?= htmlspecialchars($_POST['attDate'] ?? ($editAttendance ? date('Y-m-d', strtotime($editAttendance['date'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attTime" class="form-label fw-bold">Time</label>
                                            <input type="time" class="form-control" id="attTime" name="attTime" required value="<?= htmlspecialchars($_POST['attTime'] ?? $editAttendance['time'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attEvent" class="form-label fw-bold">Event</label>
                                            <select class="form-select" id="attEvent" name="attEvent" required>
                                                <option value="">Select Event</option>
                                                <?php foreach ($events as $event): ?>
                                                    <option value="<?= $event['id'] ?>" <?= (($_POST['attEvent'] ?? $editAttendance['event_id'] ?? '') == $event['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($event['eventname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" onclick="showSection('viewAttendanceSection')">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary"><span><i class="bi bi-clipboard-check me-2"></i></span>
                                                <?= $editAttendance ? 'Update Attendance' : 'Add Attendance' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Confirm Attendance Section -->
                <section id="confirmAttendanceSection" class="section-container d-none" aria-label="Confirm Attendance Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Approval Requests</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="attendanceTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Attendee Name</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Time</th>
                                                    <th scope="col">Event</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($confirmedAttendances)): ?>
                                                    <?php foreach ($confirmedAttendances as $index => $attendance): ?>
                                                        <tr>
                                                            <th scope="row"><?= $index + 1 ?></th>
                                                            <td><?= htmlspecialchars($attendance['name']) ?></td>
                                                            <td><?= (new DateTime($attendance['date']))->format('M d, Y') ?></td>
                                                            <td><?= (new DateTime($attendance['time']))->format('h:i A') ?></td>
                                                            <td><?= htmlspecialchars($attendance['eventname']) ?></td>
                                                            <td>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="attendance_id" value="<?= $attendance['id'] ?>" />
                                                                    <button type="submit" name="approve_attendance" class="btn btn-sm btn-success me-1">
                                                                        <i class="bi bi-check-circle"></i>
                                                                    </button>
                                                                    <button type="submit" name="delete_confirm_attendance" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance request?');">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No pending requests found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- View Attendance Section -->
                <section id="viewAttendanceSection" class="section-container d-none" aria-label="View Attendance Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Attendance Log</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="attendanceTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Attendee Name</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Time</th>
                                                    <th scope="col">Event</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($attendances)): ?>
                                                    <?php foreach ($attendances as $index => $attendance): ?>
                                                        <tr>
                                                            <th scope="row"><?= $index + 1 ?></th>
                                                            <td><?= htmlspecialchars($attendance['name']) ?></td>
                                                            <td><?= (new DateTime($attendance['date']))->format('M d, Y') ?></td>
                                                            <td><?= (new DateTime($attendance['time']))->format('h:i A') ?></td>
                                                            <td><?= htmlspecialchars($attendance['eventname']) ?></td>
                                                            <td>
                                                                <a href="?edit_att_id=<?= $attendance['id'] ?>#recordAttendanceSection" class="btn btn-sm btn-primary me-1" aria-label="Edit Attendance for <?= htmlspecialchars($attendance['name']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this attendance record?');" aria-label="Delete Attendance for <?= htmlspecialchars($attendance['name']) ?>">
                                                                    <input type="hidden" name="attendance_id" value="<?= $attendance['id'] ?>" />
                                                                    <button type="submit" name="delete_attendance" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No attendance records found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all attendance records? This action cannot be undone.');">
                                            <button type="submit" name="clear_all_attendance" class="btn btn-danger">
                                            <i class="bi bi-trash me-2"></i>Clear Records
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Create Payment Section -->
                <section id="createPaymentSection" class="section-container d-none" aria-label="Create Payment Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editPayment ? 'Edit Payment' : 'New Payment' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="createPaymentForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editPayment ? 'update_payment' : 'create_payment' ?>" value="1" />
                                        <?php if ($editPayment): ?>
                                            <input type="hidden" name="pay_id" value="<?= $editPayment['pay_id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="PaymentName" class="form-label fw-bold">Payment Name</label>
                                            <input type="text" class="form-control" id="PaymentName" name="PaymentName" required value="<?= htmlspecialchars($_POST['PaymentName'] ?? $editPayment['payname'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="Amount" class="form-label fw-bold">Amount</label>
                                            <input type="text" class="form-control" id="Amount" name="Amount" required value="<?= htmlspecialchars($_POST['Amount'] ?? $editPayment['amount'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="paymentDueDate" class="form-label fw-bold">Due Date</label>
                                            <input type="date" class="form-control" id="paymentDueDate" name="startDate" required value="<?= htmlspecialchars($_POST['startDate'] ?? ($editPayment ? date('Y-m-d', strtotime($editPayment['pay_startdate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="paymentCutoffDate" class="form-label fw-bold">Cut-Off Date</label>
                                            <input type="date" class="form-control" id="paymentCutoffDate" name="endDate" required value="<?= htmlspecialchars($_POST['endDate'] ?? ($editPayment ? date('Y-m-d', strtotime($editPayment['pay_enddate'])) : '')) ?>" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="eventDescription" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="4"><?= htmlspecialchars($_POST['eventDescription'] ?? $editPayment['pay_description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" onclick="showSection('viewPaymentsSection')">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary"><span><i class="bi bi-clipboard-check me-2"></i></span>
                                                <?= $editPayment ? 'Update Payment' : 'Add Payment' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- View Payments Section -->
                <section id="viewPaymentsSection" class="section-container d-none" aria-label="View Payments Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Payment Log</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="paymentsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Payment Name</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Due Date</th>
                                                    <th scope="col">Cut-Off Date</th>
                                                    <th scope="col">Description</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($payments)): ?>
                                                    <?php foreach ($payments as $index => $payment): ?>
                                                        <tr>
                                                            <th scope="row"><?= $index + 1 ?></th>
                                                            <td><?= htmlspecialchars($payment['payname']) ?></td>
                                                            <td>₱<?= number_format($payment['amount'], 2) ?></td>
                                                            <td><?= (new DateTime($payment['pay_startdate']))->format('M d, Y') ?></td>
                                                            <td><?= (new DateTime($payment['pay_enddate']))->format('M d, Y') ?></td>
                                                            <td><?= htmlspecialchars($payment['pay_description']) ?></td>
                                                            <td>
                                                                <a href="?edit_pay_id=<?= $payment['pay_id'] ?>#createPaymentSection" class="btn btn-sm btn-primary me-1" aria-label="Edit Payment for <?= htmlspecialchars($payment['payname']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this payment record?');" aria-label="Delete Payment for <?= htmlspecialchars($payment['payname']) ?>">
                                                                    <input type="hidden" name="pay_id" value="<?= $payment['pay_id'] ?>" />
                                                                    <button type="submit" name="delete_payment" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No payment records found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all payment records? This action cannot be undone.');">
                                            <button type="submit" name="clear_all_payments" class="btn btn-danger">
                                            <i class="bi bi-trash me-2"></i>Clear Records
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Lost and Found Sections -->
                <section id="addItemSection" class="section-container d-none" aria-label="Add New Item Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editItem ? 'Edit Item' : 'New Item' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="addItemForm" method="post" enctype="multipart/form-data" novalidate>
                                        <input type="hidden" name="<?= $editItem ? 'update_item' : 'create_item' ?>" value="1" />
                                        <?php if ($editItem): ?>
                                            <input type="hidden" name="item_id" value="<?= $editItem['lst_id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="itemName" class="form-label fw-bold">Item Name</label>
                                            <input type="text" class="form-control" id="itemName" name="itemName" required value="<?= htmlspecialchars($_POST['itemName'] ?? $editItem['lst_name'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemCategory" class="form-label fw-bold">Category</label>
                                            <select class="form-select" id="itemCategory" name="itemCategory" required>
                                                <option value="">Select Category</option>
                                                <option value="Electronics" <?= (($_POST['itemCategory'] ?? $editItem['category'] ?? '') === 'Electronics') ? 'selected' : '' ?>>Electronics</option>
                                                <option value="Clothing" <?= (($_POST['itemCategory'] ?? $editItem['category'] ?? '') === 'Clothing') ? 'selected' : '' ?>>Clothing</option>
                                                <option value="Books" <?= (($_POST['itemCategory'] ?? $editItem['category'] ?? '') === 'Books') ? 'selected' : '' ?>>Books</option>
                                                <option value="Accessories" <?= (($_POST['itemCategory'] ?? $editItem['category'] ?? '') === 'Accessories') ? 'selected' : '' ?>>Accessories</option>
                                                <option value="Others" <?= (($_POST['itemCategory'] ?? $editItem['category'] ?? '') === 'Others') ? 'selected' : '' ?>>Others</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="dateFound" class="form-label fw-bold">Date Found</label>
                                            <input type="date" class="form-control" id="dateFound" name="dateFound" required value="<?= htmlspecialchars($_POST['dateFound'] ?? ($editItem ? date('Y-m-d', strtotime($editItem['date_found'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="location" class="form-label fw-bold">Location Found</label>
                                            <input type="text" class="form-control" id="location" name="location" required value="<?= htmlspecialchars($_POST['location'] ?? $editItem['location'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? $editItem['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemImage" class="form-label fw-bold">Item Image (Optional)</label>
                                            <input type="file" class="form-control" id="itemImage" name="itemImage" accept="image/*" />
                                            <?php if ($editItem && $editItem['lst_img']): ?>
                                                <div class="mt-2">
                                                    <img src="data:image/jpeg;base64,<?= base64_encode($editItem['lst_img']) ?>" alt="Current item image" class="img-thumbnail" style="max-width: 200px;" />
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" onclick="showSection('viewItemsSection')">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary"><span><i class="bi bi-clipboard-check me-2"></i></span>
                                                <?= $editItem ? 'Update Item' : 'Add Item' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- View Items Section -->
                <section id="viewItemsSection" class="section-container d-none" aria-label="View Items Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Lost Item Log</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="lostAndFoundTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Image</th>
                                                    <th scope="col">Item Name</th>
                                                    <th scope="col">Category</th>
                                                    <th scope="col">Date Found</th>
                                                    <th scope="col">Location</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($lostAndFoundItems)): ?>
                                                    <?php foreach ($lostAndFoundItems as $index => $item): ?>
                                                        <tr>
                                                            <th scope="row"><?= $index + 1 ?></th>
                                                            <td>
                                                                <?php if ($item['lst_img']): ?>
                                                                    <img src="data:image/jpeg;base64,<?= base64_encode($item['lst_img']) ?>" alt="Item image" class="img-thumbnail" style="max-width: 50px;" />
                                                                <?php else: ?>
                                                                    <span class="text-muted">No image</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($item['lst_name']) ?></td>
                                                            <td><?= htmlspecialchars($item['category']) ?></td>
                                                            <td><?= (new DateTime($item['date_found']))->format('M d, Y') ?></td>
                                                            <td><?= htmlspecialchars($item['location']) ?></td>
                                                            <td>
                                                                <span class="badge bg-<?= htmlspecialchars($item['status']) === 'Claimed' ? 'success' : 'warning' ?>">
                                                                    <?= htmlspecialchars($item['status']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="?edit_item_id=<?= $item['lst_id'] ?>#addItemSection" class="btn btn-sm btn-primary me-1" aria-label="Edit Item <?= htmlspecialchars($item['lst_name']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this item?');" aria-label="Delete Item <?= htmlspecialchars($item['lst_name']) ?>">
                                                                    <input type="hidden" name="item_id" value="<?= $item['lst_id'] ?>" />
                                                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No items found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all lost and found items? This action cannot be undone.');">
                                            <button type="submit" name="clear_all_items" class="btn btn-danger">
                                            <i class="bi bi-trash me-2"></i>Clear Records
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Feedback Section -->
                <section id="feedbackSection" class="section-container d-none">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">Feedback Log</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="feedbackTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Feedback Type</th>
                                                    <th scope="col">Subject</th>
                                                    <th scope="col">Comment</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch feedback entries
                                                try {
                                                    $stmt = $pdo->query('SELECT * FROM feedbk ORDER BY feed_id DESC');
                                                    $feedbacks = $stmt->fetchAll();
                                                    
                                                    if (!empty($feedbacks)): 
                                                        foreach ($feedbacks as $index => $feedback): ?>
                                                            <tr>
                                                                <th scope="row"><?= $index + 1 ?></th>
                                                                <td>
                                                                    <span class="badge bg-<?= $feedback['feed_type'] === 'suggestion' ? 'info' : 
                                                                        ($feedback['feed_type'] === 'complaint' ? 'danger' : 
                                                                        ($feedback['feed_type'] === 'praise' ? 'success' : 'secondary')) ?>">
                                                                        <?= ucfirst(htmlspecialchars($feedback['feed_type'])) ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= htmlspecialchars($feedback['feed_sub']) ?></td>
                                                                <td><?= htmlspecialchars($feedback['feed_comm']) ?></td>
                                                                <td>
                                                                    <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                                                        <input type="hidden" name="feedback_id" value="<?= $feedback['feed_id'] ?>" />
                                                                        <button type="submit" name="delete_feedback" class="btn btn-sm btn-danger" title="Delete">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach;
                                                    else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">No feedback entries found.</td>
                                                        </tr>
                                                    <?php endif;
                                                } catch (\PDOException $e) {
                                                    echo '<tr><td colspan="5" class="text-center text-danger">Error loading feedback: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all feedback records? This action cannot be undone.');">
                                            <button type="submit" name="clear_all_feedback" class="btn btn-danger">
                                            <i class="bi bi-trash me-2"></i>Clear Records
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Generate Report Section -->
                <section id="generateReportSection" class="section-container d-none">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">Generate Report</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Events Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold">Event Report</h5>
                                                    <p class="card-text">Generate a report of all events and their attendance.</p>
                                                    <form method="post" action="generate_report.php">
                                                        <input type="hidden" name="report_type" value="events">
                                                        <div class="mb-3">
                                                            <label for="eventDateRange" class="form-label">Date Range</label>
                                                            <select class="form-select" id="eventDateRange" name="date_range" onchange="toggleCustomDates(this, 'eventCustomDates')">
                                                                <option value="week">Last Week</option>
                                                                <option value="month">Last Month</option>
                                                                <option value="year">Last Year</option>
                                                                <option value="custom">Custom Range</option>
                                                            </select>
                                                        </div>
                                                        <div id="eventCustomDates" class="mb-3 d-none">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label for="eventStartDate" class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" id="eventStartDate" name="start_date">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="eventEndDate" class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" id="eventEndDate" name="end_date">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Download Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attendance Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold">Attendance Report</h5>
                                                    <p class="card-text">Generate a report of attendance records for all events.</p>
                                                    <form method="post" action="generate_report.php">
                                                        <input type="hidden" name="report_type" value="attendance">
                                                        <div class="mb-3">
                                                            <label for="attendanceDateRange" class="form-label">Date Range</label>
                                                            <select class="form-select" id="attendanceDateRange" name="date_range" onchange="toggleCustomDates(this, 'attendanceCustomDates')">
                                                                <option value="week">Last Week</option>
                                                                <option value="month">Last Month</option>
                                                                <option value="year">Last Year</option>
                                                                <option value="custom">Custom Range</option>
                                                            </select>
                                                        </div>
                                                        <div id="attendanceCustomDates" class="mb-3 d-none">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label for="attendanceStartDate" class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" id="attendanceStartDate" name="start_date">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="attendanceEndDate" class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" id="attendanceEndDate" name="end_date">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Download Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold">Payment Report</h5>
                                                    <p class="card-text">Generate a report of all payment records.</p>
                                                    <form method="post" action="generate_report.php">
                                                        <input type="hidden" name="report_type" value="payments">
                                                        <div class="mb-3">
                                                            <label for="paymentDateRange" class="form-label">Date Range</label>
                                                            <select class="form-select" id="paymentDateRange" name="date_range" onchange="toggleCustomDates(this, 'paymentCustomDates')">
                                                                <option value="week">Last Week</option>
                                                                <option value="month">Last Month</option>
                                                                <option value="year">Last Year</option>
                                                                <option value="custom">Custom Range</option>
                                                            </select>
                                                        </div>
                                                        <div id="paymentCustomDates" class="mb-3 d-none">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label for="paymentStartDate" class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" id="paymentStartDate" name="start_date">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="paymentEndDate" class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" id="paymentEndDate" name="end_date">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Download Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lost and Found Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold">Lost and Found Report</h5>
                                                    <p class="card-text">Generate a report of all lost and found items.</p>
                                                    <form method="post" action="generate_report.php">
                                                        <input type="hidden" name="report_type" value="lost_and_found">
                                                        <div class="mb-3">
                                                            <label for="lostFoundDateRange" class="form-label">Date Range</label>
                                                            <select class="form-select" id="lostFoundDateRange" name="date_range" onchange="toggleCustomDates(this, 'lostFoundCustomDates')">
                                                                <option value="week">Last Week</option>
                                                                <option value="month">Last Month</option>
                                                                <option value="year">Last Year</option>
                                                                <option value="custom">Custom Range</option>
                                                            </select>
                                                        </div>
                                                        <div id="lostFoundCustomDates" class="mb-3 d-none">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label for="lostFoundStartDate" class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" id="lostFoundStartDate" name="start_date">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="lostFoundEndDate" class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" id="lostFoundEndDate" name="end_date">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Download Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Feedback Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold">Feedback Report</h5>
                                                    <p class="card-text">Generate a report of all feedback records.</p>
                                                    <form method="post" action="generate_report.php">
                                                        <input type="hidden" name="report_type" value="feedback">
                                                        <div class="mb-3">
                                                            <label for="feedbackDateRange" class="form-label">Date Range</label>
                                                            <select class="form-select" id="feedbackDateRange" name="date_range" onchange="toggleCustomDates(this, 'feedbackCustomDates')">
                                                                <option value="week">Last Week</option>
                                                                <option value="month">Last Month</option>
                                                                <option value="year">Last Year</option>
                                                                <option value="custom">Custom Range</option>
                                                            </select>
                                                        </div>
                                                        <div id="feedbackCustomDates" class="mb-3 d-none">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label for="feedbackStartDate" class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" id="feedbackStartDate" name="start_date">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="feedbackEndDate" class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" id="feedbackEndDate" name="end_date">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Download Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Manage Account Section -->
                <section id="manageAccountSection" class="section-container d-none">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">Manage Account</h5>
                                </div>
                                <div class="card-body">
                                    <form id="manageAccountForm" method="post" enctype="multipart/form-data">
                                        <div class="text-center mb-4">
                                            <div class="position-relative d-inline-block">
                                                <img id="profileImage" src="<?= isset($userProfileImage) ? 'data:image/jpeg;base64,' . base64_encode($userProfileImage) : '../img/Profile.png' ?>" alt="Profile Image" 
                                                     class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                                <label for="profileImageInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" 
                                                       style="cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-camera"></i>
                                                </label>
                                                <input type="file" id="profileImageInput" name="profileImage" class="d-none" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3">
                                                <label for="firstName" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars($userFullname) ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>

                                        <h6 class="mb-3">Change Password</h6>
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" name="currentPassword">
                                        </div>

                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" name="newPassword">
                                        </div>

                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                                        </div>

                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" onclick="showSection('dashboardSection')">
                                                <i class="bi bi-x-circle me-2"></i>Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Announcement Section -->
                <section id="announcementSection" class="section-container d-none">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Announcements</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                                        <i class="bi bi-plus-circle me-2"></i>New Announcement
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="announcementsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Title</th>
                                                    <th scope="col">Content</th>
                                                    <th scope="col">Date Posted</th>
                                                    <th scope="col" style="min-width: 110px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    $stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC');
                                                    $announcements = $stmt->fetchAll();
                                                    
                                                    if (!empty($announcements)): 
                                                        foreach ($announcements as $index => $announcement): ?>
                                                            <tr>
                                                                <th scope="row"><?= $index + 1 ?></th>
                                                                <td><?= htmlspecialchars($announcement['title']) ?></td>
                                                                <td><?= htmlspecialchars($announcement['content']) ?></td>
                                                                <td><?= date('M d, Y', strtotime($announcement['created_at'])) ?></td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-primary me-1" onclick="editAnnouncement(<?= $announcement['id'] ?>, '<?= htmlspecialchars(addslashes($announcement['title'])) ?>', '<?= htmlspecialchars(addslashes($announcement['content'])) ?>')">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-danger" onclick="deleteAnnouncement(<?= $announcement['id'] ?>)">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach;
                                                    else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">No announcements found.</td>
                                                        </tr>
                                                    <?php endif;
                                                } catch (\PDOException $e) {
                                                    echo '<tr><td colspan="5" class="text-center text-danger">Error loading announcements: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Create Announcement Modal -->
                <div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title" id="createAnnouncementModalLabel">New Announcement</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="createAnnouncementForm" method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create_announcement">
                                    <div class="mb-3">
                                        <label for="announcementTitle" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="announcementTitle" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="announcementContent" class="form-label">Content</label>
                                        <textarea class="form-control" id="announcementContent" name="content" rows="4" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Create Announcement</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Announcement Modal -->
                <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="editAnnouncementForm" method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="edit_announcement">
                                    <input type="hidden" name="announcement_id" id="editAnnouncementId">
                                    <div class="mb-3">
                                        <label for="editAnnouncementTitle" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="editAnnouncementTitle" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editAnnouncementContent" class="form-label">Content</label>
                                        <textarea class="form-control" id="editAnnouncementContent" name="content" rows="4" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Announcement</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                alert.close();
            }, 5000);
        }

        if (errorAlert) {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(errorAlert);
                alert.close();
            }, 5000);
        }

        // Handle URL hash changes and parameters
        function handleNavigation() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                showSection(hash);
                // Scroll to top of the section
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Only show dashboard if no hash is present
                showSection('dashboardSection');
            }
        }

        // Initial navigation check
        handleNavigation();

        // Listen for hash changes
        window.addEventListener('hashchange', handleNavigation);

        // Handle section navigation
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSection = this.getAttribute('data-section');
                showSection(targetSection);
                // Update URL hash without triggering page reload
                history.pushState(null, null, '#' + targetSection);
            });
        });

        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize all popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Initialize all collapse elements
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        var collapseList = collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });

        // Set initial state of collapse elements based on URL hash
        const hash = window.location.hash.substring(1);
        if (hash) {
            // If we're navigating to a specific section, collapse all menus except the one containing the target
            collapseList.forEach(collapse => {
                const collapseId = collapse._element.id;
                const targetSection = document.querySelector(`[data-section="${hash}"]`);
                if (targetSection && !collapse._element.contains(targetSection)) {
                    collapse.hide();
                }
            });
        } else {
            // If no hash, collapse all menus except dashboard
            collapseList.forEach(collapse => {
                collapse.hide();
            });
        }

        // Handle Lost and Found navigation
        document.getElementById('navAddItem').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('addItemSection');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.getElementById('navViewItems').addEventListener('click', function(e) {
            e.preventDefault();
            showSection('viewItemsSection');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Handle image preview
        const imageInput = document.getElementById('itemImage');
        const imagePreview = document.getElementById('imagePreview');
        
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Initialize FullCalendar in the Home section
        var calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 500,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,dayGridDay'
                },
                selectable: false,
                editable: false,
                events: [] // Ready for dynamic events in the future
            });
            calendar.render();
        }

        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarExpandBtn = document.getElementById('sidebarExpandBtn');

        if (sidebarToggle && sidebar && mainContent) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }
        if (sidebarExpandBtn && sidebar && mainContent) {
            sidebarExpandBtn.addEventListener('click', function() {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            });
        }

        // Profile image preview
        const profileImageInput = document.getElementById('profileImageInput');
        const profileImage = document.getElementById('profileImage');
        const adminLogoImg = document.getElementById('adminLogoImg');

        if (profileImageInput && profileImage) {
            profileImageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Update both profile image and admin logo
                        profileImage.src = e.target.result;
                        if (adminLogoImg) {
                            adminLogoImg.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Form submission handling
        const manageAccountForm = document.getElementById('manageAccountForm');
        if (manageAccountForm) {
            // Load user data when the section is shown
            document.querySelector('[data-section="manageAccountSection"]').addEventListener('click', function() {
                fetch('update_profile.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('firstName').value = data.data.firstName || '';
                            document.getElementById('email').value = data.data.email || '';
                            
                            if (data.data.profileImage) {
                                const imageData = 'data:image/jpeg;base64,' + data.data.profileImage;
                                document.getElementById('profileImage').src = imageData;
                                if (adminLogoImg) {
                                    adminLogoImg.src = imageData;
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            manageAccountForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate passwords match if changing password
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (newPassword && newPassword !== confirmPassword) {
                    alert('New passwords do not match!');
                    return;
                }
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                
                // Send form data to server
                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Update navbar with new user info
                        const adminPanelText = document.querySelector('.me-2.d-none.d-md-inline');
                        if (adminPanelText) {
                            adminPanelText.textContent = data.data.fullName;
                        }
                        // Clear password fields
                        document.getElementById('currentPassword').value = '';
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                        // Redirect to Home section
                        showSection('dashboardSection');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating profile');
                })
                .finally(() => {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
            });
        }

        // Function to update navbar user info
        function updateNavbarUserInfo() {
            fetch('update_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update admin panel text
                        const adminPanelText = document.querySelector('.me-2.d-none.d-md-inline');
                        if (adminPanelText) {
                            adminPanelText.textContent = data.data.firstName; /* ---------------------------- */
                        }
                        
                        // Update admin logo
                        if (data.data.profileImage) {
                            const imageData = 'data:image/jpeg;base64,' + data.data.profileImage;
                            if (adminLogoImg) {
                                adminLogoImg.src = imageData;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating navbar:', error);
                });
        }

        // Call updateNavbarUserInfo when page loads
        updateNavbarUserInfo();

        // Form validation functions
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
            field.classList.add('is-invalid');
            
            // Scroll to the first error
            if (!document.querySelector('.error-message')) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function clearErrors(field) {
            field.classList.remove('is-invalid');
            const errorMessage = field.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }

        // Create Event Form Validation
        const createEventForm = document.getElementById('createEventForm');
        if (createEventForm) {
            createEventForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const eventName = document.getElementById('eventName').value.trim();
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(msg => msg.remove());
                
                let hasError = false;
                
                if (!eventName) {
                    showError('eventName', 'Event Name is required');
                    hasError = true;
                }
                if (!startDate) {
                    showError('startDate', 'Start Date is required');
                    hasError = true;
                }
                if (!endDate) {
                    showError('endDate', 'End Date is required');
                    hasError = true;
                }
                if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                    showError('endDate', 'End Date cannot be before Start Date');
                    hasError = true;
                }
                
                if (!hasError) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    }
                    this.submit();
                }
            });

            // Add date validation for Create Event
            const eventStartDate = document.getElementById('startDate');
            const eventEndDate = document.getElementById('endDate');

            if (eventStartDate && eventEndDate) {
                eventStartDate.addEventListener('change', function() {
                    if (eventEndDate.value && new Date(eventEndDate.value) < new Date(this.value)) {
                        showError('endDate', 'End Date cannot be before Start Date');
                    } else {
                        clearErrors(eventEndDate);
                    }
                });

                eventEndDate.addEventListener('change', function() {
                    if (eventStartDate.value && new Date(this.value) < new Date(eventStartDate.value)) {
                        showError('endDate', 'End Date cannot be before Start Date');
                    } else {
                        clearErrors(this);
                    }
                });
            }
        }

        // Record Attendance Form Validation
        const recordAttendanceForm = document.getElementById('recordAttendanceForm');
        if (recordAttendanceForm) {
            recordAttendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const attendeeName = document.getElementById('attendeeName').value.trim();
                const attDate = document.getElementById('attDate').value;
                const attTime = document.getElementById('attTime').value;
                const attEvent = document.getElementById('attEvent').value;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(msg => msg.remove());
                
                let hasError = false;
                
                if (!attendeeName) {
                    showError('attendeeName', 'Attendee Name is required');
                    hasError = true;
                }
                if (!attDate) {
                    showError('attDate', 'Date is required');
                    hasError = true;
                }
                if (!attTime) {
                    showError('attTime', 'Time is required');
                    hasError = true;
                }
                if (!attEvent) {
                    showError('attEvent', 'Event is required');
                    hasError = true;
                }
                
                if (!hasError) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    }
                    this.submit();
                }
            });
        }

        // Create Payment Form Validation
        const createPaymentForm = document.getElementById('createPaymentForm');
        if (createPaymentForm) {
            createPaymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const paymentName = document.getElementById('PaymentName').value.trim();
                const amount = document.getElementById('Amount').value.trim();
                const dueDate = document.getElementById('paymentDueDate').value;
                const cutoffDate = document.getElementById('paymentCutoffDate').value;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(msg => msg.remove());
                
                let hasError = false;
                
                if (!paymentName) {
                    showError('PaymentName', 'Payment Name is required');
                    hasError = true;
                }
                if (!amount) {
                    showError('Amount', 'Amount is required');
                    hasError = true;
                } else if (isNaN(amount) || parseFloat(amount) <= 0) {
                    showError('Amount', 'Please enter a valid amount');
                    hasError = true;
                }

                // Date validation
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (!dueDate) {
                    showError('paymentDueDate', 'Due Date is required');
                    hasError = true;
                } else {
                    const dueDateObj = new Date(dueDate);
                    dueDateObj.setHours(0, 0, 0, 0);
                    if (dueDateObj < today) {
                        showError('paymentDueDate', 'Due Date cannot be in the past');
                        hasError = true;
                    }
                }

                if (!cutoffDate) {
                    showError('paymentCutoffDate', 'Cut-Off Date is required');
                    hasError = true;
                } else {
                    const cutoffDateObj = new Date(cutoffDate);
                    cutoffDateObj.setHours(0, 0, 0, 0);
                    if (cutoffDateObj < today) {
                        showError('paymentCutoffDate', 'Cut-Off Date cannot be in the past');
                        hasError = true;
                    }
                }

                if (dueDate && cutoffDate) {
                    const dueDateObj = new Date(dueDate);
                    const cutoffDateObj = new Date(cutoffDate);
                    dueDateObj.setHours(0, 0, 0, 0);
                    cutoffDateObj.setHours(0, 0, 0, 0);

                    if (cutoffDateObj < dueDateObj) {
                        showError('paymentCutoffDate', 'Cut-Off Date cannot be before Due Date');
                        hasError = true;
                    }
                }
                
                if (!hasError) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    }
                    this.submit();
                }
            });

            // Add real-time date validation
            const dueDateInput = document.getElementById('paymentDueDate');
            const cutoffDateInput = document.getElementById('paymentCutoffDate');

            if (dueDateInput && cutoffDateInput) {
                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                dueDateInput.min = today;
                cutoffDateInput.min = today;

                // Update cutoff date minimum when due date changes
                dueDateInput.addEventListener('change', function() {
                    cutoffDateInput.min = this.value;
                    if (cutoffDateInput.value && cutoffDateInput.value < this.value) {
                        cutoffDateInput.value = this.value;
                    }
                });

                // Validate cutoff date when it changes
                cutoffDateInput.addEventListener('change', function() {
                    if (dueDateInput.value && this.value < dueDateInput.value) {
                        showError('paymentCutoffDate', 'Cut-Off Date cannot be before Due Date');
                        this.value = dueDateInput.value;
                    } else {
                        clearErrors(this);
                    }
                });
            }
        }

        // Add Item Form Validation
        const addItemForm = document.getElementById('addItemForm');
        if (addItemForm) {
            addItemForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const itemName = document.getElementById('itemName').value.trim();
                const itemCategory = document.getElementById('itemCategory').value;
                const dateFound = document.getElementById('dateFound').value;
                const location = document.getElementById('location').value.trim();
                const itemImage = document.getElementById('itemImage');
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(msg => msg.remove());
                
                let hasError = false;
                
                if (!itemName) {
                    showError('itemName', 'Item Name is required');
                    hasError = true;
                }
                if (!itemCategory) {
                    showError('itemCategory', 'Category is required');
                    hasError = true;
                }
                if (!dateFound) {
                    showError('dateFound', 'Date Found is required');
                    hasError = true;
                }
                if (!location) {
                    showError('location', 'Location is required');
                    hasError = true;
                }
                // Remove image validation check
                
                if (!hasError) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    }
                    this.submit();
                }
            });
        }

        // Add input event listeners to all form fields
        const formFields = [
            'eventName', 'startDate', 'endDate',
            'attendeeName', 'attDate', 'attTime', 'attEvent',
            'PaymentName', 'Amount',
            'itemName', 'itemCategory', 'dateFound', 'location', 'itemImage'
        ];
        
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    clearErrors(this);
                });
            }
        });
    });

    // Function to show/hide sections
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section-container').forEach(section => {
            section.classList.add('d-none');
        });
        
        // Show the selected section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('d-none');
        }
    }

    // Function to confirm delete
    function confirmDelete(id, type) {
        if (confirm('Are you sure you want to delete this item?')) {
            window.location.href = `?delete_${type}=${id}`;
        }
    }

    // Add this JavaScript function at the end of your script section
    function toggleCustomDates(selectElement, customDatesId) {
        const customDatesDiv = document.getElementById(customDatesId);
        if (selectElement.value === 'custom') {
            customDatesDiv.classList.remove('d-none');
        } else {
            customDatesDiv.classList.add('d-none');
        }
    }

    // Add these new functions for announcement management
    function editAnnouncement(id, title, content) {
        document.getElementById('editAnnouncementId').value = id;
        document.getElementById('editAnnouncementTitle').value = title;
        document.getElementById('editAnnouncementContent').value = content;
        new bootstrap.Modal(document.getElementById('editAnnouncementModal')).show();
    }

    function deleteAnnouncement(id) {
        if (confirm('Are you sure you want to delete this announcement?')) {
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_announcement">
                <input type="hidden" name="announcement_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Handle form submissions
    document.getElementById('createAnnouncementForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('handle_announcement.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    });

    document.getElementById('editAnnouncementForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('handle_announcement.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    });
</script>

</body>
</html>