<?php
if (!isset($conn)) {
    die("Database connection not established");
}

$createTableUser = "CREATE TABLE IF NOT EXISTS User(
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'user id', 
    name VARCHAR(50) NOT NULL COMMENT 'user name',
    phone VARCHAR(20) UNIQUE COMMENT 'user phone number',
    email VARCHAR(50) UNIQUE COMMENT 'user email',
    password VARCHAR(200) NOT NULL COMMENT 'user password',
    type ENUM('user','administrator') COMMENT 'user type'
)";

$createTableLocation = "CREATE TABLE IF NOT EXISTS Location(
    locationId INT AUTO_INCREMENT PRIMARY KEY COMMENT 'location id',
    description VARCHAR(200) NULL COMMENT 'location description',
    charging_station INT NOT NULL COMMENT 'number of charging stations',
    cost_per_hour DECIMAL(10,2) NOT NULL COMMENT 'cost per hour',
    available_stations INT NOT NULL COMMENT 'number of currently available stations'
)";

$createTableCharging = "CREATE TABLE IF NOT EXISTS Charging(
    chargingId INT AUTO_INCREMENT PRIMARY KEY COMMENT 'charging id',
    locationId INT NOT NULL COMMENT 'location id',
    userId INT NOT NULL COMMENT 'user id',
    start_time DATETIME NOT NULL COMMENT 'start time',
    finish_time DATETIME NULL COMMENT 'finish time',
    cost DECIMAL(10,2) NULL COMMENT 'cost',
    status ENUM('charging','completed') DEFAULT 'charging' COMMENT 'charging status',
    CONSTRAINT fk_charging_location FOREIGN KEY (locationId) REFERENCES Location(locationId),
    CONSTRAINT fk_charging_user FOREIGN KEY (userId) REFERENCES User(id)
)";

// Create tables
$conn->query($createTableUser) or die("Error creating User table: " . $conn->error);
$conn->query($createTableLocation) or die("Error creating Location table: " . $conn->error);
$conn->query($createTableCharging) or die("Error creating Charging table: " . $conn->error);

// Check if the location table has records
$selectChargingLocation = "SELECT * FROM Location";
$allChargingLocation = $conn->query($selectChargingLocation);
if ($allChargingLocation->num_rows == 0) {
    // Insert default locations and set available_stations equal to charging_station initially
    $insertChargingLocation = 
    "INSERT INTO Location(description, charging_station, cost_per_hour, available_stations) VALUES
    ('11 Northfields Ave', 20, 5.00, 20),
    ('145 Princes Hwy', 30, 4.00, 30),
    ('North Wollongong station', 20, 3.00, 20),
    ('Fairy Meadow station', 15, 3.00, 15)";
    $conn->query($insertChargingLocation) or die("Error inserting default locations: " . $conn->error);
}
?>