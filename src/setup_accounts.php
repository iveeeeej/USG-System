<?php
require_once 'db_connection.php';

try {
    $con = getDatabaseConnection();
    
    // First, clear any existing accounts
    $con->query("DELETE FROM user_acc");
    
    // Insert new accounts
    $accounts = [
        ['user_id' => 2023304637, 'acc_pass' => 'redjan123'],
        ['user_id' => 2023305026, 'acc_pass' => 'john123'],
        ['user_id' => 2023305122, 'acc_pass' => 'jevi123'],
        ['user_id' => 2023305178, 'acc_pass' => 'mark123'],
        ['user_id' => 2023306358, 'acc_pass' => 'jay123']
    ];
    
    $stmt = $con->prepare("INSERT INTO user_acc (user_id, acc_pass) VALUES (?, ?)");
    
    foreach ($accounts as $account) {
        $stmt->bind_param("is", $account['user_id'], $account['acc_pass']);
        $stmt->execute();
    }
    
    echo "Accounts have been set up successfully!<br>";
    echo "You can now log in with:<br>";
    echo "Username: 2023304637<br>";
    echo "Password: redjan123<br>";
    
    $stmt->close();
    $con->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 