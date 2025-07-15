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

        <h2>User List</h2>

        <!-- Search Form -->
        <div>
            <form action="user_page.php" method="post">
                <select name="checkin_user">
                    <option value="all" <?php echo (!isset($_POST['checkin_user']) || $_POST['checkin_user'] == 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="checkin" <?php echo (isset($_POST['checkin_user']) && $_POST['checkin_user'] == 'checkin') ? 'selected' : ''; ?>>Currently Checked-In Users</option>
                </select>
                <input type="submit" name="search" value="Search">
            </form>
            <br>   
        </div>

        <!-- Results Table -->
        <?php
        if (isset($_POST['search'])) {
            $result = $role->searchUsers($conn, $_POST['checkin_user']);
            if ($result && $result->num_rows > 0) {
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>User Name</th>
                            <th>User Phone</th>
                            <th>User Email</th>
                            <th>User Type</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="message">No users found.</p>
            <?php } ?>
        <?php } ?>



</body>

</html>
<?php
// Close the connection if it exists
if (isset($conn)) {
    $conn->close();
}
?>

