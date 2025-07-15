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
    <meta content="width=device-width, initial-scale=1.0">
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

        <h2>Location List</h2>

        <!-- Search Form -->
        <div>
            <form action="location_page.php" method="post">
                    <div>
                        <input type="text" name="locationId" placeholder="Location ID" 
                               value="<?php echo isset($_POST['locationId']) ? htmlspecialchars($_POST['locationId']) : ''; ?>">
                    
                    
                        <input type="text" name="description" placeholder="Description" 
                               value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>">
                   
                        <input type="number" name="charging_station" placeholder="Number of Charging Stations" 
                               value="<?php echo isset($_POST['charging_station']) ? htmlspecialchars($_POST['charging_station']) : ''; ?>" min="1">
                    
                        <input type="number" name="cost_per_hour" placeholder="Cost per Hour" 
                               value="<?php echo isset($_POST['cost_per_hour']) ? htmlspecialchars($_POST['cost_per_hour']) : ''; ?>">
                    
                        <select name="available_status">
                            <option value="available" <?php echo (!isset($_POST['available_status']) || $_POST['available_status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <?php if ($role->type == 'administrator'): ?>
                                <option value="full" <?php echo (isset($_POST['available_status']) && $_POST['available_status'] == 'full') ? 'selected' : ''; ?>>Full</option>
                                <option value="all" <?php echo (isset($_POST['available_status']) && $_POST['available_status'] == 'all') ? 'selected' : ''; ?>>All</option>
                            <?php endif; ?>
                        </select>
                    </div>
                <div class="form-actions">
                    <input type="submit" name="search" value="Search">
                </div>
            </form>
            <p><small>Leave fields empty and click search to list all locations</small></p>
        </div>

        <!-- Results Table -->
        <?php if (isset($_POST['search'])): 
            $locationId = trim($_POST['locationId']);
            $description = trim($_POST['description']);
            $charging_station = trim($_POST['charging_station']);
            $cost_per_hour = trim($_POST['cost_per_hour']);
            $available_status = $_POST['available_status'];

            try {
                $result = $role->searchLocation($conn, $locationId, $description, $charging_station, $cost_per_hour, $available_status);
            } catch (mysqli_sql_exception $e) {
                die("Error searching location: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
            }
            if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Location ID</th>
                            <th>Description</th>
                            <th>Charging Stations</th>
                            <th>Cost per Hour</th>
                            <th>Available Stations</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['locationId']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['charging_station']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($row['cost_per_hour'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($row['available_stations']); ?></td>
                                <td><?php echo $row['available_stations'] == 0 ? 'Full' : 'Available'; ?></td>
                                <td>
                                    <?php if ($role->type == 'administrator'): ?>
                                        <a href='edit_location.php?id=<?php echo $row['locationId']; ?>'>Edit</a>
                                    <?php else: ?>
                                        <?php if ($row['available_stations'] > 0): ?>
                                            <form action="location_page.php" method="post" style="margin: 0;">
                                                <input type="hidden" name="locationId_check_in" value="<?php echo $row['locationId']; ?>">
                                                <input type="hidden" name="location_description_check_in" value="<?php echo $row['description']; ?>">
                                                <input type="hidden" name="cost_per_hour_check_in" value="<?php echo $row['cost_per_hour']; ?>">
                                                <input type="submit" name="check_in" value="Check In">
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="message">No locations found.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Check In Notification -->
        <?php if (isset($_POST['check_in'])): 
            try {
                $start_time = $role->checkIn($conn, $_POST['locationId_check_in']);
                echo "<div class='message'>";
                echo "<p>You just checked in our charging station at " . htmlspecialchars($_POST['location_description_check_in']) . "!</p>";
                echo "<p>Start time: " . htmlspecialchars($start_time) . "</p>";
                echo "<p>This charging location station cost $" . htmlspecialchars(number_format($_POST['cost_per_hour_check_in'], 2)) . " per hour</p>";
                echo "</div>";
                echo "<p>Please remember to check out before leaving in our <a href='charging_page.php'>Charging Dashboard</a></p>";
    
            } catch (mysqli_sql_exception $e) {
                die("Error checking in: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
            }
        endif; ?>

        <!-- Insert Form for Administrators -->
        <?php if ($role->type == 'administrator'): ?>
            <div class="form-container">
                <h3>Add New Location</h3>
                <form action="location_page.php" method="post">
                    <div>
                        <input type="text" name="description_insert" placeholder="Description" required>
                    
                        <input type="number" name="charging_station_insert" placeholder="Number of Charging Stations" required min="1">
                   
                        <input type="number" name="cost_per_hour_insert" placeholder="Cost per Hour" required min="0">
                    </div>
                    <div class="form-actions">
                        <input type="submit" name="insert" value="Add Location">
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php
        // Handle form submission of insert
        if (isset($_POST['insert'])) {
            $description_insert = trim($_POST['description_insert']);
            $charging_station_insert = trim($_POST['charging_station_insert']);
            $cost_per_hour_insert = trim($_POST['cost_per_hour_insert']);

            // validate inputs
            if (empty($description_insert) || empty($charging_station_insert) || empty($cost_per_hour_insert)) {
                echo "<div>Please enter all fields</div>";
            }
            elseif (!is_numeric($charging_station_insert)) {
                echo "<div>Number of charging stations must be a positive integer</div>";
            }
            elseif (!is_numeric($cost_per_hour_insert)) {
                echo "<div>Cost per hour must be a positive number</div>";
            }
            else {
                try {
                    $role->insertLocation($conn, $description_insert, $charging_station_insert, $cost_per_hour_insert, $charging_station_insert);
                    echo "<div class='message'>Location added successfully!</div>";
                } catch (mysqli_sql_exception $e) {
                    echo "<div>Error inserting location: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.</div>";
                }
            }
        }
        ?>
    </div>
</body>
</html>

<?php
// Close the connection if it exists
if (isset($conn)) {
    $conn->close();
}
?>

