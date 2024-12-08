<?php
$host = 'localhost'; 
$dbname = 'boarding_house_system'; 
$username = 'root'; 
$password = ''; 

$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $details = $_POST['details'];

    $sql = "INSERT INTO boarding_houses (name, details) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $name, $details);

    if ($stmt->execute()) {
        $message = "Boarding house added successfully.";
    } else {
        $message = "Error adding boarding house: " . $mysqli->error;
    }
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Boarding House</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <h1>Add New Boarding House</h1>

    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="name">Boarding House Name:</label>
        <input type="text" id="name" name="name" required>
        <br><br>

        <label for="details">Details:</label>
        <textarea id="details" name="details" required></textarea>
        <br><br>

        <button type="submit">Add Boarding House</button>
    </form>
    
    <br>
    <a href="manage_boarding_houses.php">Back to Manage Boarding Houses</a>
</body>
</html>
