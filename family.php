<?php
require_once 'firebase_helper.php';

// Include the session manager script
include 'session_manager.php';

// You can now access $_SESSION variables here
$loggedInUserEmail = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
$isLoggedIn = isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid']);

// Initialize Firebase helper
$firebase = new FirebaseHelper();

// Get family photos from Firebase (will use mock data if Firebase is not accessible)
$familyPhotos = [];
$error = null;
$loading = false;
$usingMockData = false;

try {
    $familyPhotos = $firebase->getCollection('family', 'uploadedAt', 'desc');
    
    // Check if we're using mock data
    if (!empty($familyPhotos) && isset($familyPhotos[0]['id']) && strpos($familyPhotos[0]['id'], 'mock_') === 0) {
        $usingMockData = true;
    }
    
} catch (Exception $e) {
    $error = 'Failed to load family photos: ' . $e->getMessage();
}

// Handle modal view
$selectedPhoto = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $photoId = $_GET['view'];
    $selectedPhoto = $firebase->getDocument('family', $photoId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Gallery - Hisia Pixels</title>
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
            --off-white: #f8f9fa;
            --gradient-start: #333333;
            --gradient-end: #1a1a1a;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom right, var(--gradient-start), var(--gradient-end));
            overflow-x: hidden;
            color: var(--off-white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            transition: background-color 0.3s ease;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-size: 2.2em;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
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
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9em;
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

        .family-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .family-card {
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            text-decoration: none;
            color: inherit;
        }

        .family-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0px 15px 30px rgba(255, 69, 0, 0.3);
            color: inherit;
            text-decoration: none;
        }

        .family-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .family-card:hover .family-image {
            transform: scale(1.05);
        }

        .family-info {
            padding: 20px;
        }

        .family-title {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .family-meta {
            color: var(--light-gray);
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .family-date {
            color: var(--light-gray);
            font-size: 0.8em;
            opacity: 0.7;
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

        .no-photos {
            text-align: center;
            padding: 80px 20px;
            color: var(--light-gray);
        }

        .no-photos i {
            font-size: 4em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .no-photos h3 {
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
            
            .family-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .family-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <?php include('navbar.html'); ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-users"></i> Family Gallery</h1>
            <p>Beautiful family moments captured forever (<?php echo count($familyPhotos); ?> photos)</p>
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
        <?php elseif (empty($familyPhotos)): ?>
            <div class="no-photos">
                <i class="fas fa-users"></i>
                <h3>No Family Photos Found</h3>
                <p>There are currently no family photos in the collection.</p>
                <?php if ($isLoggedIn): ?>
                    <p class="text-muted">Upload some family photos through the admin panel to see them here.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="family-grid">
                <?php foreach ($familyPhotos as $photo): ?>
                    <a href="?view=<?php echo urlencode($photo['id']); ?>" class="family-card">
                        <img src="<?php echo htmlspecialchars($photo['downloadURL'] ?? '/placeholder.svg?height=250&width=300'); ?>" 
                             alt="<?php echo htmlspecialchars($photo['name'] ?? 'Family Photo'); ?>" 
                             class="family-image" 
                             loading="lazy">
                        <div class="family-info">
                            <div class="family-title"><?php echo htmlspecialchars($photo['name'] ?? 'Untitled'); ?></div>
                            <div class="family-meta">
                                <i class="fas fa-file"></i> <?php echo htmlspecialchars($photo['originalName'] ?? 'Unknown file'); ?>
                            </div>
                            <?php if (isset($photo['fileSize'])): ?>
                                <div class="family-meta">
                                    <i class="fas fa-weight"></i> <?php echo $firebase->formatFileSize($photo['fileSize']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($photo['uploadedBy'])): ?>
                                <div class="family-meta">
                                    <i class="fas fa-user"></i> By <?php echo htmlspecialchars($photo['uploadedBy']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($photo['uploadedAt'])): ?>
                                <div class="family-date">
                                    <i class="fas fa-calendar"></i> <?php echo $firebase->formatDate($photo['uploadedAt']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Family Photo Modal -->
    <?php if ($selectedPhoto): ?>
        <div class="modal fade show" id="familyModal" tabindex="-1" aria-labelledby="familyModalLabel" style="display: block;" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="familyModalLabel"><?php echo htmlspecialchars($selectedPhoto['name'] ?? 'Family Photo Details'); ?></h5>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-close" aria-label="Close"></a>
                    </div>
                    <div class="modal-body">
                        <img src="<?php echo htmlspecialchars($selectedPhoto['downloadURL'] ?? '/placeholder.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($selectedPhoto['name'] ?? 'Family Photo'); ?>" 
                             class="modal-image">
                        <div class="mt-3">
                            <h4 class="text-primary"><?php echo htmlspecialchars($selectedPhoto['name'] ?? 'Untitled'); ?></h4>
                            <p class="text-muted">
                                <strong>Original Name:</strong> <?php echo htmlspecialchars($selectedPhoto['originalName'] ?? 'Unknown'); ?><br>
                                <?php if (isset($selectedPhoto['fileSize'])): ?>
                                    <strong>File Size:</strong> <?php echo $firebase->formatFileSize($selectedPhoto['fileSize']); ?><br>
                                <?php endif; ?>
                                <?php if (isset($selectedPhoto['uploadedBy'])): ?>
                                    <strong>Uploaded By:</strong> <?php echo htmlspecialchars($selectedPhoto['uploadedBy']); ?>
                                <?php endif; ?>
                            </p>
                            <?php if (isset($selectedPhoto['uploadedAt'])): ?>
                                <p class="text-muted small">Uploaded on <?php echo $firebase->formatDate($selectedPhoto['uploadedAt']); ?></p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Minimal JavaScript for modal handling -->
    <script>
        // Handle modal close with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            }
        });
        
        // Handle modal backdrop click
        <?php if ($selectedPhoto): ?>
            document.querySelector('.modal-backdrop').addEventListener('click', function() {
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            });
        <?php endif; ?>
    </script>
</body>
</html>
