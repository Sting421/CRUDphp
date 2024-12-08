<?php
session_start();

$host = 'localhost'; 
$dbname = 'boarding_house_system'; 
$username = 'root'; 
$password = ''; 

$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch all reservations with user and apartment details
$sql = "SELECT r.id, r.reservation_date, r.status,
               u.name as user_name, u.email as user_email,
               a.name as apartment_name, a.location as apartment_location
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN apartments a ON r.apartment_id = a.id
        ORDER BY r.reservation_date DESC";
$result = $mysqli->query($sql);

if ($result === false) {
    $error_message = "Error fetching reservations: " . $mysqli->error;
} else {
    $reservations = $result->fetch_all(MYSQLI_ASSOC);
}

$mysqli->close();

// Function to get appropriate status badge class
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'approved':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        case 'completed':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .reservation-status {
            font-size: 0.875rem;
        }
        .table th {
            font-weight: 600;
            color: #495057;
        }
        .action-buttons a {
            text-decoration: none;
            margin: 0 5px;
            font-size: 1.1rem;
        }
        .edit-btn {
            color: #198754;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Manage Reservations</h1>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($reservations)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="h5 text-muted mt-3">No reservations found</p>
                        <p class="text-muted">Reservations will appear here once users make bookings.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Apartment</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                        <td>
                                            <div>
                                                <i class="bi bi-person text-primary"></i>
                                                <?php echo htmlspecialchars($reservation['user_name']); ?>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-envelope"></i>
                                                <?php echo htmlspecialchars($reservation['user_email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="bi bi-building text-primary"></i>
                                                <?php echo htmlspecialchars($reservation['apartment_name']); ?>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="bi bi-geo-alt"></i>
                                                <?php echo htmlspecialchars($reservation['apartment_location']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($reservation['status']); ?> reservation-status">
                                                <?php echo htmlspecialchars($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                               class="edit-btn"
                                               title="Edit Reservation">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
