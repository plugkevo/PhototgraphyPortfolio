<?php

require_once 'firebase_config.php';

class FirebaseHelper {
    private $config;
    private $projectId;

    public function __construct() {
        $this->config = FirebaseConfig::getConfig();
        $this->projectId = $this->config['projectId'];
    }

    // Get documents from a Firestore collection using REST API with API key
    public function getCollection($collectionName, $orderBy = 'createdAt', $direction = 'desc') { // Changed default orderBy to createdAt
        // Use the REST API with API key parameter instead of Bearer token
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collectionName}";
        $url .= "?key=" . $this->config['apiKey'];

        // Add ordering if specified
        if ($orderBy) {
            $url .= "&orderBy={$orderBy}%20{$direction}";
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type: application/json'
                ],
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            // If REST API fails, return mock data for testing
            return $this->getMockData($collectionName);
        }

        $data = json_decode($response, true);

        if (isset($data['documents'])) {
            return $this->parseDocuments($data['documents']);
        }

        // If no documents found, return mock data for testing
        return $this->getMockData($collectionName);
    }

    // Get a specific document
    public function getDocument($collectionName, $documentId) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collectionName}/{$documentId}";
        $url .= "?key=" . $this->config['apiKey'];

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type: application/json'
                ],
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            // Return mock data if Firebase is not accessible
            $mockData = $this->getMockData($collectionName);
            foreach ($mockData as $item) {
                if ($item['id'] === $documentId) {
                    return $item;
                }
            }
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['fields'])) {
            return $this->parseDocument($data);
        }

        return null;
    }

    // New function: Get the first document from a collection (for metadata)
    public function getCollectionFirstDocument($collectionName) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collectionName}";
        $url .= "?key=" . $this->config['apiKey'];
        $url .= "&pageSize=1&orderBy=createdAt%20asc"; // Consistent with 'createdAt'

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type: application/json'
                ],
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            // If REST API fails, return null or a default for metadata
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['documents']) && !empty($data['documents'])) {
            return $this->parseDocument($data['documents'][0]);
        }

        return null;
    }

    /**
     * Deletes a single document from a Firestore collection.
     * @param string $collectionName The name of the collection.
     * @param string $documentId The ID of the document to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteDocument($collectionName, $documentId) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collectionName}/{$documentId}";
        $url .= "?key=" . $this->config['apiKey'];

        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => [
                    'Content-Type: application/json'
                ],
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        return $response !== false; // True on success, false on failure
    }

    /**
     * Deletes an entire Firestore collection by deleting all its documents.
     * WARNING: This can be slow and hit rate limits for large collections.
     * For production, consider Firebase Admin SDK or Cloud Functions for recursive deletion.
     * @param string $collectionName The name of the collection to delete.
     * @return bool True if all documents were successfully deleted or collection was empty, false otherwise.
     */
    public function deleteCollection($collectionName) {
        // Get all documents in the collection
        $documents = $this->getCollection($collectionName);

        if (empty($documents)) {
            return true; // Collection is already empty or doesn't exist
        }

        $success = true;
        foreach ($documents as $doc) {
            // Each document in $documents has an 'id' field
            if (!$this->deleteDocument($collectionName, $doc['id'])) {
                $success = false; // If any document fails to delete, mark as failure
                // Optionally, log the error here: error_log("Failed to delete document: {$doc['id']} from collection: {$collectionName}");
            }
        }
        return $success;
    }

    // Mock data for testing when Firebase is not accessible
    private function getMockData($collectionName) {
        $mockData = [
            'portraits' => [
                [
                    'id' => 'mock_portrait_1',
                    'name' => 'Professional Portrait 1',
                    'category' => 'portraits',
                    'originalName' => 'portrait_001.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2048576,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-2 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_portrait_2',
                    'name' => 'Professional Portrait 2',
                    'category' => 'portraits',
                    'originalName' => 'portrait_002.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1856432,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-1 day')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_portrait_3',
                    'name' => 'Professional Portrait 3',
                    'category' => 'portraits',
                    'originalName' => 'portrait_003.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2234567,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c'), // Changed from uploadedAt
                    'status' => 'active'
                ]
            ],
            'family' => [
                [
                    'id' => 'mock_family_1',
                    'name' => 'Happy Family Moment',
                    'category' => 'family',
                    'originalName' => 'family_celebration_001.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 3145728,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-3 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_family_2',
                    'name' => 'Family Outdoor Session',
                    'category' => 'family',
                    'originalName' => 'family_outdoor_002.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2987654,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-2 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_family_3',
                    'name' => 'Family Holiday Portrait',
                    'category' => 'family',
                    'originalName' => 'family_holiday_003.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2654321,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-1 day')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_family_4',
                    'name' => 'Multi-Generation Family',
                    'category' => 'family',
                    'originalName' => 'family_generations_004.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 3456789,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c'), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_family_5',
                    'name' => 'Family Beach Session',
                    'category' => 'family',
                    'originalName' => 'family_beach_005.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2876543,
                    'downloadURL' => '/placeholder.svg?height=400&width=300',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-4 hours')), // Changed from uploadedAt
                    'status' => 'active'
                ]
            ],
            'headshots' => [
                [
                    'id' => 'mock_headshot_1',
                    'name' => 'Executive Headshot - CEO',
                    'category' => 'headshots',
                    'originalName' => 'executive_headshot_001.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1572864,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-4 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_headshot_2',
                    'name' => 'Corporate Headshot - Manager',
                    'category' => 'headshots',
                    'originalName' => 'corporate_headshot_002.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1834567,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-3 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_headshot_3',
                    'name' => 'Professional Headshot - Lawyer',
                    'category' => 'headshots',
                    'originalName' => 'professional_headshot_003.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1654321,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-2 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_headshot_4',
                    'name' => 'Business Headshot - Consultant',
                    'category' => 'headshots',
                    'originalName' => 'business_headshot_004.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1987654,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-1 day')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_headshot_5',
                    'name' => 'LinkedIn Headshot - Entrepreneur',
                    'category' => 'headshots',
                    'originalName' => 'linkedin_headshot_005.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 1765432,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-6 hours')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_headshot_6',
                    'name' => 'Actor Headshot - Theatre',
                    'category' => 'headshots',
                    'originalName' => 'actor_headshot_006.jpg',
                    'fileType' => 'image/jpeg',
                    'fileSize' => 2123456,
                    'downloadURL' => '/placeholder.svg?height=320&width=280',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-2 hours')), // Changed from uploadedAt
                    'status' => 'active'
                ]
            ],
            'videos' => [
                [
                    'id' => 'mock_video_1',
                    'name' => 'Wedding Ceremony Highlights',
                    'category' => 'videos',
                    'videoType' => 'wedding',
                    'originalName' => 'wedding_ceremony_001.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 157286400, // ~150MB
                    'duration' => '5:32',
                    'quality' => '4K',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-5 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_video_2',
                    'name' => 'Corporate Event Coverage',
                    'category' => 'videos',
                    'videoType' => 'corporate',
                    'originalName' => 'corporate_event_002.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 89478485, // ~85MB
                    'duration' => '3:45',
                    'quality' => 'HD',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-4 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_video_3',
                    'name' => 'Family Portrait Session',
                    'category' => 'videos',
                    'videoType' => 'family',
                    'originalName' => 'family_session_003.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 67108864, // ~64MB
                    'duration' => '2:18',
                    'quality' => 'HD',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-3 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_video_4',
                    'name' => 'Product Showcase Reel',
                    'category' => 'videos',
                    'videoType' => 'commercial',
                    'originalName' => 'product_showcase_004.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 45678901, // ~43MB
                    'duration' => '1:55',
                    'quality' => '4K',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-2 days')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_video_5',
                    'name' => 'Behind the Scenes',
                    'category' => 'videos',
                    'videoType' => 'documentary',
                    'originalName' => 'behind_scenes_005.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 123456789, // ~117MB
                    'duration' => '4:12',
                    'quality' => 'HD',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-1 day')), // Changed from uploadedAt
                    'status' => 'active'
                ],
                [
                    'id' => 'mock_video_6',
                    'name' => 'Time-lapse Photography',
                    'category' => 'videos',
                    'videoType' => 'timelapse',
                    'originalName' => 'timelapse_006.mp4',
                    'fileType' => 'video/mp4',
                    'fileSize' => 78901234, // ~75MB
                    'duration' => '1:30',
                    'quality' => '4K',
                    'downloadURL' => '/placeholder.svg?height=200&width=350',
                    'thumbnailURL' => '/placeholder.svg?height=200&width=350',
                    'ownerName' => 'Admin', // Changed from uploadedBy
                    'createdAt' => date('c', strtotime('-6 hours')), // Changed from uploadedAt
                    'status' => 'active'
                ]
            ],
            // Add mock data for premiumcontent collections if needed for testing without Firebase
            'premiumcontent1' => [
                ['id' => 'pc1_doc1', 'name' => 'Premium Content 1 Item 1', 'category' => 'premiumcontent1', 'createdAt' => date('c', strtotime('-10 days')), 'ownerName' => 'Premium User'],
                ['id' => 'pc1_doc2', 'name' => 'Premium Content 1 Item 2', 'category' => 'premiumcontent1', 'createdAt' => date('c', strtotime('-9 days')), 'ownerName' => 'Premium User'],
            ],
            'premiumcontent2' => [
                ['id' => 'pc2_doc1', 'name' => 'Premium Content 2 Item 1', 'category' => 'premiumcontent2', 'createdAt' => date('c', strtotime('-8 days')), 'ownerName' => 'Premium User'],
            ],
            'premiumcontent3' => [
                ['id' => 'pc3_doc1', 'name' => 'Premium Content 3 Item 1', 'category' => 'premiumcontent3', 'createdAt' => date('c', strtotime('-7 days')), 'ownerName' => 'Premium User'],
                ['id' => 'pc3_doc2', 'name' => 'Premium Content 3 Item 2', 'category' => 'premiumcontent3', 'createdAt' => date('c', strtotime('-6 days')), 'ownerName' => 'Premium User'],
                ['id' => 'pc3_doc3', 'name' => 'Premium Content 3 Item 3', 'category' => 'premiumcontent3', 'createdAt' => date('c', strtotime('-5 days')), 'ownerName' => 'Premium User'],
            ],
            'premiumcontent4' => [
                ['id' => 'pc4_doc1', 'name' => 'Premium Content 4 Item 1', 'category' => 'premiumcontent4', 'createdAt' => date('c', strtotime('-4 days')), 'ownerName' => 'Premium User'],
            ],
            'premiumcontent5' => [
                ['id' => 'pc5_doc1', 'name' => 'Premium Content 5 Item 1', 'category' => 'premiumcontent5', 'createdAt' => date('c', strtotime('-3 days')), 'ownerName' => 'Premium User'],
                ['id' => 'pc5_doc2', 'name' => 'Premium Content 5 Item 2', 'category' => 'premiumcontent5', 'createdAt' => date('c', strtotime('-2 days')), 'ownerName' => 'Premium User'],
            ],
        ];

        return $mockData[$collectionName] ?? [];
    }

    // Parse Firestore documents
    private function parseDocuments($documents) {
        $result = [];
        foreach ($documents as $doc) {
            $result[] = $this->parseDocument($doc);
        }
        return $result;
    }

    // Parse a single Firestore document
    private function parseDocument($doc) {
        $parsed = [
            'id' => basename($doc['name'])
        ];

        if (isset($doc['fields'])) {
            foreach ($doc['fields'] as $key => $value) {
                $parsed[$key] = $this->parseFieldValue($value);
            }
        }

        return $parsed;
    }

    // Parse Firestore field values
    private function parseFieldValue($value) {
        if (isset($value['stringValue'])) {
            return $value['stringValue'];
        } elseif (isset($value['integerValue'])) {
            return (int)$value['integerValue'];
        } elseif (isset($value['doubleValue'])) {
            return (float)$value['doubleValue'];
        } elseif (isset($value['booleanValue'])) {
            return $value['booleanValue'];
        } elseif (isset($value['timestampValue'])) {
            return $value['timestampValue'];
        } elseif (isset($value['arrayValue'])) {
            $array = [];
            if (isset($value['arrayValue']['values'])) {
                foreach ($value['arrayValue']['values'] as $item) {
                    $array[] = $this->parseFieldValue($item);
                }
            }
            return $array;
        }

        return null;
    }

    // Format file size
    public function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }

    // Format date
    public function formatDate($dateString) {
        try {
            $date = new DateTime($dateString);
            return $date->format('F j, Y g:i A');
        } catch (Exception $e) {
            return 'Unknown date';
        }
    }

    // Get collection count
    public function getCollectionCount($collectionName) {
        $documents = $this->getCollection($collectionName);
        return is_array($documents) ? count($documents) : 0;
    }

    // Get all collections stats
    public function getAllStats() {
        // These are the known categories. Added premiumcontent1-5.
        $categories = ['portraits', 'family', 'headshots', 'videos', 'premiumcontent1', 'premiumcontent2', 'premiumcontent3', 'premiumcontent4', 'premiumcontent5'];
        $stats = [
            'totalMedia' => 0,
            'byCategory' => [],
            'collectionDetails' => [] // To store more details about each collection
        ];

        foreach ($categories as $category) {
            $count = $this->getCollectionCount($category);
            $stats['byCategory'][$category] = $count;
            $stats['totalMedia'] += $count;
            // Fetch first document for metadata
            $firstDoc = $this->getCollectionFirstDocument($category);
            $stats['collectionDetails'][$category] = [
                'count' => $count,
                'ownerName' => $firstDoc['ownerName'] ?? 'N/A', // Consistent with 'ownerName'
                'createdAt' => $firstDoc['createdAt'] ?? 'N/A', // Consistent with 'createdAt'
                'firstDocId' => $firstDoc['id'] ?? null // Store the ID of the first document for updates
            ];
        }

        return $stats;
    }

    // Test Firebase connection
    public function testConnection() {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
        $url .= "?key=" . $this->config['apiKey'];

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type: application/json'
                ],
                'timeout' => 10
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        return $response !== false;
    }
}
?>
