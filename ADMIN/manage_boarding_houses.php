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

// Fetch all boarding houses
$sql = "SELECT id, name, details FROM boarding_houses";
$result = $mysqli->query($sql);

// Handle delete boarding house
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $deleteSql = "DELETE FROM boarding_houses WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteSql);
    $deleteStmt->bind_param("i", $deleteId);
    if ($deleteStmt->execute()) {
        $message = "Boarding house deleted successfully.";
    } else {
        $message = "Error deleting boarding house: " . $mysqli->error;
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
    <title>Manage Boarding Houses</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <h1>Manage Boarding Houses</h1>

    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <a href="add_boarding_house.php">Add New Boarding House</a>
    <table border="1">
        <thead>
            <tr>
                <th>Boarding House Name</th>
                <th>Details</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($boardingHouse = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($boardingHouse['name']); ?></td>
                        <td><?php echo htmlspecialchars($boardingHouse['details']); ?></td>
                        <td>
                            <a href="edit_boarding_house.php?id=<?php echo $boardingHouse['id']; ?>">Edit</a> | 
                            <a href="delete_boarding_house.php?delete_id=<?php echo $boardingHouse['id']; ?>" onclick="return confirm('Are you sure you want to delete this boarding house?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No boarding houses found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
