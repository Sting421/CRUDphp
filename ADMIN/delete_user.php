<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and authentication check
require_once '../includes/db_connection.php';

// Initialize variables
$message = '';
$message_type = '';

// Check if user ID is provided in the URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "User ID is required.";
    header("Location: admin_dashboard.php?page=manage_users");
    exit;
}

$userId = intval($_GET['id']);

// Directly delete the user
try {
    // Directly use the specified SQL command
    $deleteQuery = "DELETE FROM users WHERE `users`.`id` = " . $userId;
    
    if ($conn->query($deleteQuery)) {
        // Log the deletion event
        $log_sql = "INSERT INTO admin_logs (admin_id, action, target_user_id, timestamp) VALUES (?, 'user_deleted', ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ii", $_SESSION['admin_id'], $userId);
        $log_stmt->execute();

        // Set success message in session
        $_SESSION['success'] = "User deleted successfully.";
        
        // Redirect to manage users
        header("Location: admin_dashboard.php?page=manage_users");
        exit;
    } else {
        throw new Exception("Error deleting user: " . $conn->error);
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: admin_dashboard.php?page=manage_users");
    exit;
}
?>
