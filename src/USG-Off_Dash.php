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

// Initialize variables
$successMessage = '';
$errors         = [];

// Connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
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

    // Create if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO events (eventname, startdate, enddate, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$eventname, $startdate, $enddate, $description]);
        $successMessage = 'Event created successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewEventsSection');
        exit();
    }
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

    // Create if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO attendance (name, date, time, event_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $date, $time, $event_id]);
        $successMessage = 'Attendance recorded successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#viewAttendanceSection');
        exit();
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
    $pay_startdate = $_POST['startDate'] ?? '';
    $pay_enddate   = $_POST['endDate'] ?? '';
    $pay_description = trim($_POST['eventDescription'] ?? '');

    // Validate input
    if ($payname === '') {
        $errors[] = 'Payment Name is required.';
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
        $stmt = $pdo->prepare('UPDATE pay SET payname = ?, pay_startdate = ?, pay_enddate = ?, pay_description = ? WHERE pay_id = ?');
        $stmt->execute([$payname, $pay_startdate, $pay_enddate, $pay_description, $updateId]);
        $successMessage = 'Payment updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '#viewPaymentsSection');
        exit();
    }
}

// Handle Create Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
    $payname       = trim($_POST['PaymentName'] ?? '');
    $pay_startdate = $_POST['startDate'] ?? '';
    $pay_enddate   = $_POST['endDate'] ?? '';
    $pay_description = trim($_POST['eventDescription'] ?? '');

    // Validate input
    if ($payname === '') {
        $errors[] = 'Payment Name is required.';
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
        $stmt = $pdo->prepare('INSERT INTO pay (payname, pay_startdate, pay_enddate, pay_description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$payname, $pay_startdate, $pay_enddate, $pay_description]);
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
        header('Location: ' . $_SERVER['PHP_SELF'] . '#lostAndFoundSection');
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
    } else {
        $errors[] = 'Item image is required.';
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
    $stmt        = $pdo->query('SELECT a.*, e.eventname FROM attendance a JOIN events e ON a.event_id = e.id ORDER BY a.date DESC, a.time DESC');
    $attendances = $stmt->fetchAll();

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
            }
            .nav-link:hover {
                color: white;
            }
            .nav-link.active {
                color: white;
                background-color: rgba(255, 255, 255, 0.1);
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
                color: white;
                font-weight: bold;
            }
            .dropdown-menu {
                right: 0;
                left: auto;
            }
            .card {
                margin-bottom: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .card-icon {
                font-size: 2rem;
                color: #0d6efd;
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

        <div class="d-flex align-items-center">
            <img src="../img/USG-Logo2.png" alt="Company Logo" height="40" class="me-2" />
            <a class="navbar-brand" href="#">UNIVERSITY OF STUDENT GOVERNMENT</a>
        </div>

            <div class="dropdown">
                <div class="d-flex align-items-center text-white" role="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-md-inline">Admin Panel</span>
                    <div class="admin-logo" aria-label="Admin Panel Logo">A</div>
                </div>
                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="adminDropdown">
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
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="dashboardSection" id="navDashboard">
                                <i class="bi bi-house me-2"></i>
                                Home
                            </a>
                        </li>

                        <!-- Events Menu -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#eventsSubMenu" role="button" aria-expanded="true" aria-controls="eventsSubMenu">
                                <i class="bi bi-calendar-event me-2"></i>
                                Events
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse show" id="eventsSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="createEventSection" id="navCreateEvent">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Create Event
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="viewEventsSection" id="navViewEvents">
                                            <i class="bi bi-eye me-2"></i>
                                            Event Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Attendance Menu -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#attendanceSubMenu" role="button" aria-expanded="false" aria-controls="attendanceSubMenu" id="navAttendanceCollapseBtn">
                                <i class="bi bi-people me-2"></i>
                                Attendance
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="attendanceSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="recordAttendanceSection" id="navRecordAttendance">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Record Attendance
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="viewAttendanceSection" id="navViewAttendance">
                                            <i class="bi bi-eye me-2"></i>
                                            Attendance Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Payments Menu -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#paymentsSubMenu" role="button" aria-expanded="false" aria-controls="paymentsSubMenu" id="navPaymentsCollapseBtn">
                                <i class="bi bi-cash-coin me-2"></i>
                                Payments
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="paymentsSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="createPaymentSection" id="navCreatePayment">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Create Payment
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="viewPaymentsSection" id="navViewPayments">
                                            <i class="bi bi-eye me-2"></i>
                                            Payment Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Lost and Found Menu -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#lostAndFoundSubMenu" role="button" aria-expanded="false" aria-controls="lostAndFoundSubMenu">
                                <i class="bi bi-question-diamond me-2"></i>
                                Lost and Found
                                <i class="bi bi-chevron-down ms-2"></i>
                            </a>
                            <div class="collapse" id="lostAndFoundSubMenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="addItemSection" id="navAddItem">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Add Item
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" data-section="viewItemsSection" id="navViewItems">
                                            <i class="bi bi-eye me-2"></i>
                                            Lost Item Log
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Feedback -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="feedbackSection">
                                <i class="bi bi-chat-left-text me-2"></i>
                                Feedback
                            </a>
                        </li>

                        <!-- Generate Report -->
                        <li class="nav-item">
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
                <?php if ($successMessage): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="successAlert">
                        <?= $successMessage ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
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
                                            <h5 class="card-title">Events</h5>
                                            <h2 class="mb-0" id="totalEventsCount">
                                                <?= $totalEvents ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2" id="upcomingEventsCount">
                                        <?= $upcomingCount ?>
                                        upcoming this week
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Attendance" style="cursor: pointer;" onclick="showSection('viewAttendanceSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Attendance</h5>
                                            <h2 class="mb-0">
                                                <?= count($attendances) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total attendance records</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Payments" style="cursor: pointer;" onclick="showSection('viewPaymentsSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Payments</h5>
                                            <h2 class="mb-0">
                                                <?= count($payments) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total payment records</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Lost and Found" style="cursor: pointer;" onclick="showSection('viewItemsSection')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Lost and Found</h5>
                                            <h2 class="mb-0">
                                                <?= count($lostAndFoundItems) ?>
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-question-diamond"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Total items recorded</p>
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
                                        <h5 class="card-title">General Report</h5>
                                        <p class="card-text text-muted mb-2">Generate and download summary reports for all sections.</p>
                                    </div>
                                    <a href="#generateReportSection" class="btn btn-primary mt-3 mt-md-0" data-section="generateReportSection">
                                        <i class="bi bi-file-earmark-bar-graph me-2"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Card (full width below) -->
                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card" aria-label="Calendar">
                                <div class="card-body">
                                    <h5 class="card-title">Calendar</h5>
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
                                        <?= $editEvent ? 'Edit Event' : 'Create New Event' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="createEventForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editEvent ? 'update_event' : 'create_event' ?>" value="1" />
                                        <?php if ($editEvent): ?>
                                            <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="eventName" class="form-label">Event Name*</label>
                                            <input type="text" class="form-control" id="eventName" name="eventName" required value="<?= htmlspecialchars($_POST['eventName'] ?? $editEvent['eventname'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="startDate" class="form-label">Start Date*</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate" required value="<?= htmlspecialchars($_POST['startDate'] ?? ($editEvent ? date('Y-m-d', strtotime($editEvent['startdate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="endDate" class="form-label">End Date*</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate" required value="<?= htmlspecialchars($_POST['endDate'] ?? ($editEvent ? date('Y-m-d', strtotime($editEvent['enddate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="eventDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="4"><?= htmlspecialchars($_POST['eventDescription'] ?? $editEvent['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" id="cancelCreateEventBtn">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <?= $editEvent ? 'Update Event' : 'Create Event' ?>
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
                                    <h5 class="card-title mb-0">All Events</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="eventsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
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
                                                                <a href="?edit_id=<?= $event['id'] ?>#createEventSection" class="btn btn-sm btn-outline-secondary me-1" aria-label="Edit Event <?= htmlspecialchars($event['eventname']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this event?');" aria-label="Delete Event <?= htmlspecialchars($event['eventname']) ?>">
                                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
                                                                    <button type="submit" name="delete_event" class="btn btn-sm btn-outline-danger" title="Delete">
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
                                        <?= $editAttendance ? 'Edit Attendance' : 'Record New Attendance' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="recordAttendanceForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editAttendance ? 'update_attendance' : 'create_attendance' ?>" value="1" />
                                        <?php if ($editAttendance): ?>
                                            <input type="hidden" name="attendance_id" value="<?= $editAttendance['id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="attendeeName" class="form-label">Attendee Name*</label>
                                            <input type="text" class="form-control" id="attendeeName" name="attendeeName" required value="<?= htmlspecialchars($_POST['attendeeName'] ?? $editAttendance['name'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attDate" class="form-label">Attendance Date*</label>
                                            <input type="date" class="form-control" id="attDate" name="attDate" required value="<?= htmlspecialchars($_POST['attDate'] ?? ($editAttendance ? date('Y-m-d', strtotime($editAttendance['date'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attTime" class="form-label">Attendance Time*</label>
                                            <input type="time" class="form-control" id="attTime" name="attTime" required value="<?= htmlspecialchars($_POST['attTime'] ?? $editAttendance['time'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attEvent" class="form-label">Event*</label>
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
                                            <button type="button" class="btn btn-danger me-2" id="cancelRecordAttendanceBtn">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <?= $editAttendance ? 'Update Attendance' : 'Record Attendance' ?>
                                            </button>
                                        </div>
                                    </form>
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
                                    <h5 class="card-title mb-0">All Attendances</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="attendanceTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Attendee Name</th>
                                                    <th scope="col">Attendance Date</th>
                                                    <th scope="col">Attendance Time</th>
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
                                                                <a href="?edit_att_id=<?= $attendance['id'] ?>#recordAttendanceSection" class="btn btn-sm btn-outline-secondary" aria-label="Edit Attendance for <?= htmlspecialchars($attendance['name']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this attendance record?');" aria-label="Delete Attendance for <?= htmlspecialchars($attendance['name']) ?>">
                                                                    <input type="hidden" name="attendance_id" value="<?= $attendance['id'] ?>" />
                                                                    <button type="submit" name="delete_attendance" class="btn btn-sm btn-outline-danger" title="Delete">
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
                                        <?= $editPayment ? 'Edit Payment' : 'Create New Payment' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="createPaymentForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editPayment ? 'update_payment' : 'create_payment' ?>" value="1" />
                                        <?php if ($editPayment): ?>
                                            <input type="hidden" name="pay_id" value="<?= $editPayment['pay_id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="PaymentName" class="form-label">Payment Name*</label>
                                            <input type="text" class="form-control" id="PaymentName" name="PaymentName" required value="<?= htmlspecialchars($_POST['PaymentName'] ?? $editPayment['payname'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="startDate" class="form-label">Start Date*</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate" required value="<?= htmlspecialchars($_POST['startDate'] ?? ($editPayment ? date('Y-m-d', strtotime($editPayment['pay_startdate'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="endDate" class="form-label">End Date*</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate" required value="<?= htmlspecialchars($_POST['endDate'] ?? ($editPayment ? date('Y-m-d', strtotime($editPayment['pay_enddate'])) : '')) ?>" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="eventDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="4"><?= htmlspecialchars($_POST['eventDescription'] ?? $editPayment['pay_description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" id="cancelCreatePaymentBtn">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <?= $editPayment ? 'Update Payment' : 'Create Payment' ?>
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
                                    <h5 class="card-title mb-0">All Payments</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="paymentsTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Payment Name</th>
                                                    <th scope="col">Start Date</th>
                                                    <th scope="col">End Date</th>
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
                                                            <td><?= (new DateTime($payment['pay_startdate']))->format('M d, Y') ?></td>
                                                            <td><?= (new DateTime($payment['pay_enddate']))->format('M d, Y') ?></td>
                                                            <td><?= htmlspecialchars($payment['pay_description']) ?></td>
                                                            <td>
                                                                <a href="?edit_pay_id=<?= $payment['pay_id'] ?>#createPaymentSection" class="btn btn-sm btn-outline-secondary" aria-label="Edit Payment for <?= htmlspecialchars($payment['payname']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this payment record?');" aria-label="Delete Payment for <?= htmlspecialchars($payment['payname']) ?>">
                                                                    <input type="hidden" name="pay_id" value="<?= $payment['pay_id'] ?>" />
                                                                    <button type="submit" name="delete_payment" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No payment records found.</td>
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

                <!-- Lost and Found Sections -->
                <!-- Add New Item Section -->
                <section id="addItemSection" class="section-container d-none" aria-label="Add New Item Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editItem ? 'Edit Item' : 'Add New Item' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="addItemForm" method="post" enctype="multipart/form-data" novalidate>
                                        <input type="hidden" name="<?= $editItem ? 'update_item' : 'create_item' ?>" value="1" />
                                        <?php if ($editItem): ?>
                                            <input type="hidden" name="item_id" value="<?= $editItem['lst_id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="itemName" class="form-label">Item Name*</label>
                                            <input type="text" class="form-control" id="itemName" name="itemName" required value="<?= htmlspecialchars($_POST['itemName'] ?? $editItem['lst_name'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemCategory" class="form-label">Category*</label>
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
                                            <label for="dateFound" class="form-label">Date Found*</label>
                                            <input type="date" class="form-control" id="dateFound" name="dateFound" required value="<?= htmlspecialchars($_POST['dateFound'] ?? ($editItem ? date('Y-m-d', strtotime($editItem['date_found'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Location Found*</label>
                                            <input type="text" class="form-control" id="location" name="location" required value="<?= htmlspecialchars($_POST['location'] ?? $editItem['location'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? $editItem['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemImage" class="form-label">Item Image</label>
                                            <input type="file" class="form-control" id="itemImage" name="itemImage" accept="image/*" <?= !$editItem ? 'required' : '' ?> />
                                            <?php if ($editItem && $editItem['lst_img']): ?>
                                                <div class="mt-2">
                                                    <img src="data:image/jpeg;base64,<?= base64_encode($editItem['lst_img']) ?>" alt="Current item image" class="img-thumbnail" style="max-width: 200px;" />
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-danger me-2" id="cancelAddItemBtn">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
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
                                    <h5 class="card-title mb-0">Lost and Found Items</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="lostAndFoundTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
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
                                                                <a href="?edit_item_id=<?= $item['lst_id'] ?>#addItemSection" class="btn btn-sm btn-outline-secondary" aria-label="Edit Item <?= htmlspecialchars($item['lst_name']) ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this item?');" aria-label="Delete Item <?= htmlspecialchars($item['lst_name']) ?>">
                                                                    <input type="hidden" name="item_id" value="<?= $item['lst_id'] ?>" />
                                                                    <button type="submit" name="delete_item" class="btn btn-sm btn-outline-danger" title="Delete">
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
                                    <h5 class="card-title mb-0">Feedback Management</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Feedback Form -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Submit Feedback</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form id="feedbackForm">
                                                        <div class="mb-3">
                                                            <label for="feedbackType" class="form-label">Feedback Type</label>
                                                            <select class="form-select" id="feedbackType" name="feedbackType" required>
                                                                <option value="">Select Type</option>
                                                                <option value="suggestion">Suggestion</option>
                                                                <option value="complaint">Complaint</option>
                                                                <option value="praise">Praise</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="feedbackTitle" class="form-label">Title</label>
                                                            <input type="text" class="form-control" id="feedbackTitle" name="feedbackTitle" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="feedbackDescription" class="form-label">Description</label>
                                                            <textarea class="form-control" id="feedbackDescription" name="feedbackDescription" rows="4" required></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="feedbackPriority" class="form-label">Priority</label>
                                                            <select class="form-select" id="feedbackPriority" name="feedbackPriority" required>
                                                                <option value="low">Low</option>
                                                                <option value="medium">Medium</option>
                                                                <option value="high">High</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-send me-2"></i>Submit Feedback
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Feedback List -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title mb-0">Recent Feedback</h5>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                            Filter
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#">All</a></li>
                                                            <li><a class="dropdown-item" href="#">Suggestions</a></li>
                                                            <li><a class="dropdown-item" href="#">Complaints</a></li>
                                                            <li><a class="dropdown-item" href="#">Praise</a></li>
                                                            <li><a class="dropdown-item" href="#">Other</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Type</th>
                                                                    <th>Title</th>
                                                                    <th>Priority</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No feedback records found.</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                    <h5 class="card-title mb-0">Generate Reports</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Events Report -->
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">Events Report</h5>
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
                                                    <h5 class="card-title">Attendance Report</h5>
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
                                                    <h5 class="card-title">Payment Report</h5>
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
                                                    <h5 class="card-title">Lost and Found Report</h5>
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
                                                    <h5 class="card-title">Feedback Report</h5>
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
                                                <img id="profileImage" src="../img/default-profile.png" alt="Profile Image" 
                                                     class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                                <label for="profileImageInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" 
                                                       style="cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-camera"></i>
                                                </label>
                                                <input type="file" id="profileImageInput" name="profileImage" class="d-none" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="department" name="department" required>
                                        </div>

                                        <hr class="my-4">

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

        // Handle section navigation
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSection = this.getAttribute('data-section');
                showSection(targetSection);
            });
        });

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

        // Show dashboard by default
        showSection('dashboardSection');

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

        if (profileImageInput && profileImage) {
            profileImageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
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
                fetch('manage_account.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('firstName').value = data.data.firstName;
                            document.getElementById('lastName').value = data.data.lastName;
                            document.getElementById('email').value = data.data.email;
                            document.getElementById('department').value = data.data.department;
                            
                            if (data.data.profileImage) {
                                document.getElementById('profileImage').src = 'data:image/jpeg;base64,' + data.data.profileImage;
                            }
                        } else {
                            alert('Error loading user data: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading user data');
                    });
            });

            manageAccountForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate password change
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (newPassword && newPassword !== confirmPassword) {
                    alert('New passwords do not match!');
                    return;
                }

                // Create FormData object
                const formData = new FormData(this);

                // Send form data to server
                fetch('manage_account.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Clear password fields
                        document.getElementById('currentPassword').value = '';
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating profile');
                });
            });
        }
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
</script>

</body>
</html>