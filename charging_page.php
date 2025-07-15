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

        <h2>Charging List</h2>
        <div>
            <form action="charging_page.php" method="post">
                <select name="status">
                    <option value="all" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="charging" <?php echo (isset($_POST['status']) && $_POST['status'] == 'charging') ? 'selected' : ''; ?>>Charging</option>
                    <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
                <input type="submit" name="search" value="Search">
            </form>
            <br>
        </div>

        <!-- Results Table -->
        <?php
        if (isset($_POST['search'])) {
            try {
                $result = $role->searchCharging($conn, $_POST['status']);
            } catch (mysqli_sql_exception $e) {
                die("Error listing charging: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
            }
            if ($result && $result->num_rows > 0) {
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Charging ID</th>
                            <th>Location ID</th>
                            <th>Location Description</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Cost per Hour</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['chargingId']); ?></td>
                                <td><?php echo htmlspecialchars($row['locationId']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                                <td><?php echo isset($row['finish_time']) ? htmlspecialchars($row['finish_time']) : '-'; ?></td>
                                <td>$<?php echo number_format($row['cost_per_hour'], 2); ?></td>
                                <td><?php echo isset($row['cost']) ? '$' . number_format($row['cost'], 2) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'charging'): ?>
                                        <form action="charging_page.php" method="post">
                                            <input type="hidden" name="chargingId_check_out" value="<?php echo $row['chargingId']; ?>">
                                            <input type="hidden" name="locationId_check_out" value="<?php echo $row['locationId']; ?>">
                                            <input type="hidden" name="location_description_check_out" value="<?php echo $row['description']; ?>">
                                            <input type="hidden" name="start_time_check_out" value="<?php echo $row['start_time']; ?>">
                                            <input type="hidden" name="cost_per_hour_check_out" value="<?php echo $row['cost_per_hour']; ?>">
                                            <input type="submit" name="check_out" value="Check Out">
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No charging records found.</p>   
            <?php } ?>
        <?php } ?>

        <!-- Check Out Notification -->
        <?php if (isset($_POST['check_out'])) {
            try {
                $result = $role->checkOut($conn, $_POST['chargingId_check_out'], $_POST['locationId_check_out'], $_POST['start_time_check_out'], $_POST['cost_per_hour_check_out']);
                echo "<div class='message'>";
                echo "<p>You just checked out our charging station at " . $_POST['location_description_check_out'] . "!</p>";
                echo "<p>Start time: " . $_POST['start_time_check_out'] . "</p>";
                echo "<p>Finish time: " . $result['finish_time'] . "</p>";
                echo "<p>This charging station cost per hour is $" . $_POST['cost_per_hour_check_out'] . ", and the total cost you need to pay is $" . $result['cost'] . "</p>";
                echo "</div>";
            } catch (mysqli_sql_exception $e) {
                die("Error checking out: " . $e->getCode() . " : " . $e->getMessage() . "<br> Please try again.");
            }
        }
        ?>



</html>

<?php
// Close the connection if it exists
if (isset($conn)) {
    $conn->close();
}

?>