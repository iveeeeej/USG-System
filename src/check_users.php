<?php
require_once 'db_connection.php';

try {
    $con = getDatabaseConnection();
    
    // Check user_prof table
    echo "<h2>User Profiles:</h2>";
    $result = $con->query("SELECT * FROM user_prof");
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Check user_acc table
    echo "<h2>User Accounts:</h2>";
    $result = $con->query("SELECT * FROM user_acc");
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
    $con->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 