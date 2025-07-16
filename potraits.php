<?php
require_once 'firebase_helper.php';

// Initialize Firebase helper
$firebase = new FirebaseHelper();

// Get portraits from Firebase (will use mock data if Firebase is not accessible)
$portraits = [];
$error = null;
$loading = false;
$usingMockData = false;

try {
    $portraits = $firebase->getCollection('portraits', 'uploadedAt', 'desc');
    
    // Check if we're using mock data
    if (!empty($portraits) && isset($portraits[0]['id']) && strpos($portraits[0]['id'], 'mock_') === 0) {
        $usingMockData = true;
    }
    
} catch (Exception $e) {
    $error = 'Failed to load portraits: ' . $e->getMessage();
}

// Handle modal view
$selectedPortrait = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $portraitId = $_GET['view'];
    $selectedPortrait = $firebase->getDocument('portraits', $portraitId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portraits Gallery - Hisia Pixels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            padding: 5px 0;
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-size: 2.2em;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
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

        .portraits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .portrait-card {
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.4);
            text-decoration: none;
            color: inherit;
        }

        .portrait-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0px 15px 30px rgba(255, 69, 0, 0.3);
            color: inherit;
            text-decoration: none;
        }

        .portrait-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .portrait-card:hover .portrait-image {
            transform: scale(1.05);
        }

        .portrait-info {
            padding: 20px;
        }

        .portrait-title {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .portrait-meta {
            color: var(--light-gray);
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .portrait-date {
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
            
            .portraits-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .portrait-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <?php include('navbar.html'); ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-portrait"></i> Portraits Gallery</h1>
            <p>Professional portrait photography collection (<?php echo count($portraits); ?> portraits)</p>
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
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($portraits)): ?>
            <div class="text-center py-5">
                <i class="fas fa-image" style="font-size: 4em; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h3>No Portraits Found</h3>
                <p>There are currently no portraits in the collection.</p>
            </div>
        <?php else: ?>
            <div class="portraits-grid">
                <?php foreach ($portraits as $portrait): ?>
                    <a href="?view=<?php echo urlencode($portrait['id']); ?>" class="portrait-card">
                        <img src="<?php echo htmlspecialchars($portrait['downloadURL'] ?? '/placeholder.svg?height=250&width=300'); ?>" 
                             alt="<?php echo htmlspecialchars($portrait['name'] ?? 'Portrait'); ?>" 
                             class="portrait-image" 
                             loading="lazy">
                        <div class="portrait-info">
                            <div class="portrait-title"><?php echo htmlspecialchars($portrait['name'] ?? 'Untitled'); ?></div>
                            <div class="portrait-meta">
                                <i class="fas fa-file"></i> <?php echo htmlspecialchars($portrait['originalName'] ?? 'Unknown file'); ?>
                            </div>
                            <?php if (isset($portrait['fileSize'])): ?>
                                <div class="portrait-meta">
                                    <i class="fas fa-weight"></i> <?php echo $firebase->formatFileSize($portrait['fileSize']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($portrait['uploadedBy'])): ?>
                                <div class="portrait-meta">
                                    <i class="fas fa-user"></i> By <?php echo htmlspecialchars($portrait['uploadedBy']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($portrait['uploadedAt'])): ?>
                                <div class="portrait-date">
                                    <i class="fas fa-calendar"></i> <?php echo $firebase->formatDate($portrait['uploadedAt']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; Hisia Pixels 2024 - Professional Photography
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
