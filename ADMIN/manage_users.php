<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connection.php';

// Handle user deletion
if (isset($_GET['delete']) && isset($_SESSION['admin_id'])) {
    $userId = intval($_GET['delete']);
    
    // Prevent deleting the current admin
    if ($userId == $_SESSION['admin_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        // Start a transaction for data integrity
        $conn->begin_transaction();
        
        try {
            // First, delete associated reservations
            $deleteReservationsSql = "DELETE FROM reservations WHERE user_id = ?";
            $reservationsStmt = $conn->prepare($deleteReservationsSql);
            $reservationsStmt->bind_param("i", $userId);
            $reservationsStmt->execute();
            
            // Then delete the user
            $deleteUserSql = "DELETE FROM users WHERE id = ?";
            $userStmt = $conn->prepare($deleteUserSql);
            $userStmt->bind_param("i", $userId);
            
            if ($userStmt->execute()) {
                // Log the deletion event
                $logSql = "INSERT INTO admin_logs (admin_id, action, target_user_id, timestamp) VALUES (?, 'user_deleted', ?, NOW())";
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("ii", $_SESSION['admin_id'], $userId);
                $logStmt->execute();
                
                // Commit the transaction
                $conn->commit();
                $success_message = "User and associated reservations deleted successfully.";
            } else {
                throw new Exception("Failed to delete user: " . $conn->error);
            }
            
            // Close statements
            $reservationsStmt->close();
            $userStmt->close();
            $logStmt->close();
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
} elseif (isset($_GET['delete']) && !isset($_SESSION['admin_id'])) {
    $error_message = "Unauthorized access. Please log in as an admin.";
}

// Fetch users with additional information
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM reservations WHERE user_id = u.id) as reservation_count
        FROM users u
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);

if ($result === false) {
    $error_message = "Error fetching users: " . $conn->error;
} else {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-management {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .add-user-btn {
            background: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .add-user-btn:hover {
            background: #2980b9;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .user-table th,
        .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .user-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-table tr:hover {
            background-color: #f8f9fa;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="user-management">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="actions-bar">
            <h2><i class="fas fa-users"></i> Manage Users</h2>
            <a href="add_user.php" class="add-user-btn">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>

        <?php if (isset($users) && count($users) > 0): ?>
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Reservations</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <div class="user-status">
                                        <?php echo htmlspecialchars($user['name'] . ' ' . $user['lastname']); ?>
                                        <?php if ($user['reservation_count'] > 0): ?>
                                            <span class="status-badge">Active</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['reservation_count']; ?> reservations</td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="user-actions">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this user? This will also delete all their reservations.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No users found. Add your first user to get started!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
