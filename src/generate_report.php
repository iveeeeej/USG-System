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

// Get report type and date range
$reportType = $_POST['report_type'] ?? '';
$dateRange = $_POST['date_range'] ?? '';

// Set date range conditions based on report type
$dateCondition = '';
$params = [];

switch ($reportType) {
    case 'events':
        $dateColumn = 'startdate';
        break;
    case 'attendance':
        $dateColumn = 'a.date';
        break;
    case 'payments':
        $dateColumn = 'pay_startdate';
        break;
    case 'lost_and_found':
        $dateColumn = 'date_found';
        break;
    case 'feedback':
        $dateColumn = 'feed_id';
        break;
    default:
        die('Invalid report type');
}

switch ($dateRange) {
    case 'week':
        $dateCondition = "AND $dateColumn >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
        break;
    case 'month':
        $dateCondition = "AND $dateColumn >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case 'year':
        $dateCondition = "AND $dateColumn >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        break;
    case 'custom':
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            $dateCondition = "AND $dateColumn BETWEEN ? AND ?";
            $params = [$_POST['start_date'], $_POST['end_date']];
        }
        break;
}

// Generate report based on type
switch ($reportType) {
    case 'events':
        $query = "SELECT * FROM events WHERE 1=1 $dateCondition ORDER BY startdate DESC";
        $filename = 'events_report.csv';
        $headers = ['ID', 'Event Name', 'Start Date', 'End Date', 'Description'];
        break;

    case 'attendance':
        $query = "SELECT a.*, e.eventname FROM attendance a 
                 JOIN events e ON a.event_id = e.id 
                 WHERE 1=1 $dateCondition 
                 ORDER BY a.date DESC, a.time DESC";
        $filename = 'attendance_report.csv';
        $headers = ['ID', 'Attendee Name', 'Date', 'Time', 'Event Name'];
        break;

    case 'payments':
        $query = "SELECT * FROM pay WHERE 1=1 $dateCondition ORDER BY pay_startdate DESC";
        $filename = 'payments_report.csv';
        $headers = ['ID', 'Payment Name', 'Amount', 'Start Date', 'End Date', 'Description'];
        break;

    case 'lost_and_found':
        $query = "SELECT * FROM lst_fnd WHERE 1=1 $dateCondition ORDER BY date_found DESC";
        $filename = 'lost_and_found_report.csv';
        $headers = ['ID', 'Item Name', 'Category', 'Date Found', 'Location', 'Status', 'Description'];
        break;

    case 'feedback':
        $query = "SELECT * FROM feedbk ORDER BY feed_id DESC";
        $filename = 'feedback_report.csv';
        $headers = ['ID', 'Type', 'Subject', 'Comment'];
        break;

    default:
        die('Invalid report type');
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create CSV file
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write headers
    fputcsv($output, $headers);

    // Write data
    foreach ($data as $row) {
        $csvRow = [];
        switch ($reportType) {
            case 'events':
                $csvRow = [
                    $row['id'],
                    $row['eventname'],
                    $row['startdate'],
                    $row['enddate'],
                    $row['description']
                ];
                break;

            case 'attendance':
                $csvRow = [
                    $row['id'],
                    $row['name'],
                    $row['date'],
                    $row['time'],
                    $row['eventname']
                ];
                break;

            case 'payments':
                $csvRow = [
                    $row['pay_id'],
                    $row['payname'],
                    $row['amount'],
                    $row['pay_startdate'],
                    $row['pay_enddate'],
                    $row['pay_description']
                ];
                break;

            case 'lost_and_found':
                $csvRow = [
                    $row['lst_id'],
                    $row['lst_name'],
                    $row['category'],
                    $row['date_found'],
                    $row['location'],
                    $row['status'],
                    $row['description']
                ];
                break;

            case 'feedback':
                $csvRow = [
                    $row['feed_id'],
                    $row['feed_type'],
                    $row['feed_sub'],
                    $row['feed_comm']
                ];
                break;
        }
        fputcsv($output, $csvRow);
    }

    fclose($output);
    exit();

} catch (\PDOException $e) {
    die('Error generating report: ' . $e->getMessage());
} 