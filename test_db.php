<?php
// Simple database connection test for InfinityFree
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Update these with your actual database credentials
$serverName = "sqlXXX.infinityfree.com";  // Your MySQL hostname
$uname = "if0_XXXXXXXX";                  // Your MySQL username
$serverPassword = "your_password";        // Your MySQL password
$dbName = "if0_XXXXXXXX_easyev";         // Your database name

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    $conn = new mysqli($serverName, $uname, $serverPassword, $dbName);
    
    if ($conn->connect_error) {
        die("<p style='color:red'>❌ Connection failed: " . $conn->connect_error . "</p>");
    }
    
    echo "<p style='color:green'>✅ Database connected successfully!</p>";
    echo "<p>Server: " . $serverName . "</p>";
    echo "<p>Database: " . $dbName . "</p>";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<p>✅ Query test successful</p>";
        echo "<p>Tables found: " . $result->num_rows . "</p>";
        
        while ($row = $result->fetch_array()) {
            echo "<p> - " . $row[0] . "</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ No tables found (this is normal for new database)</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 