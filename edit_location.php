<?php
require_once 'roleClass.php';
require_once 'dbConnection.php';
session_start();
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyEV-Charging Station</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1>EasyEV-Charging Station</h1>
        <div class="nav-menu">
            <a href='location_page.php'>Locations</a>
            <?php if ($role->type == 'administrator'): ?>
                <a href='user_page.php'>User Management</a>
            <?php else: ?>
                <a href='charging_page.php'>Charging Dashboard</a>
            <?php endif; ?>
            <a href='index.php?logout=true'>Log Out</a>
        </div>

        <h2>Edit Location</h2>

        <?php
        try {
            $locationId = trim(stripslashes($_GET['id']));
            $location = $role->getLocation($conn, $locationId);
            if (!$location) {
                die("<div class='error-message'>Location not found.</div>");
            }
        } catch (mysqli_sql_exception $e) {
            die("Error loading location: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
        }
         // Handle form submission
         if (isset($_POST['update'])) {
            try {
                $location['description'] = trim($_POST['description']);
                $location['charging_station'] = trim($_POST['charging_station']);
                $location['cost_per_hour'] = trim($_POST['cost_per_hour']);
                $location['available_stations'] = trim($_POST['available_stations']);
                $role->updateLocation($conn, $locationId, $location['description'], $location['charging_station'], $location['cost_per_hour'], $location['available_stations']);
                
                echo "<div class='success-message'>";
                echo "<p class='message'>Location updated successfully!</p>";
                echo "<p>Use the navigation menu to go back to the location page</p>";
                echo "</div>";
            } catch (mysqli_sql_exception $e) {
                die("Error updating location: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
            }
        }
        ?>

        <div>
            <form action="edit_location.php?id=<?php echo $locationId ?>" method="post">
                <div>
                    <label for="description">Description:</label>
                    <input type="text" id="description" name="description" placeholder="Description" required 
                           value="<?php echo htmlspecialchars($location['description']) ?>">
                </div>
                <div>
                    <label for="charging_station">Number of Charging Stations:</label>
                    <input type="number" id="charging_station" name="charging_station" placeholder="Number of Charging Stations" required min="1" 
                           value="<?php echo htmlspecialchars($location['charging_station']) ?>">
                </div>
                <div>
                    <label for="cost_per_hour">Cost per Hour:</label>
                    <input type="number" id="cost_per_hour" name="cost_per_hour" placeholder="Cost per Hour" required min="0" step="0.01"
                           value="<?php echo htmlspecialchars($location['cost_per_hour']) ?>">
                </div>
                <div>
                    <label for="available_stations">Available Stations:</label>
                    <input type="number" id="available_stations" name="available_stations" placeholder="Available Stations" required min="0"
                           value="<?php echo htmlspecialchars($location['available_stations']) ?>">
                </div>
                <div class="form-actions">
                    <input type="submit" name="update" value="Update Location">
                </div>
                <br>
            </form>
        </div>
    </div>
</body>
</html>

<?php
// Close the connection if it exists
if (isset($conn)) {
    $conn->close();
}
?>
