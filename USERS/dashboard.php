
<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's name
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT name, lastname FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_fullname = htmlspecialchars(ucwords(strtolower($user['name'] . " " . $user['lastname'])));

// Handle form submission for new reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_reservation'])) {
    $user_id = $_SESSION['user_id'];
    $apartment_id = $_POST['apartment_id'];
    $reservation_date = $_POST['reservation_date'];
    
    // Check if apartment is already reserved for this date
    $check_sql = "SELECT * FROM reservations 
                  WHERE apartment_id = ? 
                  AND reservation_date = ? 
                  AND status != 'Cancelled'";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $apartment_id, $reservation_date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "This apartment is already reserved for the selected date and time.";
    } else {
        // Insert with default status
        $sql = "INSERT INTO reservations (user_id, apartment_id, reservation_date) 
                VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $apartment_id, $reservation_date);
        
        if ($stmt->execute()) {
            $success_message = "Reservation added successfully!";
        } else {
            $error_message = "Error adding reservation: " . $conn->error;
        }
    }
}

// Handle Delete Reservation
if (isset($_POST['delete_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify ownership and delete
    $delete_sql = "DELETE FROM reservations WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $reservation_id, $user_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Reservation cancelled successfully!";
    } else {
        $error_message = "Error cancelling reservation.";
    }
}

// Handle Edit Reservation
if (isset($_POST['edit_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_date = $_POST['new_reservation_date'];
    $user_id = $_SESSION['user_id'];
    
    // Check if new date is already reserved
    $check_sql = "SELECT * FROM reservations 
                  WHERE apartment_id = ? 
                  AND reservation_date = ? 
                  AND id != ? 
                  AND status != 'Cancelled'";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("isi", $_POST['apartment_id'], $new_date, $reservation_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "This apartment is already reserved for the selected date and time.";
    } else {
        // Update reservation
        $update_sql = "UPDATE reservations 
                      SET reservation_date = ? 
                      WHERE id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $new_date, $reservation_id, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Reservation updated successfully!";
        } else {
            $error_message = "Error updating reservation.";
        }
    }
}

// Fetch available apartments
$apartments_sql = "SELECT * FROM apartments";
$apartments_result = $conn->query($apartments_sql);

// Fetch user's reservations
$user_id = $_SESSION['user_id'];
$reservations_sql = "SELECT r.*, a.name as apartment_name 
                     FROM reservations r 
                     JOIN apartments a ON r.apartment_id = a.id 
                     WHERE r.user_id = ? 
                     ORDER BY r.reservation_date DESC";
$stmt = $conn->prepare($reservations_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
        }

        #snackbar.success {
            background-color: #198754;
        }

        #snackbar.error {
            background-color: #dc3545;
        }

        #snackbar.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
</head>
<body>
    <!-- Snackbar div -->
    <div id="snackbar"></div>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?php echo $user_fullname; ?>!</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        
        <?php
        if (isset($success_message)) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showSnackbar('" . addslashes($success_message) . "', 'success');
                    });
                  </script>";
        }
        if (isset($error_message)) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showSnackbar('" . addslashes($error_message) . "', 'error');
                    });
                  </script>";
        }
        ?>

        <!-- Add Reservation Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Make a New Reservation</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="apartment_id" class="form-label">Select Apartment</label>
                        <select class="form-select" name="apartment_id" required>
                            <option value="">Choose an apartment...</option>
                            <?php while($apartment = $apartments_result->fetch_assoc()): ?>
                                <option value="<?php echo $apartment['id']; ?>">
                                    <?php echo htmlspecialchars($apartment['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reservation_date" class="form-label">Reservation Date and Time</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               name="reservation_date" 
                               min="<?php echo date('Y-m-d\TH:i'); ?>"
                               required>
                    </div>
                    <button type="submit" name="submit_reservation" class="btn btn-primary">Submit Reservation</button>
                </form>
            </div>
        </div>

        <!-- Display User's Reservations -->
        <div class="card">
            <div class="card-header">
                <h4>Your Reservations</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Apartment</th>
                                <th>Reservation Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($reservation = $reservations_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['apartment_name']); ?></td>
                                    <td><?php echo date('F j, Y g:i A', strtotime($reservation['reservation_date'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $reservation['status'] == 'Confirmed' ? 'bg-success' : 
                                            ($reservation['status'] == 'Cancelled' ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php echo $reservation['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $reservation['id']; ?>">
                                            Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="delete_reservation" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                                Cancel
                                            </button>
                                        </form>
                                        
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $reservation['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Reservation</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                            <input type="hidden" name="apartment_id" value="<?php echo $reservation['apartment_id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">New Reservation Date and Time</label>
                                                                <input type="datetime-local" class="form-control" 
                                                                       name="new_reservation_date" 
                                                                       value="<?php echo date('Y-m-d\TH:i', strtotime($reservation['reservation_date'])); ?>" 
                                                                       min="<?php echo date('Y-m-d\TH:i'); ?>"
                                                                       required>
                                                            </div>
                                                            <button type="submit" name="edit_reservation" class="btn btn-primary">
                                                                Save Changes
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSnackbar(message, type) {
            var snackbar = document.getElementById("snackbar");
            snackbar.textContent = message;
            snackbar.className = "show " + type;
            setTimeout(function(){ 
                snackbar.className = snackbar.className.replace("show", ""); 
            }, 3000);
        }
    </script>
</body>
</html>
