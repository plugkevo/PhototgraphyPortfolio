<?php
session_start();
require_once 'firebase_config.php';

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

// Handle form submission
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
            'uploadedBy' => $admin_username,
            'uploadedAt' => date('c'),
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
                    <h3 id="totalPhotos">156</h3>
                    <p>Total Photos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users icon"></i>
                    <h3 id="totalUsers">42</h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-eye icon"></i>
                    <h3 id="pageViews">1,234</h3>
                    <p>Page Views</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-envelope icon"></i>
                    <h3 id="newMessages">18</h3>
                    <p>New Messages</p>
                </div>
            </div>
        </div>

        <div class="admin-actions">
            <h3><i class="fas fa-cogs"></i> Quick Actions</h3>
            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="button" class="action-btn" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                        <i class="fas fa-plus"></i> Add New Photo
                    </button>
                    <button type="button" class="action-btn" onclick="loadFirebaseData()">
                        <i class="fas fa-cloud"></i> Load from Firebase
                    </button>
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
                    <a href="index.php" class="action-btn">
                        <i class="fas fa-globe"></i> View Website
                    </a>
                </div>
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

    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
        import { getFirestore, collection, addDoc, getDocs, query, orderBy } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js';
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

        // Upload function
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

        // Load data from Firebase
        async function loadFirebaseData() {
            try {
                const categories = ['portraits', 'family', 'headshots', 'videos'];
                let totalItems = 0;
                
                console.log('Firebase Media Items by Category:');
                
                for (const category of categories) {
                    try {
                        const q = query(collection(db, category), orderBy('uploadedAt', 'desc'));
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

        // Update statistics from Firebase
        async function updateStats() {
            try {
                const categories = ['portraits', 'family', 'headshots', 'videos'];
                let totalMedia = 0;
                
                for (const category of categories) {
                    try {
                        const categorySnapshot = await getDocs(collection(db, category));
                        totalMedia += categorySnapshot.size;
                    } catch (error) {
                        // Collection might not exist yet, which is fine
                        console.log(`${category} collection not found or empty`);
                    }
                }
                
                document.getElementById('totalPhotos').textContent = totalMedia;
            } catch (error) {
                console.error('Failed to update stats:', error);
            }
        }

        // Helper functions
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

        // Load initial stats
        updateStats();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle form submission
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
                        uploadedBy: '<?php echo $admin_username; ?>',
                        uploadedAt: new Date().toISOString(),
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

        // Modal event handlers
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

        // Show modal if PHP has a message
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
