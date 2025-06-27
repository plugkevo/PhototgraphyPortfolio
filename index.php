<?php
    // index.php

    // Include the session manager script. 
    // This handles session_start(), session lifetime, and inactivity checks.
    // It DOES NOT force a login redirect for this page.
    include 'session_manager.php'; 

    // You can now access $_SESSION variables here.
    // For example, to display a welcome message if logged in:
    $loggedInUserEmail = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
    $isLoggedIn = isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hisia Pixels - Photography</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF4500; /* Orangered */
            --dark-color: #1a1a1a;
            --light-gray: #cccccc;
            --white: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--dark-color);
            overflow-x: hidden; /* Prevent horizontal scroll */
            color: var(--white);
        }
        

        .hero-section {
            height: 100vh; /* Full viewport height */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--white);
            animation: change-background 20s infinite ease-in-out; /* Slower and smoother transition */
            position: relative;
            z-index: 1; /* Ensure content is above background */
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Dark overlay for better text readability */
            z-index: -1;
        }

        @keyframes change-background {
            0% { background-image: url('images/untitled-8447c.jpg'); }
            25% { background-image: url('images/IMG_9260 banner.jpg'); }
            50% { background-image: url('images/untitled-8536.jpg'); }
            75% { background-image: url('images/IMG_0803-Edit-Recovered.jpg'); }
            100% { background-image: url('images/untitled-8447c.jpg'); }
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent black for a sleek look */
            transition: background-color 0.3s ease;
            position: sticky;
            top: 0;
            z-index: 1000; /* Higher z-index */
            padding: 15px 0; /* Add padding for better spacing */
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-size: 2.2em; /* Larger brand name */
            font-weight: 700;
            font-family: 'Playfair Display', serif; /* Elegant font for brand */
        }

        .navbar-nav .nav-link {
            color: var(--light-gray) !important;
            font-weight: 500;
            margin-right: 20px;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
        }

        .navbar-toggler {
            border-color: var(--primary-color) !important;
        }

        .navbar-toggler-icon {
            /* This SVG defines the hamburger lines, colored with primary-color */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 69, 0, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        .dropdown-menu {
            background-color: var(--dark-color);
            border: none;
        }

        .dropdown-item {
            color: var(--light-gray) !important;
            transition: background-color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: var(--white) !important;
        }

        .intro-text {
            max-width: 800px;
            margin-bottom: 50px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Text shadow for readability */
        }

        .intro-text h3 {
            font-size: 2.5em; /* Larger, more impactful heading */
            color: var(--primary-color);
            font-family: 'Playfair Display', serif; /* Elegant font */
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .intro-text p {
            font-size: 1.2em;
            color: var(--light-gray);
            font-style: italic;
        }

        .explore-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            font-size: 1.5em;
            font-weight: 700;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .explore-button:hover {
            /* Using a CSS filter to darken the color slightly on hover */
            filter: brightness(90%); 
            transform: translateY(-3px); /* Subtle lift effect */
            color: var(--white); /* Ensure text stays white */
        }

        .explore-button i {
            margin-left: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) { /* Bootstrap's default breakpoint for this navbar type */
            .navbar-brand {
                font-size: 1.8em;
            }
            .intro-text h3 {
                font-size: 1.8em;
            }
            .intro-text p {
                font-size: 1em;
            }
            .explore-button {
                font-size: 1.2em;
                padding: 12px 25px;
            }
        }
        
        .intro h6 {
            color: #FF4500;
            font-size: 1em; /* Smaller h6 */
            margin-bottom: 10px; /* Reduce space below heading */
        }
        .intro p {
            margin-bottom: 10px; /* Reduced further */
            font-size: 0.9em; /* Smaller paragraph text */
        }
        /* Other styles for btn-logout remain unchanged */
        .btn-logout {
            background: #FF4500;
            color: white;
            border: none;
            padding: 8px 18px; /* Smaller button padding */
            border-radius: 6px; /* Smaller button border-radius */
            text-decoration: none;
            font-size: 0.9em; /* Smaller button text */
        }
        .btn-logout:hover {
            background: #e63e00;
        }
    </style>
</head>
<body>

    <?php
    include('navbar.html');
    ?>
    <div class="intro">
        <h6>Welcome to Hisia Pixels!</h6>
        <p>You are logged in as: **<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Guest'; ?>**</p>
        
        
    </div>
    
    <header class="hero-section">
        <div class="intro-text">
            <h3><b>{"You don't take a Photograph"} <br /> {"You make it"}</b></h3>
            <p>~Ansel Adams~</p>
        </div>
        <a href="explore.html" class="explore-button">
            Explore Now <i class="fas fa-arrow-right"></i>
        </a>
    </header>

    <!-- Fixed Bootstrap JavaScript CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
