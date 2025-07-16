<?php
require_once 'firebase_helper.php';

// Include the session manager script
include 'session_manager.php';

// You can now access $_SESSION variables here
$loggedInUserEmail = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
$isLoggedIn = isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid']);

// Initialize Firebase helper
$firebase = new FirebaseHelper();

// Get videos from Firebase (will use mock data if Firebase is not accessible)
$videos = [];
$error = null;
$loading = false;
$usingMockData = false;

try {
    $videos = $firebase->getCollection('videos', 'uploadedAt', 'desc');
    
    // Check if we're using mock data
    if (!empty($videos) && isset($videos[0]['id']) && strpos($videos[0]['id'], 'mock_') === 0) {
        $usingMockData = true;
    }
    
} catch (Exception $e) {
    $error = 'Failed to load videos: ' . $e->getMessage();
}

// Handle modal view
$selectedVideo = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $videoId = $_GET['view'];
    $selectedVideo = $firebase->getDocument('videos', $videoId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Gallery - Hisia Pixels</title>
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
            --video-accent: #ff6b35;
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
            background: var(--primary-color);
            color: var(--white);
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
            background: linear-gradient(135deg, var(--primary-color), var(--video-accent));
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M30 30l15-15v30l-15-15zm-15 0l15 15V15l-15 15z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.1;
        }

        .page-header h1 {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
            position: relative;
            z-index: 2;
        }

        .page-header p {
            font-size: 1.3em;
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
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

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .video-card {
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .video-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0px 15px 30px rgba(255, 69, 0, 0.3);
            color: inherit;
            text-decoration: none;
        }

        .video-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
            position: relative;
        }

        .video-card:hover .video-thumbnail {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 69, 0, 0.9);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .play-overlay i {
            color: var(--white);
            font-size: 1.5em;
            margin-left: 3px; /* Slight offset for play icon */
        }

        .video-card:hover .play-overlay {
            transform: translate(-50%, -50%) scale(1.1);
            background: rgba(255, 69, 0, 1);
        }

        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .video-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--video-accent);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .video-info {
            padding: 20px;
        }

        .video-title {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .video-meta {
            color: var(--light-gray);
            font-size: 0.9em;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .video-date {
            color: var(--light-gray);
            font-size: 0.8em;
            opacity: 0.7;
        }

        .video-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .video-quality {
            background: rgba(255, 69, 0, 0.2);
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7em;
            font-weight: 600;
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

        .no-videos {
            text-align: center;
            padding: 80px 20px;
            color: var(--light-gray);
        }

        .no-videos i {
            font-size: 4em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .no-videos h3 {
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

        .modal-video {
            width: 100%;
            max-height: 60vh;
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
            
            .videos-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
            
            .video-thumbnail {
                height: 180px;
            }

            .play-overlay {
                width: 50px;
                height: 50px;
            }

            .play-overlay i {
                font-size: 1.2em;
            }
        }

        .video-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
            border: 2px dashed rgba(255, 69, 0, 0.3);
        }

        .video-thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Ensure video thumbnails don't show controls */
        .video-thumbnail::-webkit-media-controls {
            display: none !important;
        }

        .video-thumbnail::-webkit-media-controls-panel {
            display: none !important;
        }
    </style>
</head>
<body>
    <?php include('navbar.html'); ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-video"></i> Video Gallery</h1>
            <p>Professional videography and cinematic storytelling (<?php echo count($videos); ?> videos)</p>
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
        <?php elseif (empty($videos)): ?>
            <div class="no-videos">
                <i class="fas fa-video"></i>
                <h3>No Videos Found</h3>
                <p>There are currently no videos in the collection.</p>
                <?php if ($isLoggedIn): ?>
                    <p class="text-muted">Upload some videos through the admin panel to see them here.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="videos-grid">
                <?php foreach ($videos as $video): ?>
                    <a href="?view=<?php echo urlencode($video['id']); ?>" class="video-card">
                        <div style="position: relative;">
                            <div class="video-type-badge"><?php echo ucfirst($video['videoType'] ?? 'Video'); ?></div>
                            
                            <!-- Replace img tag with video tag -->
                            <?php if (isset($video['downloadURL']) && $video['downloadURL'] !== '/placeholder.svg'): ?>
                                <video class="video-thumbnail" 
                                       poster="<?php echo htmlspecialchars($video['thumbnailURL'] ?? '/placeholder.svg?height=200&width=350'); ?>"
                                       preload="metadata"
                                       muted>
                                    <source src="<?php echo htmlspecialchars($video['downloadURL']); ?>" 
                                            type="<?php echo htmlspecialchars($video['fileType'] ?? 'video/mp4'); ?>">
                                    <!-- Fallback image if video fails to load -->
                                    <img src="<?php echo htmlspecialchars($video['thumbnailURL'] ?? '/placeholder.svg?height=200&width=350'); ?>" 
                                         alt="<?php echo htmlspecialchars($video['name'] ?? 'Video Thumbnail'); ?>" 
                                         class="video-thumbnail">
                                </video>
                            <?php else: ?>
                                <!-- For demo/placeholder videos, show a video-style placeholder -->
                                <div class="video-thumbnail video-placeholder">
                                    <i class="fas fa-video" style="font-size: 3em; color: rgba(255,255,255,0.3);"></i>
                                    <div style="margin-top: 10px; font-size: 0.9em; color: rgba(255,255,255,0.5);">Demo Video</div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="video-duration"><?php echo $video['duration'] ?? '0:00'; ?></div>
                        </div>
                        <!-- Rest of the video info remains the same -->
                        <div class="video-info">
                            <div class="video-title"><?php echo htmlspecialchars($video['name'] ?? 'Untitled Video'); ?></div>
                            <div class="video-meta">
                                <i class="fas fa-file-video"></i>
                                <span><?php echo htmlspecialchars($video['originalName'] ?? 'Unknown file'); ?></span>
                            </div>
                            <?php if (isset($video['fileSize'])): ?>
                                <div class="video-meta">
                                    <i class="fas fa-hdd"></i>
                                    <span><?php echo $firebase->formatFileSize($video['fileSize']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($video['uploadedBy'])): ?>
                                <div class="video-meta">
                                    <i class="fas fa-video"></i>
                                    <span>By <?php echo htmlspecialchars($video['uploadedBy']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="video-stats">
                                <div class="video-date">
                                    <?php if (isset($video['uploadedAt'])): ?>
                                        <i class="fas fa-calendar"></i> <?php echo $firebase->formatDate($video['uploadedAt']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="video-quality"><?php echo $video['quality'] ?? 'HD'; ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Video Modal -->
    <?php if ($selectedVideo): ?>
        <div class="modal fade show" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" style="display: block;" aria-modal="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="videoModalLabel"><?php echo htmlspecialchars($selectedVideo['name'] ?? 'Video Player'); ?></h5>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-close" aria-label="Close"></a>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($selectedVideo['downloadURL']) && $selectedVideo['downloadURL'] !== '/placeholder.svg'): ?>
                            <video controls class="modal-video" poster="<?php echo htmlspecialchars($selectedVideo['thumbnailURL'] ?? ''); ?>">
                                <source src="<?php echo htmlspecialchars($selectedVideo['downloadURL']); ?>" type="<?php echo htmlspecialchars($selectedVideo['fileType'] ?? 'video/mp4'); ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="fas fa-video" style="font-size: 4em; color: var(--primary-color); margin-bottom: 20px;"></i>
                                <h4>Video Preview</h4>
                                <p class="text-muted">This is a demo video placeholder. In production, the actual video would play here.</p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <h4 class="text-primary"><?php echo htmlspecialchars($selectedVideo['name'] ?? 'Untitled Video'); ?></h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted">
                                        <strong>Original Name:</strong> <?php echo htmlspecialchars($selectedVideo['originalName'] ?? 'Unknown'); ?><br>
                                        <strong>Duration:</strong> <?php echo $selectedVideo['duration'] ?? 'Unknown'; ?><br>
                                        <?php if (isset($selectedVideo['fileSize'])): ?>
                                            <strong>File Size:</strong> <?php echo $firebase->formatFileSize($selectedVideo['fileSize']); ?><br>
                                        <?php endif; ?>
                                        <strong>Quality:</strong> <?php echo $selectedVideo['quality'] ?? 'HD'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted">
                                        <strong>Type:</strong> <?php echo ucfirst($selectedVideo['videoType'] ?? 'Video'); ?><br>
                                        <?php if (isset($selectedVideo['uploadedBy'])): ?>
                                            <strong>Videographer:</strong> <?php echo htmlspecialchars($selectedVideo['uploadedBy']); ?><br>
                                        <?php endif; ?>
                                        <strong>Category:</strong> Videos<br>
                                        <?php if (isset($selectedVideo['uploadedAt'])): ?>
                                            <strong>Created:</strong> <?php echo $firebase->formatDate($selectedVideo['uploadedAt']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <footer>
        &copy; Hisia Pixels 2024 - Professional Photography & Videography
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- JavaScript for video modal and controls -->
    <script>
        // Handle modal close with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            }
        });
        
        // Handle modal backdrop click
        <?php if ($selectedVideo): ?>
            document.querySelector('.modal-backdrop').addEventListener('click', function() {
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            });
            
            // Pause video when modal is closed
            window.addEventListener('beforeunload', function() {
                const video = document.querySelector('.modal-video');
                if (video) {
                    video.pause();
                }
            });
        <?php endif; ?>

        // Add video hover preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const videoCards = document.querySelectorAll('.video-card');
            
            videoCards.forEach(card => {
                const video = card.querySelector('video.video-thumbnail');
                
                if (video) {
                    // Play video preview on hover
                    card.addEventListener('mouseenter', function() {
                        video.currentTime = 0;
                        video.play().catch(e => {
                            // Silently handle autoplay restrictions
                            console.log('Video preview autoplay prevented');
                        });
                    });
                    
                    // Pause video when not hovering
                    card.addEventListener('mouseleave', function() {
                        video.pause();
                        video.currentTime = 0;
                    });
                }
                
                // Loading state for video modal
                card.addEventListener('click', function(e) {
                    const playOverlay = card.querySelector('.play-overlay');
                    if (playOverlay) {
                        playOverlay.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    }
                });
            });
        });
    </script>
</body>
</html>
