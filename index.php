<?php
session_start();
// Only destroy session if user is logged in
if (isset($_SESSION['id'])) {
    $_SESSION = array();
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyEV-Charging Station</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1>Welcome to EasyEV-Charging Station</h1>
        <p>We are offering electrical vehicle charging stations at several locations in Wollongong.</p>
        
        <div class="form-container">
            <?php if (isset($_GET['logout'])): ?>
                <div class="message">You have been successfully logged out.</div>
            <?php endif; ?>
            
            <h2>Login</h2>
            <form action="login.php" method="post">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" name="psw" required placeholder="Enter your password">
                </div>
                <div class="form-actions">
                    <input type="submit" name="login" value="Login">
                    <input type="reset" name="reset" value="Reset Form">
                </div>
                <div>
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>