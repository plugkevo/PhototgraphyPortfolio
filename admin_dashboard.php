<?php
session_start();
require_once 'firebase_config.php';
require_once 'firebase_helper.php'; // Include the helper

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

$admin_username = $_SESSION['admin_username'] ?? 'unknown';
$uploadMessage = '';
$uploadMessageType = 'success';
$firebaseData = null;

// Initialize Firebase helper
$firebaseHelper = new FirebaseHelper();
$stats = $firebaseHelper->getAllStats(); // Get all stats including collection details

// Handle form submission for media upload
if (isset($_POST['submit_media'])) {
    $mediaName = $_POST['mediaName'] ?? '';
    $mediaCategory = $_POST['mediaCategory'] ?? '';

    if (isset($_FILES['mediaFile']) && $_FILES['mediaFile']['error'] === UPLOAD_ERR_OK) {
        // Prepare file for Firebase upload
        $file = $_FILES['mediaFile'];
        $fileName = time() . '_' . $file['name'];
        $storagePath = 'media/' . $mediaCategory . '/' . $fileName;

        // Prepare data for Firebase
        $mediaData = [
            'name' => $mediaName,
            'category' => $mediaCategory,
            'fileName' => $fileName,
            'originalName' => $file['name'],
            'fileType' => $file['type'],
            'fileSize' => $file['size'],
            'ownerName' => $admin_username, // Changed from uploadedBy
            'createdAt' => date('c'), // Changed from uploadedAt
            'status' => 'active'
        ];

        // Prepare for JavaScript upload
        $firebaseData = [
            'file' => [
                'data' => base64_encode(file_get_contents($file['tmp_name'])),
                'name' => $fileName,
                'type' => $file['type'],
                'path' => $storagePath
            ],
            'document' => $mediaData,
            'collection' => 'media'
        ];

        $uploadMessage = 'Ready to upload to Firebase...';
        $uploadMessageType = 'info';
    } else {
        $uploadMessage = 'Please select a valid file.';
        $uploadMessageType = 'danger';
    }
}
$firebaseConfig = FirebaseConfig::getConfig();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Hisia Pixels</title>
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

        .admin-actions, .collection-management {
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

        .modal-content {
            background-color: var(--dark-color);
            color: var(--white);
            border: 1px solid var(--primary-color);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-label {
            color: var(--light-gray);
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white);
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 69, 0, 0.25);
            color: var(--white);
        }

        .form-control::placeholder {
            color: var(--light-gray);
            opacity: 0.7;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary-custom:hover {
            background-color: #e63e00;
            border-color: #e63e00;
            color: var(--white);
        }

        .progress {
            height: 20px;
            margin-top: 15px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .progress-bar {
            background-color: var(--primary-color);
        }

        .upload-progress {
            display: none;
        }

        .firebase-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }

        .firebase-status.success {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }

        .firebase-status.error {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }
        .table-dark-custom {
            --bs-table-bg: rgba(255, 255, 255, 0.05);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
            --bs-table-striped-color: var(--white);
            --bs-table-active-bg: rgba(255, 255, 255, 0.1);
            --bs-table-active-color: var(--white);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.07);
            --bs-table-hover-color: var(--white);
            color: var(--white);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .table-dark-custom th, .table-dark-custom td {
            border-color: rgba(255, 255, 255, 0.1);
        }
        .table-dark-custom thead th {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
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
            <p>Manage your photography website with Firebase integration</p>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-images icon"></i>
                    <h3 id="totalPhotos"><?php echo $stats['totalMedia']; ?></h3>
                    <p>Total Media</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-portrait icon"></i>
                    <h3><?php echo $stats['byCategory']['portraits'] ?? 0; ?></h3>
                    <p>Portraits</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users icon"></i>
                    <h3><?php echo $stats['byCategory']['family'] ?? 0; ?></h3>
                    <p>Family Photos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-video icon"></i>
                    <h3><?php echo $stats['byCategory']['videos'] ?? 0; ?></h3>
                    <p>Videos</p>
                </div>
            </div>
        </div>
        <div class="admin-actions">
            <h3><i class="fas fa-cogs"></i> Quick Actions</h3>
            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="button" class="action-btn" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                        <i class="fas fa-plus"></i> Add New Photo/Video
                    </button>
                    <button type="button" class="action-btn" data-bs-toggle="modal" data-bs-target="#createCollectionModal">
                        <i class="fas fa-folder-plus"></i> Create New Collection
                    </button>
                    <button type="button" class="action-btn" onclick="loadFirebaseData()">
                        <i class="fas fa-cloud"></i> Load from Firebase
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="#" class="action-btn">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="#" class="action-btn">
                        <i class="fas fa-envelope"></i> View Messages
                    </a>
                    <a href="index.php" class="action-btn">
                        <i class="fas fa-globe"></i> View Website
                    </a>
                </div>
            </div>
        </div>
        <div class="collection-management">
            <h3><i class="fas fa-database"></i> Manage Collections</h3>
            <p class="text-muted">
                <i class="fas fa-info-circle"></i>
                For dynamically created collections to appear here, you might need to manually add them to the `categories` array in `firebase_helper.php`'s `getAllStats` function, or implement a more dynamic collection listing mechanism.
            </p>
            <div class="table-responsive mt-4">
                <table class="table table-dark-custom table-striped">
                    <thead>
                        <tr>
                            <th>Collection Name</th>
                            <th>Items</th>
                            <th>Owner</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="collectionsTableBody">
                        <?php foreach ($stats['collectionDetails'] as $categoryName => $details): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucfirst($categoryName)); ?></td>
                                <td><?php echo htmlspecialchars($details['count']); ?></td>
                                <td><?php echo htmlspecialchars($details['ownerName']); ?></td>
                                <td><?php echo htmlspecialchars($firebaseHelper->formatDate($details['createdAt'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="editCollection('<?php echo htmlspecialchars($categoryName); ?>', '<?php echo htmlspecialchars($details['ownerName']); ?>', '<?php echo htmlspecialchars($details['firstDocId'] ?? ''); ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="<?php echo htmlspecialchars($categoryName); ?>.php" class="btn btn-sm btn-outline-info ms-2">
                                        <i class="fas fa-eye"></i> Display
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger ms-2"
                                        onclick="confirmDeleteCollection('<?php echo htmlspecialchars($categoryName); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Add Photo Modal -->
    <div class="modal fade" id="addPhotoModal" tabindex="-1" aria-labelledby="addPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPhotoModalLabel">Add New Photo/Video to Firebase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addMediaForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="mediaName" class="form-label">Name/Title</label>
                            <input type="text" class="form-control" id="mediaName" name="mediaName" required>
                        </div>
                        <div class="mb-3">
                            <label for="mediaCategory" class="form-label">Category</label>
                            <select class="form-select" id="mediaCategory" name="mediaCategory" required>
                                <option value="">Select a category</option>
                                <option value="portraits">Portraits</option>
                                <option value="family">Family</option>
                                <option value="headshots">Headshots</option>
                                <option value="videos">Videos</option>
                                <option value="premiumcontent1">Premium Content 1</option>
                                <option value="premiumcontent2">Premium Content 2</option>
                                <option value="premiumcontent3">Premium Content 3</option>
                                <option value="premiumcontent4">Premium Content 4</option>
                                <option value="premiumcontent5">Premium Content 5</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mediaFile" class="form-label">Upload Image/Video</label>
                            <input class="form-control" type="file" id="mediaFile" name="mediaFile" accept="image/*,video/*" required>
                        </div>

                        <div class="upload-progress">
                            <label class="form-label">Upload Progress</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="uploadProgressBar"></div>
                            </div>
                            <small class="text-muted" id="uploadStatus">Ready to upload...</small>
                        </div>

                        <div class="firebase-status" id="firebaseStatus"></div>

                        <div class="mb-3 <?php echo ($uploadMessage) ? '' : 'd-none'; ?>" id="uploadMessageContainer">
                            <div class="alert alert-<?php echo $uploadMessageType; ?>" role="alert">
                                <?php echo $uploadMessage; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom" name="submit_media" id="submitMediaBtn">
                            <i class="fas fa-cloud-upload-alt"></i> Upload to Firebase
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Create Collection Modal -->
    <div class="modal fade" id="createCollectionModal" tabindex="-1" aria-labelledby="createCollectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCollectionModalLabel">Create New Firebase Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createCollectionForm">
                        <div class="mb-3">
                            <label for="newCollectionName" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="newCollectionName" required pattern="^[a-zA-Z0-9_-]+$" title="Only letters, numbers, hyphens, and underscores are allowed. No spaces or slashes." >
                        </div>
                        <div class="mb-3">
                            <label for="collectionOwnerName" class="form-label">Owner's Name</label>
                            <input type="text" class="form-control" id="collectionOwnerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="collectionPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="collectionPassword" required>
                            <small class="form-text text-muted">
                                <i class="fas fa-exclamation-triangle"></i> For production, passwords should be hashed and not stored directly.
                            </small>
                        </div>

                        <div class="upload-progress" id="createCollectionProgress" style="display: none;">
                            <label class="form-label">Creating Collection...</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="createCollectionProgressBar"></div>
                            </div>
                            <small class="text-muted" id="createCollectionStatus">Processing...</small>
                        </div>

                        <div class="firebase-status" id="createCollectionFirebaseStatus"></div>

                        <button type="submit" class="btn btn-primary-custom" id="createCollectionBtn">
                            <i class="fas fa-plus-circle"></i> Create Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Collection Modal -->
    <div class="modal fade" id="editCollectionModal" tabindex="-1" aria-labelledby="editCollectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCollectionModalLabel">Edit Collection Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCollectionForm">
                        <div class="mb-3">
                            <label for="editCollectionName" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="editCollectionName" readonly>
                            <input type="hidden" id="editCollectionDocId">
                        </div>
                        <div class="mb-3">
                            <label for="editCollectionOwnerName" class="form-label">Owner's Name</label>
                            <input type="text" class="form-control" id="editCollectionOwnerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCollectionPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editCollectionPassword">
                            <small class="form-text text-muted">
                                <i class="fas fa-exclamation-triangle"></i> Leave blank to keep current password. For production, passwords should be hashed.
                            </small>
                        </div>

                        <div class="upload-progress" id="editCollectionProgress" style="display: none;">
                            <label class="form-label">Updating Collection...</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="editCollectionProgressBar"></div>
                            </div>
                            <small class="text-muted" id="editCollectionStatus">Processing...</small>
                        </div>

                        <div class="firebase-status" id="editCollectionFirebaseStatus"></div>

                        <button type="submit" class="btn btn-primary-custom" id="updateCollectionBtn">
                            <i class="fas fa-save"></i> Update Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Collection Confirmation Modal -->
    <div class="modal fade" id="deleteCollectionConfirmModal" tabindex="-1" aria-labelledby="deleteCollectionConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCollectionConfirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the collection "<strong id="collectionToDeleteName"></strong>"?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action is irreversible and will delete ALL documents within this collection.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCollectionBtn">Delete Collection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
        import { getFirestore, collection, addDoc, getDocs, query, orderBy, updateDoc, doc, deleteDoc, limit } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js';
        import { getStorage, ref, uploadBytesResumable, getDownloadURL } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-storage.js';

        // Firebase configuration from PHP
        const firebaseConfig = <?php echo json_encode($firebaseConfig); ?>;

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const storage = getStorage(app);

        // Make functions globally available
        window.uploadToFirebase = uploadToFirebase;
        window.loadFirebaseData = loadFirebaseData;
        window.updateStats = updateStats;
        window.editCollection = editCollection; // Make editCollection globally available
        window.confirmDeleteCollection = confirmDeleteCollection; // Make confirmDeleteCollection globally available

        // Helper functions for create collection modal
        function showCreateCollectionProgress(show) {
            const progressDiv = document.getElementById('createCollectionProgress');
            progressDiv.style.display = show ? 'block' : 'none';
        }
        function updateCreateCollectionProgress(progress) {
            const progressBar = document.getElementById('createCollectionProgressBar');
            progressBar.style.width = progress + '%';
        }
        function updateCreateCollectionStatus(status) {
            document.getElementById('createCollectionStatus').textContent = status;
        }
        function showCreateCollectionFirebaseStatus(message, type) {
            const statusDiv = document.getElementById('createCollectionFirebaseStatus');
            statusDiv.textContent = message;
            statusDiv.className = `firebase-status ${type}`;
            statusDiv.style.display = 'block';
        }

        // Handle create collection form submission
        document.getElementById('createCollectionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const collectionName = document.getElementById('newCollectionName').value.trim();
            const ownerName = document.getElementById('collectionOwnerName').value.trim();
            const password = document.getElementById('collectionPassword').value; // Password as plain text for now, will add security note

            if (!collectionName || !ownerName || !password) {
                showCreateCollectionFirebaseStatus('All fields are required.', 'error');
                return;
            }

            // Validate collection name for slashes to ensure top-level creation
            if (collectionName.includes('/')) {
                showCreateCollectionFirebaseStatus('Collection name cannot contain slashes (/) to create a top-level collection.', 'error');
                return;
            }

            const createBtn = document.getElementById('createCollectionBtn');
            createBtn.disabled = true;
            createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            showCreateCollectionProgress(true);
            updateCreateCollectionProgress(0);
            updateCreateCollectionStatus('Initializing...');
            showCreateCollectionFirebaseStatus('', ''); // Clear previous status

            try {
                // Simulate progress for collection creation (as it's a single API call)
                updateCreateCollectionProgress(30);
                updateCreateCollectionStatus('Sending data to Firebase...');
                const docData = {
                    ownerName: ownerName,
                    password: password, // WARNING: Storing plain passwords is insecure. Hash them in a real application.
                    createdAt: new Date().toISOString(),
                    description: `Initial document for ${collectionName} collection.`,
                    status: 'active'
                };
                // Add a dummy document to implicitly create the collection
                const docRef = await addDoc(collection(db, collectionName), docData);
                updateCreateCollectionProgress(100);
                updateCreateCollectionStatus('Collection created!');
                showCreateCollectionFirebaseStatus(`Collection '${collectionName}' created successfully!`, 'success');
                // Reset form
                document.getElementById('createCollectionForm').reset();

                // Update overall stats and collection list
                await updateStats();
                // Hide modal after a delay
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('createCollectionModal')).hide();
                }, 2000);
            } catch (error) {
                console.error('Error creating collection:', error);
                updateCreateCollectionProgress(0);
                updateCreateCollectionStatus('Failed!');
                showCreateCollectionFirebaseStatus('Failed to create collection: ' + error.message, 'error');
            } finally {
                createBtn.disabled = false;
                createBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Create Collection';
                showCreateCollectionProgress(false); // Hide progress bar on completion/error
            }
        });

        // Modal event handler to reset form and status when hidden
        const createCollectionModal = document.getElementById('createCollectionModal');
        createCollectionModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('createCollectionForm').reset();
            showCreateCollectionProgress(false);
            showCreateCollectionFirebaseStatus('', '');
        });

        // Helper functions for edit collection modal
        function showEditCollectionProgress(show) {
            const progressDiv = document.getElementById('editCollectionProgress');
            progressDiv.style.display = show ? 'block' : 'none';
        }
        function updateEditCollectionProgress(progress) {
            const progressBar = document.getElementById('editCollectionProgressBar');
            progressBar.style.width = progress + '%';
        }
        function updateEditCollectionStatus(status) {
            document.getElementById('editCollectionStatus').textContent = status;
        }
        function showEditCollectionFirebaseStatus(message, type) {
            const statusDiv = document.getElementById('editCollectionFirebaseStatus');
            statusDiv.textContent = message;
            statusDiv.className = `firebase-status ${type}`;
            statusDiv.style.display = 'block';
        }

        // Function to populate and show the edit modal
        async function editCollection(collectionName, ownerName, docId) {
            const editModal = new bootstrap.Modal(document.getElementById('editCollectionModal'));
            document.getElementById('editCollectionName').value = collectionName;
            document.getElementById('editCollectionOwnerName').value = ownerName;
            document.getElementById('editCollectionDocId').value = docId; // Store the document ID
            document.getElementById('editCollectionPassword').value = ''; // Clear password field for security
            showEditCollectionFirebaseStatus('', ''); // Clear previous status
            showEditCollectionProgress(false); // Hide progress bar
            editModal.show();
        }

        // Handle edit collection form submission
        document.getElementById('editCollectionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const collectionName = document.getElementById('editCollectionName').value.trim();
            const ownerName = document.getElementById('editCollectionOwnerName').value.trim();
            const docId = document.getElementById('editCollectionDocId').value.trim();
            const password = document.getElementById('editCollectionPassword').value;

            if (!ownerName) {
                showEditCollectionFirebaseStatus('Owner\'s Name is required.', 'error');
                return;
            }
            if (!docId) {
                showEditCollectionFirebaseStatus('Error: Document ID not found for this collection.', 'error');
                return;
            }

            const updateBtn = document.getElementById('updateCollectionBtn');
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            showEditCollectionProgress(true);
            updateEditCollectionProgress(0);
            updateEditCollectionStatus('Initializing update...');
            showEditCollectionFirebaseStatus('', ''); // Clear previous status

            try {
                updateEditCollectionProgress(30);
                updateEditCollectionStatus('Sending update to Firebase...');
                const updateData = {
                    ownerName: ownerName,
                    updatedAt: new Date().toISOString()
                };
                if (password) {
                    updateData.password = password; // WARNING: Storing plain passwords is insecure. Hash them.
                }
                const docRef = doc(db, collectionName, docId);
                await updateDoc(docRef, updateData);
                updateEditCollectionProgress(100);
                updateEditCollectionStatus('Collection updated!');
                showEditCollectionFirebaseStatus(`Collection '${collectionName}' updated successfully!`, 'success');
                // Update overall stats and collection list
                await updateStats();
                // Hide modal after a delay
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('editCollectionModal')).hide();
                }, 2000);
            } catch (error) {
                console.error('Error updating collection:', error);
                updateEditCollectionProgress(0);
                updateEditCollectionStatus('Failed!');
                showEditCollectionFirebaseStatus('Failed to update collection: ' + error.message, 'error');
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fas fa-save"></i> Update Collection';
                showEditCollectionProgress(false); // Hide progress bar on completion/error
            }
        });

        // Modal event handler to reset form and status when hidden
        const editCollectionModal = document.getElementById('editCollectionModal');
        editCollectionModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('editCollectionForm').reset();
            showEditCollectionProgress(false);
            showEditCollectionFirebaseStatus('', '');
        });

        // --- Delete Collection Logic ---
        let collectionToDelete = ''; // Variable to store the name of the collection to be deleted

        function confirmDeleteCollection(collectionName) {
            collectionToDelete = collectionName;
            document.getElementById('collectionToDeleteName').textContent = collectionName;
            const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteCollectionConfirmModal'));
            deleteConfirmModal.show();
        }

        document.getElementById('confirmDeleteCollectionBtn').addEventListener('click', async function() {
            const deleteConfirmModal = bootstrap.Modal.getInstance(document.getElementById('deleteCollectionConfirmModal'));
            deleteConfirmModal.hide(); // Hide the confirmation modal

            if (!collectionToDelete) {
                alert('Error: No collection selected for deletion.');
                return;
            }

            try {
                // Make an AJAX call to your PHP endpoint to delete the collection
                const response = await fetch('api/delete_collection.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ collectionName: collectionToDelete }),
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Collection '${collectionToDelete}' deleted successfully!`);
                    await updateStats(); // Refresh the table
                } else {
                    alert(`Failed to delete collection '${collectionToDelete}': ${result.message}`);
                }
            } catch (error) {
                console.error('Error deleting collection:', error);
                alert('An error occurred while trying to delete the collection.');
            } finally {
                collectionToDelete = ''; // Clear the stored collection name
            }
        });
        // --- End Delete Collection Logic ---


        // Upload function (remains the same)
        async function uploadToFirebase(fileData, documentData) {
            try {
                showUploadProgress(true);
                updateUploadStatus('Starting upload...');

                // Convert base64 to blob
                const byteCharacters = atob(fileData.data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: fileData.type });

                // Upload to Firebase Storage
                const storageRef = ref(storage, fileData.path);
                const uploadTask = uploadBytesResumable(storageRef, blob);

                return new Promise((resolve, reject) => {
                    uploadTask.on('state_changed',
                        (snapshot) => {
                            const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                            updateUploadProgress(progress);
                            updateUploadStatus(`Upload is ${progress.toFixed(1)}% done`);
                        },
                        (error) => {
                            showFirebaseStatus('Upload failed: ' + error.message, 'error');
                            reject(error);
                        },
                        async () => {
                            try {
                                const downloadURL = await getDownloadURL(uploadTask.snapshot.ref);
                                updateUploadStatus('File uploaded, saving metadata...');

                                // Use category as collection name
                                const collectionName = documentData.category;

                                // Add document to Firestore using category as collection
                                const docData = {
                                    ...documentData,
                                    downloadURL: downloadURL,
                                    storagePath: fileData.path
                                };

                                const docRef = await addDoc(collection(db, collectionName), docData);

                                showFirebaseStatus(`Successfully uploaded to ${collectionName} collection!`, 'success');
                                updateUploadStatus('Upload complete!');

                                // Update stats
                                updateStats();

                                resolve({
                                    id: docRef.id,
                                    collection: collectionName,
                                    downloadURL: downloadURL,
                                    ...docData
                                });
                            } catch (error) {
                                showFirebaseStatus('Failed to save metadata: ' + error.message, 'error');
                                reject(error);
                            }
                        }
                    );
                });
            } catch (error) {
                showFirebaseStatus('Upload error: ' + error.message, 'error');
                throw error;
            }
        }

        // Load data from Firebase (remains the same)
        async function loadFirebaseData() {
            try {
                // Updated categories array to include premium content
                const categories = ['portraits', 'family', 'headshots', 'videos', 'premiumcontent1', 'premiumcontent2', 'premiumcontent3', 'premiumcontent4', 'premiumcontent5'];
                let totalItems = 0;

                console.log('Firebase Media Items by Category:');

                for (const category of categories) {
                    try {
                        const q = query(collection(db, category), orderBy('createdAt', 'desc')); // Consistent with 'createdAt'
                        const querySnapshot = await getDocs(q);

                        console.log(`\n${category.toUpperCase()} (${querySnapshot.size} items):`);
                        querySnapshot.forEach((doc) => {
                            console.log(doc.id, ' => ', doc.data());
                        });

                        totalItems += querySnapshot.size;
                    } catch (error) {
                        console.log(`No items found in ${category} collection or error:`, error.message);
                    }
                }

                showFirebaseStatus(`Loaded ${totalItems} total items from all collections`, 'success');
            } catch (error) {
                showFirebaseStatus('Failed to load data: ' + error.message, 'error');
            }
        }

        // Update statistics from Firebase (modified to also update collection table)
        async function updateStats() {
            try {
                // Updated categories array to include premium content
                const categories = ['portraits', 'family', 'headshots', 'videos', 'premiumcontent1', 'premiumcontent2', 'premiumcontent3', 'premiumcontent4', 'premiumcontent5'];
                let totalMedia = 0;
                const collectionDetails = {};

                for (const category of categories) {
                    try {
                        const categorySnapshot = await getDocs(collection(db, category));
                        const count = categorySnapshot.size;
                        totalMedia += count;

                        let ownerName = 'N/A';
                        let createdAt = 'N/A';
                        let firstDocId = null;
                        if (count > 0) {
                            const firstDocSnapshot = await getDocs(query(collection(db, category), orderBy('createdAt', 'asc'), limit(1))); // Consistent with 'createdAt'
                            if (!firstDocSnapshot.empty) {
                                const docData = firstDocSnapshot.docs[0].data();
                                ownerName = docData.ownerName || 'N/A'; // Consistent with 'ownerName'
                                createdAt = docData.createdAt || 'N/A'; // Consistent with 'createdAt'
                                firstDocId = firstDocSnapshot.docs[0].id;
                            }
                        }

                        collectionDetails[category] = {
                            count: count,
                            ownerName: ownerName,
                            createdAt: createdAt,
                            firstDocId: firstDocId
                        };
                    } catch (error) {
                        console.log(`${category} collection not found or empty:`, error.message);
                        collectionDetails[category] = {
                            count: 0,
                            ownerName: 'N/A',
                            createdAt: 'N/A',
                            firstDocId: null
                        };
                    }
                }

                document.getElementById('totalPhotos').textContent = totalMedia;
                // Update the collections table
                const collectionsTableBody = document.getElementById('collectionsTableBody');
                collectionsTableBody.innerHTML = ''; // Clear existing rows
                for (const categoryName in collectionDetails) {
                    const details = collectionDetails[categoryName];
                    const row = collectionsTableBody.insertRow();
                    row.innerHTML = `
                        <td>${capitalizeFirstLetter(categoryName)}</td>
                        <td>${details.count}</td>
                        <td>${details.ownerName}</td>
                        <td>${formatDate(details.createdAt)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="editCollection('${categoryName}', '${details.ownerName}', '${details.firstDocId || ''}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="${categoryName}.php" class="btn btn-sm btn-outline-info ms-2">
                                <i class="fas fa-eye"></i> Display
                            </a>
                            <button type="button" class="btn btn-sm btn-danger ms-2"
                                onclick="confirmDeleteCollection('${categoryName}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                }
            } catch (error) {
                console.error('Failed to update stats:', error);
            }
        }
        // Helper functions (remains the same)
        function showUploadProgress(show) {
            const progressDiv = document.querySelector('.upload-progress');
            progressDiv.style.display = show ? 'block' : 'none';
        }
        function updateUploadProgress(progress) {
            const progressBar = document.getElementById('uploadProgressBar');
            progressBar.style.width = progress + '%';
        }
        function updateUploadStatus(status) {
            document.getElementById('uploadStatus').textContent = status;
        }
        function showFirebaseStatus(message, type) {
            const statusDiv = document.getElementById('firebaseStatus');
            statusDiv.textContent = message;
            statusDiv.className = `firebase-status ${type}`;
            statusDiv.style.display = 'block';
        }
        // Utility functions for formatting (replicated from PHP for JS display)
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
        function formatDate(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });
            } catch (e) {
                return 'Unknown date';
            }
        }
        // Load initial stats
        updateStats();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle form submission for media upload (remains the same)
        document.getElementById('addMediaForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const fileInput = document.getElementById('mediaFile');

            if (!fileInput.files[0]) {
                alert('Please select a file');
                return;
            }

            try {
                // Disable submit button
                const submitBtn = document.getElementById('submitMediaBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

                // Prepare file data
                const file = fileInput.files[0];
                const reader = new FileReader();

                reader.onload = async function(e) {
                    const base64Data = e.target.result.split(',')[1];
                    const fileName = Date.now() + '_' + file.name;
                    const category = formData.get('mediaCategory');

                    const fileData = {
                        data: base64Data,
                        name: fileName,
                        type: file.type,
                        path: `media/${category}/${fileName}`
                    };

                    const documentData = {
                        name: formData.get('mediaName'),
                        category: category,
                        fileName: fileName,
                        originalName: file.name,
                        fileType: file.type,
                        fileSize: file.size,
                        ownerName: '<?php echo $admin_username; ?>', // Consistent with 'ownerName'
                        createdAt: new Date().toISOString(), // Consistent with 'createdAt'
                        status: 'active'
                    };

                    try {
                        await window.uploadToFirebase(fileData, documentData);

                        // Reset form
                        document.getElementById('addMediaForm').reset();

                        // Hide modal after delay
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('addPhotoModal')).hide();
                        }, 2000);

                    } catch (error) {
                        console.error('Upload failed:', error);
                    } finally {
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload to Firebase';
                    }
                };

                reader.readAsDataURL(file);

            } catch (error) {
                console.error('Form submission error:', error);
                alert('Upload failed: ' + error.message);
            }
        });

        // Modal event handlers for add photo modal (remains the same)
        const addPhotoModal = document.getElementById('addPhotoModal');
        addPhotoModal.addEventListener('hidden.bs.modal', function () {
            // Reset form and hide status messages
            document.getElementById('addMediaForm').reset();
            document.querySelector('.upload-progress').style.display = 'none';
            document.getElementById('firebaseStatus').style.display = 'none';

            const uploadMessageContainer = document.getElementById('uploadMessageContainer');
            if (uploadMessageContainer) {
                uploadMessageContainer.classList.add('d-none');
            }
        });

        // Show modal if PHP has a message (remains the same)
        <?php if ($uploadMessage && $firebaseData): ?>
            var addPhotoModalInstance = new bootstrap.Modal(document.getElementById('addPhotoModal'));
            addPhotoModalInstance.show();

            // Auto-upload the prepared data
            setTimeout(async () => {
                const firebaseData = <?php echo json_encode($firebaseData); ?>;
                try {
                    await window.uploadToFirebase(firebaseData.file, firebaseData.document);
                } catch (error) {
                    console.error('Auto-upload failed:', error);
                }
            }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
