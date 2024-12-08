<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php"); // Redirect to login if not logged in
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?message=You have successfully logged out.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./dashboard.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="?page=manage_users">Manage Users</a></li>
                <li><a href="?page=manage_apartments">Manage Apartments</a></li>
                <li><a href="?page=manage_reservations">Manage Reservations</a></li>
                <li><a href="?page=manage_boarding_houses">Manage Boarding Houses</a></li>
                <li><a href="?logout=true">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        // Include content based on the page selected
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            switch ($page) {
                case 'manage_users':
                    include 'manage_users.php';
                    break;
                case 'manage_apartments':
                    include 'manage_apartments.php';
                    break;
                case 'manage_reservations':
                    include 'manage_reservations.php';
                    break;
                case 'manage_boarding_houses':
                    include 'manage_boarding_houses.php';
                    break;
                default:
                    echo "<h2>Welcome to the Admin Dashboard</h2>";
            }
        } else {
            echo "<h2>Welcome to the Admin Dashboard</h2>";
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Boarding House Management System</p>
    </footer>
</body>
</html>
