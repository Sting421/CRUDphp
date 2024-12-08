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

// Fetch all apartments
$sql = "SELECT id, name, location, price FROM apartments";
$result = $mysqli->query($sql);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Apartments</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .action-buttons a {
            text-decoration: none;
            margin: 0 5px;
            font-size: 1.1rem;
        }
        .edit-btn {
            color: #198754;
        }
        .delete-btn {
            color: #dc3545;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Manage Apartments</h1>
            <a href="add_apartment.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Apartment
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Location</th>
                                <th scope="col">Price</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($apartment = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $apartment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($apartment['name']); ?></td>
                                        <td>
                                            <i class="bi bi-geo-alt text-primary"></i>
                                            <?php echo htmlspecialchars($apartment['location']); ?>
                                        </td>
                                        <td>
                                            <i class="bi bi-currency-dollar"></i>
                                            <?php echo htmlspecialchars($apartment['price']); ?>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="edit_apartment.php?id=<?php echo $apartment['id']; ?>" 
                                               class="edit-btn" 
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete_apartment.php?id=<?php echo $apartment['id']; ?>" 
                                               class="delete-btn" 
                                               onclick="return confirm('Are you sure you want to delete this apartment?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No apartments found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
