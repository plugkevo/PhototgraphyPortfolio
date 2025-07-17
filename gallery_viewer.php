<?php
require_once 'firebase_helper.php';

$category = $_GET['category'] ?? '';
$mediaItems = [];
$galleryTitle = 'Gallery';
$galleryDescription = 'View media from your collections.';

if (!empty($category)) {
    $firebaseHelper = new FirebaseHelper();

    // Use the helper function to get the correct Firestore collection path
    $collectionPath = $firebaseHelper->getFirestoreCollectionPath($category);
    $mediaItems = $firebaseHelper->getCollection($collectionPath);

    // Determine title and description based on the category
    // You might want to fetch metadata for custom collections if available
    $customCollectionMetadata = $firebaseHelper->getDocument('premium_content', $category);

    if ($customCollectionMetadata && isset($customCollectionMetadata['description'])) {
        $galleryTitle = ucfirst(str_replace('_', ' ', $category)) . ' (Premium)';
        $galleryDescription = htmlspecialchars($customCollectionMetadata['description']);
    } else {
        // Fallback for predefined or other top-level collections
        $galleryTitle = ucfirst(str_replace('_', ' ', $category)) . ' Gallery';
        switch ($category) {
            case 'portraits':
                $galleryDescription = 'Capturing the essence of individuals.';
                break;
            case 'family':
                $galleryDescription = 'Cherished moments with your loved ones.';
                break;
            case 'headshots':
                $galleryDescription = 'Professional and impactful headshots.';
                break;
            case 'videos':
                $galleryDescription = 'Dynamic stories in motion.';
                break;
            default:
                $galleryDescription = 'View media from this collection.';
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($galleryTitle); ?> - Hisia Pixels</title>
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
        .gallery-header {
            background: linear-gradient(135deg, var(--primary-color), #ff6b35);
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .gallery-header h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        .gallery-item img, .gallery-item video {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover img, .gallery-item:hover video {
            transform: scale(1.05);
        }
        .gallery-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: var(--white);
            padding: 15px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .gallery-item:hover .gallery-info {
            transform: translateY(0);
        }
        .gallery-info h5 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        .gallery-info p {
            font-size: 0.9em;
            margin: 0;
            color: var(--light-gray);
        }
        .no-media-message {
            padding: 50px;
            text-align: center;
            color: var(--light-gray);
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-camera"></i> Hisia Pixels
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="portraits.php">Portraits</a></li>
                    <li class="nav-item"><a class="nav-link" href="family.php">Family</a></li>
                    <li class="nav-item"><a class="nav-link" href="headshots.php">Headshots</a></li>
                    <li class="nav-item"><a class="nav-link" href="videos.php">Videos</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="gallery-header">
        <div class="container text-center">
            <h1><i class="fas fa-images"></i> <?php echo htmlspecialchars($galleryTitle); ?></h1>
            <p><?php echo htmlspecialchars($galleryDescription); ?></p>
        </div>
    </div>

    <div class="container">
        <?php if (empty($mediaItems)): ?>
            <div class="no-media-message">
                <p>No media available for this collection yet. Please check back later!</p>
                <p>If you are an admin, you can add media from the <a href="admin_dashboard_firebase.php" class="text-primary">Admin Dashboard</a>.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($mediaItems as $item): ?>
                    <div class="col-md-4">
                        <div class="gallery-item">
                            <?php if (str_starts_with($item['fileType'], 'image/')): ?>
                                <img src="<?php echo htmlspecialchars($item['downloadURL']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php elseif (str_starts_with($item['fileType'], 'video/')): ?>
                                <video controls src="<?php echo htmlspecialchars($item['downloadURL']); ?>" poster="<?php echo htmlspecialchars($item['thumbnailURL'] ?? '/placeholder.svg?height=250&width=400'); ?>"></video>
                            <?php else: ?>
                                <div class="w-100 h-250px bg-secondary d-flex align-items-center justify-content-center text-white">
                                    Unsupported File Type
                                </div>
                            <?php endif; ?>
                            <div class="gallery-info">
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p>Uploaded: <?php echo htmlspecialchars($firebaseHelper->formatDate($item['uploadedAt'])); ?></p>
                                <p>Size: <?php echo htmlspecialchars($firebaseHelper->formatFileSize($item['fileSize'])); ?></p>
                                <?php if (str_starts_with($item['fileType'], 'video/') && isset($item['duration'])): ?>
                                    <p>Duration: <?php echo htmlspecialchars($item['duration']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
