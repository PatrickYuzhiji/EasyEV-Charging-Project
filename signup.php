<?php
session_start();
$Body = "";
$registration_success = false;
$errors = 0;

if (!empty($_POST)) {  // Only validate if form was submitted
	if (empty($_POST['name'])) {
		++$errors;
		$Body .= "<p>You need to enter your name.</p>\n";
	}

	if (empty($_POST['phone'])) {
		++$errors;
		$Body .= "<p>You need to enter your phone number.</p>\n";
	}

	if (empty($_POST['email'])) {
		++$errors;
		$Body .= "<p>You need to enter an e-mail address.</p>\n";
	} else {
		$email = trim(stripslashes($_POST['email']));
		if (preg_match("/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)*(\.[a-z]{2,3})$/i", $email) == 0) {
			++$errors;
			$Body .= "<p>You need to enter a valid e-mail address.</p>\n";
			$email = "";
		}
	}

	if (empty($_POST['password'])) {
		++$errors;
		$Body .= "<p>You need to enter a password.</p>\n";
		$password = "";
	} else {
		$password = trim(stripslashes($_POST['password']));
	}

	if (empty($_POST['password2'])) {
		++$errors;
		$Body .= "<p>You need to enter a confirmation password.</p>\n";
		$password2 = "";
	} else {
		$password2 = trim(stripslashes($_POST['password2']));
	}

	if ((!(empty($password))) && (!(empty($password2)))) {
		if (strlen($password) < 6) {
			++$errors;
			$Body .= "<p>The password must be at least 6 characters long.</p>\n";
			$password = "";
			$password2 = "";
		}
		if ($password !== $password2) {
			++$errors;
			$Body .= "<p>The passwords do not match.</p>\n";
			$password = "";
			$password2 = "";
		}
	}

	if ($errors == 0) {
		try {
			include_once("dbConnection.php");
			
			$TableName = "User";
			$stmt = $conn->prepare("SELECT COUNT(*) FROM $TableName WHERE email = ?");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$result = $stmt->get_result();
			$Row = $result->fetch_row();
			
			if ($Row[0] > 0) {
				$Body .= "<p>The email address entered (" . htmlentities($email) . ") is already registered.</p>\n";
				++$errors;
			}
			$stmt->close();
		} catch (mysqli_sql_exception $e) {
			$Body .= "<p>Unable to connect to the database</p>\n";
			++$errors;
		}
	}

	if ($errors == 0) {
		try {
			$name = trim(stripslashes($_POST['name']));
			$phone = trim(stripslashes($_POST['phone']));
			$type = trim(stripslashes($_POST['type']));
			$password_md5 = md5($password);
			
			$stmt = $conn->prepare("INSERT INTO User (name, phone, email, password, type) VALUES (?, ?, ?, ?, ?)");
			$stmt->bind_param("sssss", $name, $phone, $email, $password_md5, $type);
			$stmt->execute();
			
			$userId = $conn->insert_id;
            include_once("roleClass.php");
            if ($type == 'administrator') {
                $role = new Administrator($userId, $name, $type);
            } else {
                $role = new User($userId, $name, $type);
            }
			$_SESSION['role'] = $role;
			
			$stmt->close();
			
			$registration_success = true;
			$Body .= "<p>Registration successful! Welcome, " . htmlentities($name) . "!</p>\n";
			$Body .= "<p>You can now <a href='location_page.php'>View Charging Locations</a></p>\n";
		} catch (mysqli_sql_exception $e) {
			$Body .= "<p>Unable to create account. Please try again.</p>\n";
			++$errors;
		}
	}
    
    // Close the connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasyEV-Charging Station - Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to EasyEV-Charging Station</h1>
        <p>We are offering electrical vehicle charging stations at several locations in Wollongong.</p>
        
        <?php if ($registration_success): ?>
            <div class="success-message">
                <?php echo $Body; ?>
            </div>
        <?php else: ?>
            <div class="form-container">
                <h2>New User Registration</h2>
                <?php if ($errors > 0): ?>
                    <div class="error-message">
                        <?php echo $Body; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="signup.php">
                    <div>
                        <label for="type">Select your role:</label>
                        <select name="type" id="type">
                            <option value="user">User</option>
                            <option value="administrator">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label for="name">Enter your name:</label>
                        <input type="text" id="name" name="name" required />
                    </div>
                    <div>
                        <label for="phone">Enter your phone number:</label>
                        <input type="tel" id="phone" name="phone" required />
                    </div>
                    <div>
                        <label for="email">Enter your e-mail address:</label>
                        <input type="email" id="email" name="email" required />
                    </div>
                    <div>
                        <label for="password">Enter a password:</label>
                        <input type="password" id="password" name="password" required />
                    </div>
                    <div>
                        <label for="password2">Confirm your password:</label>
                        <input type="password" id="password2" name="password2" required />
                    </div>
                    <p><em>(Passwords are case-sensitive and must be at least 6 characters long)</em></p>
                    
                    <div class="form-actions">
                        <input type="reset" name="reset" value="Reset Form" />
                        <input type="submit" name="register" value="Register" />
                    </div>
                    <div class="form-actions">
                        Already have an account? <a href="index.php">Login here</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>