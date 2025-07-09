<?php
// Firebase configuration
class FirebaseConfig {
    const PROJECT_ID = 'photographyportfolio-db8b4';
    const API_KEY = 'AIzaSyDSOd8k0F6HgJA2w9wly7ppJ4a6zpE5wgg';
    const DATABASE_URL = 'https://photographyportfolio-db8b4-default-rtdb.firebaseio.com';
    const STORAGE_BUCKET = 'photographyportfolio-db8b4.firebasestorage.app';
    
    public static function getConfig() {
        return [
            'apiKey' => self::API_KEY,
            'authDomain' => self::PROJECT_ID . '.firebaseapp.com',
            'databaseURL' => self::DATABASE_URL,
            'projectId' => self::PROJECT_ID,
            'storageBucket' => self::STORAGE_BUCKET,
            'messagingSenderId' => '613103682575',
            'appId' => '1:613103682575:web:7a79d5c4dc4cb514c2cf7a'
        ];
    }
}

// Firebase helper functions
function uploadToFirebaseStorage($file, $path) {
    // This would typically use Firebase Admin SDK
    // For now, we'll prepare the data for JavaScript upload
    return [
        'success' => true,
        'file_data' => base64_encode(file_get_contents($file['tmp_name'])),
        'file_name' => $file['name'],
        'file_type' => $file['type'],
        'path' => $path
    ];
}

function saveToFirestore($collection, $data) {
    // Prepare data for JavaScript submission
    return [
        'collection' => $collection,
        'data' => $data,
        'timestamp' => date('c')
    ];
}
?>
