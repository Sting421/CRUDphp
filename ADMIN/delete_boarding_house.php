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

// Check if delete_id is provided in the URL
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']); // Sanitize the id input

    // Delete the boarding house with the provided ID
    $deleteSql = "DELETE FROM boarding_houses WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteSql);
    $deleteStmt->bind_param("i", $deleteId);
    
    if ($deleteStmt->execute()) {
        // Redirect to manage_boarding_houses.php with a success message
        header('Location: manage_boarding_houses.php?message=Boarding house deleted successfully');
        exit();
    } else {
        // Redirect to manage_boarding_houses.php with an error message
        header('Location: manage_boarding_houses.php?message=Error deleting boarding house');
        exit();
    }
}

$mysqli->close();
?>
