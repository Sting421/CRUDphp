<?php
session_start();

// Database configuration
$host = 'localhost'; 
$dbname = 'boarding_house_system'; 
$username = 'root'; 
$password = ''; 

$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $usernameOrEmail = trim($_POST['usernameOrEmail']);
    $password = trim($_POST['password']);

    // Prepare an SQL statement to fetch user data
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ? OR name = ?");
    
    if ($stmt) {
        // Bind parameters (s for string)
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);

        // Execute the statement
        $stmt->execute();
        
        // Store result to check if user exists
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User exists, now fetch the hashed password and user id
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $userId; // Store user ID in session
                $_SESSION['user'] = $usernameOrEmail; // Store username/email in session
                header("Location: dashboard.php"); // Redirect to user dashboard
                exit;
               
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that username or email.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
}

// Close the connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./signup.css"> <!-- Link to the CSS file -->
    <title>Login Form</title>
</head>
<body>
    <div class="container"> <!-- Added container div for styling -->
        <h2>User Log In</h2>
        
        <!-- Display success message if set -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']); // Clear the message after displaying
                ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="usernameOrEmail">Username or Email:</label>
            <input type="text" id="usernameOrEmail" name="usernameOrEmail" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Log In">
        </form>
        <p style="text-align: center;">Don't have an account? <a href="signup.php">Sign Up</a></p> <!-- Link to sign up page -->
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5; /* Light gray background */
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full viewport height */
            margin: 0;
        }

        .container {
            background-color: #ffffff; /* White background for the form */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 350px; /* Fixed width for the form */
        }

        h2 {
            text-align: center;
            color: #333; /* Dark text color */
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%; /* Full width inputs */
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc; /* Light gray border */
            box-sizing: border-box; /* Include padding in width */
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #1877f2; /* Change border color on focus */
            outline: none; /* Remove default outline */
        }

        input[type="submit"] {
            background-color: #1877f2; /* Facebook blue */
            color: white; /* White text color */
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 16px;
            width: 100%; /* Full width for button */
        }

        input[type="submit"]:hover {
            background-color: #165eab; /* Darker blue on hover */
        }

        p {
            text-align: center;
        }
        
        /* Add styles for success message */
        .message.success {
            background-color: #dff0d8; /* Light green background */
            color: #3c763d; /* Dark green text color */
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</body>
</html>