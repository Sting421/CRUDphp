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

// Fetch the apartment's current details before deletion
$sql = "SELECT id, name FROM apartments WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $apartmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Apartment not found.");
}

$apartment = $result->fetch_assoc();
$stmt->close();

// Handle the form submission for deleting apartment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteSql = "DELETE FROM apartments WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteSql);
    $deleteStmt->bind_param("i", $apartmentId);

    if ($deleteStmt->execute()) {
        $message = "Apartment deleted successfully.";
        // Redirect after deletion
        header("Location: manage_apartments.php");
        exit;
    } else {
        $message = "Error deleting apartment: " . $mysqli->error;
    }

    $deleteStmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Apartment</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <h1>Delete Apartment</h1>
    <p>Are you sure you want to delete the apartment: <strong><?php echo htmlspecialchars($apartment['name']); ?></strong>?</p>
    
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <button type="submit">Delete Apartment</button>
    </form>

    <a href="manage_apartments.php">Back to Manage Apartments</a>
</body>
</html>
