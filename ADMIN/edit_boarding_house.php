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

// Check if boarding house ID is provided in the URL
if (!isset($_GET['id'])) {
    die("Boarding house ID is required.");
}

$boardingHouseId = intval($_GET['id']);

// Fetch the boarding house's current details
$sql = "SELECT id, name, details FROM boarding_houses WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $boardingHouseId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Boarding house not found.");
}

$boardingHouse = $result->fetch_assoc();
$stmt->close();

// Handle the form submission for updating boarding house details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $details = $_POST['details'];

    $updateSql = "UPDATE boarding_houses SET name = ?, details = ? WHERE id = ?";
    $updateStmt = $mysqli->prepare($updateSql);
    $updateStmt->bind_param("ssi", $name, $details, $boardingHouseId);

    if ($updateStmt->execute()) {
        $message = "Boarding house updated successfully.";
    } else {
        $message = "Error updating boarding house: " . $mysqli->error;
    }

    $updateStmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Boarding House</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <h1>Edit Boarding House</h1>

    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="name">Boarding House Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($boardingHouse['name']); ?>" required>
        <br><br>

        <label for="details">Details:</label>
        <textarea id="details" name="details" required><?php echo htmlspecialchars($boardingHouse['details']); ?></textarea>
        <br><br>

        <button type="submit">Update Boarding House</button>
    </form>
    
    <br>
    <a href="manage_boarding_houses.php">Back to Manage Boarding Houses</a>
</body>
</html>
