<?php
// Database configuration
$host = 'localhost'; // Your database host
$dbname = 'boarding_house_system'; // Your database name
$username = 'root'; // Your database username (default for XAMPP)
$password = ''; // Your database password (leave empty if there is none)

// Create a MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize message variable
$message = ""; // Variable to hold success or error messages

// Start session to store success message
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confipassword = trim($_POST['confipassword']);

    // Validate passwords match
    if ($password !== $confipassword) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $checkEmailStmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        echo "Email already exists. Please use a different email.";
        exit;
    }

    // Prepare an SQL statement to insert user data
    $stmt = $mysqli->prepare("INSERT INTO users (name, lastname, email, password) VALUES (?, ?, ?, ?)");
    
    if ($stmt) {
        // Bind parameters (s for string)
        $stmt->bind_param("ssss", $name, $lastname, $email, $hashedPassword);

        // Execute the statement
        if ($stmt->execute()) {
            // Set success message and redirect to login page
            $_SESSION['message'] = "Registered Successfully! Proceed to Log In!";
            header("Location: login.php");
            exit();
        } else {
            echo "Error: Could not register. " . $stmt->error;
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
    <title>Sign Up Form</title>
</head>
<body>
    <div class="container"> <!-- Added container div for styling -->
        <h2>Sign Up</h2>

        <!-- Display success message if set -->
        <?php if (!empty($message)): ?>
            <div class="message" style="color: green; text-align: center;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="POST"> <!-- Action points to this PHP file -->
            <input type="text" name="name" placeholder="First Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required> <!-- Corrected type -->
            <input type="password" name="password" placeholder="Password" required> <!-- Corrected type -->
            <input type="password" name="confipassword" placeholder="Confirm Password" required> <!-- Corrected type -->
            <input type="submit" value="Sign Up" name="signup_button">
        </form>
        <p style="text-align: center;">Already have an account? <a href="login.php">Log In</a></p> <!-- Link to login page -->
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
        }

        input[type="submit"]:hover {
            background-color: #165eab; /* Darker blue on hover */
        }

        p {
            text-align: center;
        }
        
        .message {
              margin-bottom: 20px;
              padding: 10px;
              background-color: #d4edda; /* Light green background */
              color: #155724; /* Dark green text */
              border-radius: 5px;
              border: 1px solid #c3e6cb; /* Border color */
          }
    </style>
</body>
</html>