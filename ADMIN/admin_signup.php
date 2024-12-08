<?php
// Database configuration
$host = 'localhost'; 
$dbname = 'boarding_house_system'; 
$username = 'root';
$password = ''; 

// Create a MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize an error message variable
$errorMessage = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $adminName = trim($_POST['admin_name']);
    $adminEmail = trim($_POST['admin_email']);
    $adminPassword = trim($_POST['admin_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate passwords match
    if ($adminPassword !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } else {
        // Check if the email already exists
        $checkEmailStmt = $mysqli->prepare("SELECT * FROM admins WHERE email = ?");
        $checkEmailStmt->bind_param("s", $adminEmail);
        $checkEmailStmt->execute();
        $result = $checkEmailStmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Error: Email already exists.";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

            // Prepare an SQL statement to insert admin data
            $stmt = $mysqli->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
            
            if ($stmt) {
                // Bind parameters (s for string)
                $stmt->bind_param("sss", $adminName, $adminEmail, $hashedPassword);

                // Execute the statement
                if ($stmt->execute()) {
                    echo "<script>alert('Admin registration successful!');</script>";
                    // Optionally redirect to login page or perform further actions here
                } else {
                    $errorMessage = "Error: Could not register. " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                $errorMessage = "Error preparing statement: " . $mysqli->error;
            }
        }

        // Close the email check statement
        $checkEmailStmt->close();
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
    <title>Admin Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4; /* Light gray background */
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full height of viewport */
            margin: 0; /* Remove default margin */
        }

        .container {
            background-color: #fff; /* White background for the form */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px; /* Fixed width for the form */
        }

        h2 {
            text-align: center;
            margin-bottom: 20px; /* Space below heading */
            color: #333; /* Dark text color */
        }

        .error-message {
            color: #d9534f; /* Red color for error messages */
            margin-bottom: 20px; /* Space below error message */
            text-align: center; /* Center text in error message */
        }

        label {
            display: block;
            margin-bottom: 5px; /* Space below labels */
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px); /* Full width minus padding */
            padding: 10px; /* Padding inside inputs */
            margin-bottom: 15px; /* Space below inputs */
            border-radius: 4px; /* Rounded corners */
            border: 1px solid #ccc; /* Light gray border */
            font-size: 16px; /* Font size for inputs */
        }

        input[type="submit"] {
            background-color: #28a745; /* Green background */
            color: white; /* White text color */
            border: none; /* No border */
            padding: 12px; /* Padding inside button */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 16px; /* Font size for button */
            width: 100%; /* Full width for button */
        }

        input[type="submit"]:hover {
            background-color: #218838; /* Darker green on hover */
        }

        p {
            text-align: center; /* Center text in paragraph */
        }

        a {
            color: #007bff; /* Link color */
            text-decoration: none; /* Remove underline from links */
        }

        a:hover {
            text-decoration: underline; /* Underline on hover for links */
        }
        
    </style>
</head>
<body>
    <div class="container"> <!-- Added container div for styling -->
        
        <?php if (!empty($errorMessage)): ?>
          <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <h2>Admin Sign Up</h2>
        
        <form action="admin_signup.php" method="POST">
            <label for="admin_name">Name:</label>
            <input type="text" id="admin_name" name="admin_name" required>

            <label for="admin_email">Email:</label>
            <input type="email" id="admin_email" name="admin_email" required>

            <label for="admin_password">Password:</label>
            <input type="password" id="admin_password" name="admin_password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <input type="submit" value="Sign Up">
        </form>
        
        <p style="text-align:center;">Already have an account? <a href="admin_login.php">Log In</a></p> <!-- Link to admin login page -->
        
    </div>
</body>
</html>