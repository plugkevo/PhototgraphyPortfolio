<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Hisia Pixels</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF4500;
            --dark-color: #1a1a1a;
            --light-gray: #cccccc;
            --white: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-color);
            color: var(--white);
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.9);
            border-bottom: 2px solid var(--primary-color);
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-size: 1.8em;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }

        .navbar-nav .nav-link {
            color: var(--light-gray) !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #ff6b35);
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dashboard-header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card .icon {
            font-size: 3em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stats-card h3 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stats-card p {
            color: var(--light-gray);
            margin: 0;
        }

        .admin-actions {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }

        .action-btn {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            color: var(--white);
            padding: 15px 25px;
            margin: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #e63e00;
            transform: translateY(-2px);
            color: var(--white);
        }

        .logout-btn {
            background: #dc3545;
        }

        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-camera"></i> Hisia Pixels Admin
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($admin_username); ?>
                </span>
                <a class="nav-link" href="?logout=1">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-header">
        <div class="container text-center">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Manage your photography website</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-images icon"></i>
                    <h3>156</h3>
                    <p>Total Photos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users icon"></i>
                    <h3>42</h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-eye icon"></i>
                    <h3>1,234</h3>
                    <p>Page Views</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-envelope icon"></i>
                    <h3>18</h3>
                    <p>New Messages</p>
                </div>
            </div>
        </div>

        <div class="admin-actions">
            <h3><i class="fas fa-cogs"></i> Quick Actions</h3>
            <div class="row mt-4">
                <div class="col-md-6">
                    <a href="#" class="action-btn">
                        <i class="fas fa-plus"></i> Add New Photo
                    </a>
                    <a href="#" class="action-btn">
                        <i class="fas fa-folder"></i> Manage Gallery
                    </a>
                    <a href="#" class="action-btn">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="#" class="action-btn">
                        <i class="fas fa-envelope"></i> View Messages
                    </a>
                    <a href="#" class="action-btn">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a href="index.html" class="action-btn">
                        <i class="fas fa-globe"></i> View Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
