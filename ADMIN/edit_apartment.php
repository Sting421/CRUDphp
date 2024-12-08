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

// Check if apartment ID is provided in the URL
if (!isset($_GET['id'])) {
    die("Apartment ID is required.");
}

$apartmentId = intval($_GET['id']);

// Fetch the apartment's current details
$sql = "SELECT id, name, location, price FROM apartments WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $apartmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Apartment not found.");
}

$apartment = $result->fetch_assoc();
$stmt->close();

// Handle the form submission for updating apartment details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $price = trim($_POST['price']);

    if (!empty($name) && !empty($location) && !empty($price)) {
        $updateSql = "UPDATE apartments SET name = ?, location = ?, price = ? WHERE id = ?";
        $updateStmt = $mysqli->prepare($updateSql);
        $updateStmt->bind_param("sssi", $name, $location, $price, $apartmentId);

        if ($updateStmt->execute()) {
            $message = "Apartment updated successfully.";
        } else {
            $message = "Error updating apartment: " . $mysqli->error;
        }

        $updateStmt->close();

        // Refresh apartment data after update
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $apartmentId);
        $stmt->execute();
        $apartment = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Apartment</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <h1>Edit Apartment</h1>
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label for="name">Apartment Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($apartment['name']); ?>" required>
        <br><br>
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($apartment['location']); ?>" required>
        <br><br>
        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($apartment['price']); ?>" required>
        <br><br>
        <button type="submit">Update Apartment</button>
    </form>
    <a href="manage_apartments.php">Back to Manage Apartments</a>
</body>
</html>
