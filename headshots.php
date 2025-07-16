<?php
require_once 'firebase_helper.php';

// Include the session manager script
include 'session_manager.php';

// You can now access $_SESSION variables here
$loggedInUserEmail = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
$isLoggedIn = isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid']);

// Initialize Firebase helper
$firebase = new FirebaseHelper();

// Get headshots from Firebase (will use mock data if Firebase is not accessible)
$headshots = [];
$error = null;
$loading = false;
$usingMockData = false;

try {
    $headshots = $firebase->getCollection('headshots', 'uploadedAt', 'desc');
    
    // Check if we're using mock data
    if (!empty($headshots) && isset($headshots[0]['id']) && strpos($headshots[0]['id'], 'mock_') === 0) {
        $usingMockData = true;
    }
    
} catch (Exception $e) {
    $error = 'Failed to load headshots: ' . $e->getMessage();
}

// Handle modal view
$selectedHeadshot = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $headshotId = $_GET['view'];
    $selectedHeadshot = $firebase->getDocument('headshots', $headshotId);
}
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
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #ff6b35);
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
        }

        .page-header p {
            font-size: 1.3em;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .container {
            flex: 1;
            padding-bottom: 40px;
        }

        .mock-data-notice {
            background-color: rgba(255, 193, 7, 0.2);
            border: 1px solid #ffc107;
            color: #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .headshots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .headshot-card {
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .headshot-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0px 15px 30px rgba(255, 69, 0, 0.3);
            color: inherit;
            text-decoration: none;
        }

        .headshot-image {
            width: 100%;
            height: 320px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .headshot-card:hover .headshot-image {
            transform: scale(1.05);
        }

        .headshot-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.8) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .headshot-card:hover .headshot-overlay {
            opacity: 1;
        }

        .headshot-info {
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .headshot-title {
            font-size: 1.3em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .headshot-meta {
            color: var(--light-gray);
            font-size: 0.85em;
            margin-bottom: 4px;
        }

        .headshot-date {
            color: var(--light-gray);
            font-size: 0.75em;
            opacity: 0.7;
        }

        .professional-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: var(--white);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .back-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 30px;
        }

        .back-btn:hover {
            background: #e63e00;
            transform: translateY(-2px);
            color: var(--white);
        }

        .setup-btn {
            background: #ffc107;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-left: 10px;
        }

        .setup-btn:hover {
            background: #e0a800;
            color: #000;
        }

        .error-message {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .no-headshots {
            text-align: center;
            padding: 80px 20px;
            color: var(--light-gray);
        }

        .no-headshots i {
            font-size: 4em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .no-headshots h3 {
            font-size: 2em;
            margin-bottom: 15px;
        }

        /* Modal Styles */
        .modal-content {
            background-color: var(--dark-color);
            border: 1px solid var(--primary-color);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            color: var(--primary-color);
        }

        .modal-body {
            text-align: center;
        }

        .modal-image {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .btn-close {
            filter: invert(1);
        }

        footer {
            background-color: var(--dark-color);
            color: var(--light-gray);
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
            font-size: 0.9em;
            border-top: 1px solid rgba(255, 69, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2.5em;
            }
            
            .headshots-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .headshot-image {
                height: 280px;
            }
        }

        
    </style>
</head>
<body>

    <?php
    include('navbar.html');
    ?>
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-user-tie"></i> Professional Headshots</h1>
            <p>Corporate and professional headshot photography (<?php echo count($headshots); ?> headshots)</p>
        </div>
    </div>

    <div class="container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Gallery
        </a>

        <?php if ($usingMockData): ?>
            <div class="mock-data-notice">
                <i class="fas fa-info-circle"></i>
                <strong>Demo Mode:</strong> Showing sample data. Firebase connection is not configured yet.
                <a href="firebase_setup_guide.php" class="setup-btn">
                    <i class="fas fa-cog"></i> Setup Firebase
                </a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($headshots)): ?>
            <div class="no-headshots">
                <i class="fas fa-user-tie"></i>
                <h3>No Headshots Found</h3>
                <p>There are currently no professional headshots in the collection.</p>
                <?php if ($isLoggedIn): ?>
                    <p class="text-muted">Upload some headshots through the admin panel to see them here.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="headshots-grid">
                <?php foreach ($headshots as $headshot): ?>
                    <a href="?view=<?php echo urlencode($headshot['id']); ?>" class="headshot-card">
                        <div class="professional-badge">Professional</div>
                        <img src="<?php echo htmlspecialchars($headshot['downloadURL'] ?? '/placeholder.svg?height=320&width=280'); ?>" 
                             alt="<?php echo htmlspecialchars($headshot['name'] ?? 'Professional Headshot'); ?>" 
                             class="headshot-image" 
                             loading="lazy">
                        <div class="headshot-overlay"></div>
                        <div class="headshot-info">
                            <div class="headshot-title"><?php echo htmlspecialchars($headshot['name'] ?? 'Professional Headshot'); ?></div>
                            <div class="headshot-meta">
                                <i class="fas fa-file"></i> <?php echo htmlspecialchars($headshot['originalName'] ?? 'Unknown file'); ?>
                            </div>
                            <?php if (isset($headshot['fileSize'])): ?>
                                <div class="headshot-meta">
                                    <i class="fas fa-weight"></i> <?php echo $firebase->formatFileSize($headshot['fileSize']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($headshot['uploadedBy'])): ?>
                                <div class="headshot-meta">
                                    <i class="fas fa-camera"></i> By <?php echo htmlspecialchars($headshot['uploadedBy']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($headshot['uploadedAt'])): ?>
                                <div class="headshot-date">
                                    <i class="fas fa-calendar"></i> <?php echo $firebase->formatDate($headshot['uploadedAt']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Headshot Modal -->
    <?php if ($selectedHeadshot): ?>
        <div class="modal fade show" id="headshotModal" tabindex="-1" aria-labelledby="headshotModalLabel" style="display: block;" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="headshotModalLabel"><?php echo htmlspecialchars($selectedHeadshot['name'] ?? 'Professional Headshot'); ?></h5>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-close" aria-label="Close"></a>
                    </div>
                    <div class="modal-body">
                        <img src="<?php echo htmlspecialchars($selectedHeadshot['downloadURL'] ?? '/placeholder.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($selectedHeadshot['name'] ?? 'Professional Headshot'); ?>" 
                             class="modal-image">
                        <div class="mt-3">
                            <h4 class="text-primary"><?php echo htmlspecialchars($selectedHeadshot['name'] ?? 'Professional Headshot'); ?></h4>
                            <p class="text-muted">
                                <strong>Original Name:</strong> <?php echo htmlspecialchars($selectedHeadshot['originalName'] ?? 'Unknown'); ?><br>
                                <?php if (isset($selectedHeadshot['fileSize'])): ?>
                                    <strong>File Size:</strong> <?php echo $firebase->formatFileSize($selectedHeadshot['fileSize']); ?><br>
                                <?php endif; ?>
                                <?php if (isset($selectedHeadshot['uploadedBy'])): ?>
                                    <strong>Photographer:</strong> <?php echo htmlspecialchars($selectedHeadshot['uploadedBy']); ?><br>
                                <?php endif; ?>
                                <strong>Category:</strong> Professional Headshot
                            </p>
                            <?php if (isset($selectedHeadshot['uploadedAt'])): ?>
                                <p class="text-muted small">Captured on <?php echo $firebase->formatDate($selectedHeadshot['uploadedAt']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <footer>
        &copy; Hisia Pixels 2024 - Professional Photography
    </footer>

    

    <!-- Fixed Bootstrap JavaScript CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
