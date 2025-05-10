<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$userFullname   = '';

// Connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Fetch user's full name if logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT user_fullname FROM user_prof WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        if ($result) {
            $userFullname = $result['user_fullname'];
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
    if ($date <= 0) {
        $errors[] = 'Date is required.';
    }
    if (!$time) {
        $errors[] = 'Time is required.';
    }
    if ($event_id <= 0) {
        $errors[] = 'Valid Event is required.';
    }

    // Create if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO attendance (name, date, time, event_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $date, $time, $event_id]);
        $successMessage = 'Attendance recorded successfully.';
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($successMessage) . '#recordAttendanceForm');
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

$editAttendance = null;
if (isset($_GET['edit_att_id'])) {
    $editAttId = (int) $_GET['edit_att_id'];
    $stmt      = $pdo->prepare('SELECT * FROM attendance WHERE id = ?');
    $stmt->execute([$editAttId]);
    $editAttendance = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $type = $_POST['feedbackType'] ?? '';
    $subject = $_POST['feedbackTitle'] ?? '';
    $comment = $_POST['feedbackDescription'] ?? '';

    // Validate input
    if ($type && $subject && $comment) {
        $stmt = $pdo->prepare('INSERT INTO feedbk (feed_type, feed_sub, feed_comm) VALUES (?, ?, ?)');
        $stmt->execute([$type, $subject, $comment]);
        // Optionally, set a success message or redirect
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>USG-Student-Testing_Dashboard</title>
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
                    <span class="me-2 d-none d-md-inline"><?= htmlspecialchars($userFullname) ?></span>
                    <div class="admin-logo" aria-label="Admin Panel Logo">
                        <img id="adminLogoImg" src="../img/default-profile.png" alt="Profile Image" height="40" class="rounded-circle" style="object-fit: cover;">
                    </div>
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
                            <a class="nav-link" href="#" data-section="viewEventsSection">
                                <i class="bi bi-calendar-event me-2"></i>
                                Events
                            </a>
                        </li>

                        <!-- Attendance Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="recordAttendanceForm">
                                <i class="bi bi-people me-2"></i>
                                Attendance
                            </a>
                        </li>

                        <!-- Payments Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="viewPaymentsSection">
                                <i class="bi bi-cash-coin me-2"></i>
                                Payments
                            </a>
                        </li>

                        <!-- Lost and Found Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="viewItemsSection">
                                <i class="bi bi-question-diamond me-2"></i>
                                Lost and Found
                            </a>
                        </li>

                        <!-- Feedback -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="feedbackSection">
                                <i class="bi bi-chat-left-text me-2"></i>
                                Feedback
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

                <!-- Events Home CardDashboard -->
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
                                        Total Events Happening Now
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Attendace Home CardDashboard -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card" aria-label="Attendance" style="cursor: pointer;" onclick="showSection('recordAttendanceForm')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Attendance</h5>
                                            <h2 class="mb-0">
                                                &nbsp
                                            </h2>
                                        </div>
                                        <div class="card-icon" aria-hidden="true">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mt-2">Click Here!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Home CardDashboard -->
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
                                    <p class="card-text text-muted small mt-2">Total Payments Recorded</p>
                                </div>
                            </div>
                        </div>

                        <!-- Lst n Fnd Home CardDashboard -->
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
                                    <p class="card-text text-muted small mt-2">Total Lost Items Recorded</p>
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

                <!-- View Events Section -->
                <section id="viewEventsSection" class="section-container d-none" aria-label="View Events Section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Events</h5>
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
                                                                <button onclick="showSection('recordAttendanceForm')" class="btn btn-sm btn-outline-secondary me-1">
                                                                    <i class="bi bi-check-circle"></i>
                                                                </button>
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
                <section id="recordAttendanceForm" class="section-container d-none" aria-label="Record Attendance Section">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card mt-4 mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <?= $editAttendance ? 'Edit Attendance' : 'Record Attendance' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="recordAttendanceForm" method="post" novalidate>
                                        <input type="hidden" name="<?= $editAttendance ? 'update_attendance' : 'create_attendance' ?>" value="1" />
                                        <?php if ($editAttendance): ?>
                                            <input type="hidden" name="attendance_id" value="<?= $editAttendance['id'] ?>" />
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="attendeeName" class="form-label">Attendee Name</label>
                                            <input type="text" class="form-control" id="attendeeName" name="attendeeName" required value="<?= htmlspecialchars($_POST['attendeeName'] ?? $editAttendance['name'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attDate" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="attDate" name="attDate" required value="<?= htmlspecialchars($_POST['attDate'] ?? ($editAttendance ? date('Y-m-d', strtotime($editAttendance['date'])) : '')) ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attTime" class="form-label">Time</label>
                                            <input type="time" class="form-control" id="attTime" name="attTime" required value="<?= htmlspecialchars($_POST['attTime'] ?? $editAttendance['time'] ?? '') ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="attEvent" class="form-label">Event</label>
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
                                            <button type="submit" class="btn btn-success me-2">
                                                <?= $editAttendance ? 'Check In' : 'Check In' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- View Payments Section -->
                <section id="viewPaymentsSection" class="section-container d-none">
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
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Payment Name</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Due Date</th>
                                                    <th scope="col">Cut-Off Date</th>
                                                    <th scope="col">Description</th>
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
                <section id="viewItemsSection" class="section-container d-none">
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
                                                    <th scope="col">No.</th>
                                                    <th scope="col">Image</th>
                                                    <th scope="col">Item Name</th>
                                                    <th scope="col">Category</th>
                                                    <th scope="col">Date Found</th>
                                                    <th scope="col">Location</th>
                                                    <th scope="col">Status</th>
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
                                        <!-- Feedback Form (now full width) -->
                                        <div class="col-12">
                                            <form id="feedbackForm" method="post" action="">
                                                <input type="hidden" name="submit_feedback" value="1">
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
                                                    <label for="feedbackTitle" class="form-label">Subject</label>
                                                    <input type="text" class="form-control" id="feedbackTitle" name="feedbackTitle" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="feedbackDescription" class="form-label">Comment</label>
                                                    <textarea class="form-control" id="feedbackDescription" name="feedbackDescription" rows="4" required></textarea>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-send me-2"></i>Submit Feedback
                                                    </button>
                                                </div>
                                            </form>
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
                fetch('manage_account.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('firstName').value = data.data.firstName;
                            document.getElementById('lastName').value = data.data.lastName;
                            document.getElementById('email').value = data.data.email;
                            document.getElementById('department').value = data.data.department;
                            
                            if (data.data.profileImage) {
                                const imageData = 'data:image/jpeg;base64,' + data.data.profileImage;
                                document.getElementById('profileImage').src = imageData;
                                if (adminLogoImg) {
                                    adminLogoImg.src = imageData;
                                }
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
                        
                        // Update navbar with new user info
                        const adminPanelText = document.querySelector('.me-2.d-none.d-md-inline');
                        if (adminPanelText) {
                            adminPanelText.textContent = data.data.firstName + ' ' + data.data.lastName;
                        }
                        
                        // Update both profile image and admin logo
                        if (data.data.profileImage) {
                            const imageData = 'data:image/jpeg;base64,' + data.data.profileImage;
                            document.getElementById('profileImage').src = imageData;
                            if (adminLogoImg) {
                                adminLogoImg.src = imageData;
                            }
                        }
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

        // Function to update navbar user info
        function updateNavbarUserInfo() {
            fetch('manage_account.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update admin panel text
                        const adminPanelText = document.querySelector('.me-2.d-none.d-md-inline');
                        if (adminPanelText) {
                            adminPanelText.textContent = data.data.firstName + ' ' + data.data.lastName;
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