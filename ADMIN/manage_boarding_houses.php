<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connection.php';

// Fetch all boarding houses
$sql = "SELECT bh.* FROM boarding_houses bh ORDER BY bh.id DESC";
$result = $conn->query($sql);

if ($result === false) {
    $error_message = "Error fetching boarding houses: " . $conn->error;
} else {
    $boarding_houses = $result->fetch_all(MYSQLI_ASSOC);
}

// Calculate totals
$total_houses = count($boarding_houses);
$total_reservations = 0;
$avg_reservations = $total_houses > 0 ? $total_reservations / $total_houses : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Boarding Houses | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light-bg: #f8f9fa;
            --border-color: rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .dashboard-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stats-card:hover::before {
            opacity: 1;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3436;
            margin: 0.5rem 0;
        }

        .stats-label {
            color: #636e72;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            margin-top: 2rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
        }

        .btn-add-new {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.2);
        }

        .btn-add-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(67, 97, 238, 0.3);
            background: linear-gradient(135deg, #3f37c9 0%, #4361ee 100%);
            color: white;
        }

        .house-name-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .house-icon {
            width: 40px;
            height: 40px;
            background: #e3f2fd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.25rem;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table th {
            font-weight: 600;
            color: #2d3436;
            padding: 1.25rem 1.5rem;
            background: var(--light-bg);
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .details-text {
            color: #636e72;
            font-size: 0.875rem;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reservation-badge {
            background: #ffbd24;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .btn-outline-secondary, .btn-outline-danger {
            color: #000;
            border-color: #dee2e6;
            background: transparent;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover, .btn-outline-danger:hover {
            background: #f8f9fa;
            color: #000;
            border-color: #dee2e6;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #b2bec3;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #636e72;
            font-size: 1.1rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary)">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stats-value"><?php echo $total_houses; ?></div>
                    <div class="stats-label">Total Houses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--success)">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-value"><?php echo $total_reservations; ?></div>
                    <div class="stats-label">Total Reservations</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--info)">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($avg_reservations, 1); ?></div>
                    <div class="stats-label">Avg. Reservations</div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manage Boarding Houses</h4>
                    <a href="add_boarding_house.php" class="btn-add-new">
                        <i class="fas fa-plus"></i>
                        Add New House
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>House Name</th>
                            <th>Details</th>
                            <th>Reservations</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($boarding_houses) && count($boarding_houses) > 0): ?>
                            <?php foreach ($boarding_houses as $house): ?>
                                <tr>
                                    <td>
                                        <div class="house-name-container">
                                            <div class="house-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <?php echo htmlspecialchars($house['name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="details-text">
                                            <?php echo htmlspecialchars($house['details']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="reservation-badge">
                                            0 reservations
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="edit_boarding_house.php?id=<?php echo $house['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_boarding_house.php?id=<?php echo $house['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this boarding house?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <p>No boarding houses found. Add your first house to get started!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
