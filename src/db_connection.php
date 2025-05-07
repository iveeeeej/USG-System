<?php
define("DB_USER", 'root');
define("DB_PASSWORD", '');
define("DB_NAME", 'db_usg_main');
define("DB_HOST", 'localhost');

// Create a database connection
function getDatabaseConnection() {
    $con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //Try-catch if error in LOGIN
    if ($con->connect_error) {
        error_log("Connection failed: " . $con->connect_error);
        die("Unable to connect to the database. Please try again later.");
    }
    return $con;
}
?>