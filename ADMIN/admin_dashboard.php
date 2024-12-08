<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php?message=You have successfully logged out.");
    exit;
}

// Get admin information (assuming it's stored in session)
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

// Get current page from URL, default to dashboard if not set
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-info {
            text-align: center;
            padding: 20px 0;
        }

        .admin-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #fff;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-avatar i {
            font-size: 40px;
            color: var(--primary-color);
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-height: calc(100vh - 140px);
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>BHMS Admin</h2>
            </div>
            <div class="admin-info">
                <div class="admin-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($admin_name); ?></h3>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="?page=dashboard" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=manage_users" class="nav-link <?php echo $current_page === 'manage_users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=manage_apartments" class="nav-link <?php echo $current_page === 'manage_apartments' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i>
                        <span>Manage Apartments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=manage_reservations" class="nav-link <?php echo $current_page === 'manage_reservations' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Manage Reservations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=manage_boarding_houses" class="nav-link <?php echo $current_page === 'manage_boarding_houses' ? 'active' : ''; ?>">
                        <i class="fas fa-house-user"></i>
                        <span>Manage Boarding Houses</span>
                    </a>
                </li>
            </ul>
            <a href="?logout=true" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><?php echo isset($_GET['page']) ? ucfirst(str_replace('_', ' ', $_GET['page'])) : 'Dashboard'; ?></h1>
            </div>
            <div class="content">
                <?php
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
                            include 'dashboard_overview.php';
                    }
                } else {
                    include 'dashboard_overview.php';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
