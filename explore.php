
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Gallery</title>
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
            --off-white: #f8f9fa; /* A slightly off-white for better contrast */
            --gradient-start: #333333; /* Darker shade for gradient */
            --gradient-end: #1a1a1a; /* Even darker shade for gradient */
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom right, var(--gradient-start), var(--gradient-end)); /* Subtle gradient background */
            overflow-x: hidden;
            color: var(--off-white); /* Use off-white for better readability */
            min-height: 100vh; /* Ensure body takes full viewport height */
            display: flex;
            flex-direction: column;
        }

       .navbar {
            background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent black for a sleek look */
            transition: background-color 0.3s ease;
            position: sticky;
            top: 0;
            z-index: 1000; /* Higher z-index */
            padding: 5px 0; /* Add padding for better spacing */
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

        .btn-logout {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 10px 22px; /* Slightly larger button */
            border-radius: 8px; /* More rounded corners */
            text-decoration: none;
            font-size: 1em; /* Slightly larger text */
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-logout:hover {
            background: #e63e00; /* Darker shade on hover */
            transform: translateY(-2px); /* Lift on hover */
        }

        /* Main Content Styling */
        .container {
            flex: 1; /* Allow container to grow and take available space */
            padding-top: 40px; /* Add more padding at the top */
            padding-bottom: 40px; /* Add padding at the bottom */
        }

        .row {
            display: flex;
            flex-wrap: wrap; /* Allow items to wrap to the next line */
            justify-content: center; /* Center items in the row */
            gap: 30px; /* Increase gap between media cards */
        }

        .media {
            text-align: center;
            margin: 0;
            padding: 30px 15px; /* More padding inside the card */
            border-radius: 12px; /* More rounded corners for cards */
            cursor: pointer;
            background-color: rgba(30, 30, 30, 0.7); /* Slightly lighter dark background with transparency */
            transition: transform 0.5s ease, background-color 0.5s ease, box-shadow 0.5s ease;
            height: 380px; /* Adjust height for better consistency */
            width: 320px; /* Adjust width for better consistency */
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4); /* Stronger shadow for depth */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribute space evenly */
        }

        .media img,
        .media video {
            height: 220px; /* Slightly larger media previews */
            width: 100%; /* Ensure media fills its container */
            object-fit: cover; /* Cover the area, cropping if necessary */
            border-radius: 8px; /* Rounded corners for media */
            margin-bottom: 15px; /* Space between media and title */
        }

        .media:hover {
            background-color: var(--dark-color); /* Darker on hover */
            color: var(--white);
            transform: scale(1.03) translateY(-5px); /* Lift and scale slightly on hover */
            box-shadow: 0px 12px 25px rgba(0, 0, 0, 0.6); /* Even stronger shadow on hover */
        }

        .media h3 {
            color: var(--primary-color);
            margin-bottom: 10px; /* Space below heading */
            font-size: 1.8em; /* Larger heading */
            font-weight: 700;
        }

        .media .fa-2x {
            font-size: 2.5em !important; /* Larger icons */
            color: var(--light-gray);
            transition: color 0.3s ease;
        }

        .media:hover .fa-2x {
            color: var(--primary-color); /* Icon color changes on hover */
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:hover {
            color: inherit;
        }

        footer {
            background-color: var(--dark-color);
            color: var(--light-gray);
            text-align: center;
            padding: 20px 0;
            margin-top: auto; /* Push footer to the bottom */
            font-size: 0.9em;
            border-top: 1px solid rgba(255, 69, 0, 0.2); /* Subtle border top */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 2em;
            }
            .navbar-nav .nav-link {
                margin-right: 0;
            }
            .row {
                gap: 20px;
            }
            .media {
                width: 90%; /* Make cards wider on smaller screens */
                max-width: 350px; /* Limit max width */
                height: auto; /* Auto height for better content fitting */
            }
            .media img,
            .media video {
                height: 180px; /* Adjust media height for smaller screens */
            }
        }
    </style>
</head>
<body>
    <?php
        include('navbar.html');
    ?>
    <div class="container">
        <div class="row">
            <div class="media">
                <a href="potraits.php">
                    <img src="images/pexels-michael-block-3225528.jpg" alt="Portraits">
                    <h3><b><i>Portraits</i></b></h3>
                    <i class="fa-solid fa-image fa-2x"></i>
                </a>
            </div>
            <div class="media">
                <a href="family.php">
                    <img src="images/pexels-michael-block-3225528.jpg" alt="Family">
                    <h3><b><i>Family</i></b></h3>
                    <i class="fa-solid fa-image fa-2x"></i>
                </a>
            </div>
            <div class="media">
                <a href="headshots.php">
                    <img src="images/pexels-michael-block-3225528.jpg" alt="Headshots">
                    <h3><b><i>Headshots</i></b></h3>
                    <i class="fa-solid fa-image fa-2x"></i>
                </a>
            </div>
            <div class="media">
                <a href="videos.php">
                    <video src="videos/Tropical Video Background - No Ads(720P_HD).mp4" autoplay loop muted></video> <h3><b><i>Videos</i></b></h3>
                    <i class="fa-solid fa-video fa-2x"></i>
                </a>
            </div>
        </div>
    </div>
    <footer>
        &copy; Company 2023
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>