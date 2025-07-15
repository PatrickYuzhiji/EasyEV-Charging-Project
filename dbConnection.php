<?php
// InfinityFree Database Configuration
$serverName = "sql312.infinityfree.com";
$uname = "if0_*******";
$serverPassword = "********";
$dbName = "if0_********_easyev";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect directly to the database (database must exist in hosting control panel)
    $conn = new mysqli($serverName, $uname, $serverPassword, $dbName);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to prevent encoding issues
    $conn->set_charset("utf8");
    
    // Create tables if they don't exist
    require_once("createTable.php");
    
    echo "<!-- Database connected successfully -->";

} catch (mysqli_sql_exception $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("General error: " . $e->getMessage());
}
?>