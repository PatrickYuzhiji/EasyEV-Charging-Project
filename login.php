<?php
session_start();

$errors = 0;
$error_message = "";
$login_success = false;

try {
    if (!empty($_POST['email']) && !empty($_POST['psw'])) {
        include_once("dbConnection.php");
        
        $TableName = "User";
        $stmt = $conn->prepare("SELECT id, name, type FROM $TableName WHERE email = ? AND password = ?");
        $email = stripslashes($_POST['email']);
        $password_md5 = md5(stripslashes($_POST['psw']));
        $stmt->bind_param("ss", $email, $password_md5);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $error_message = "Invalid email or password. Please try again.";
            ++$errors;
        } else {
            $Row = $result->fetch_assoc();
            include_once("roleClass.php");
            // check if the user is an administrator
            if ($Row['type'] == 'administrator') {
                $role = new Administrator($Row['id'], $Row['name'], $Row['type']);
            } else {
                $role = new User($Row['id'], $Row['name'], $Row['type']);
            }
            $_SESSION['role'] = $role;
            
            // Free resources
            $result->free_result();
            $stmt->close();
            
            $login_success = true;
        }
        
        // Clean up if login failed
        if ($errors > 0) {
            $result->free_result();
            $stmt->close();
        }
    } else {
        $error_message = "Please enter both email and password.";
        ++$errors;
    }
} catch(mysqli_sql_exception $e) {
    $error_message = "A system error occurred. Please try again later.";
    ++$errors;
}

// Close the connection only once at the end if it exists
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasyEV-Charging Station - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>EasyEV-Charging Station</h1>
        <?php if ($login_success): ?>
            <div>
                <p class="message">Login successful!</p>
                <p>Now you can start to <a href="location_page.php">View Charging Locations</a></p>
            </div>
        <?php elseif ($errors > 0): ?>
            <div class="message">
                <?php echo htmlspecialchars($error_message); ?>
                <p><a href="index.php">Back to Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
