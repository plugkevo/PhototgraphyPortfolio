<?php
// API endpoint for Firebase operations
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check if admin is logged in for write operations
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleGet($action);
        break;
    case 'POST':
        checkAdminAuth();
        handlePost($action);
        break;
    case 'PUT':
        checkAdminAuth();
        handlePut($action);
        break;
    case 'DELETE':
        checkAdminAuth();
        handleDelete($action);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGet($action) {
    switch ($action) {
        case 'stats':
            // Return cached stats or placeholder data
            echo json_encode([
                'totalPhotos' => 156,
                'totalUsers' => 42,
                'pageViews' => 1234,
                'newMessages' => 18
            ]);
            break;
        case 'media':
            // Return media list (would typically come from Firebase)
            echo json_encode([
                'media' => [],
                'message' => 'Media list would be loaded from Firebase'
            ]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}

function handlePost($action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'upload':
            // Handle upload confirmation
            echo json_encode([
                'success' => true,
                'message' => 'Upload processed',
                'data' => $input
            ]);
            break;
        case 'batch-upload':
            // Handle batch upload
            echo json_encode([
                'success' => true,
                'message' => 'Batch upload processed',
                'count' => count($input['files'] ?? [])
            ]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}

function handlePut($action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'media':
            // Handle media update
            echo json_encode([
                'success' => true,
                'message' => 'Media updated',
                'id' => $input['id'] ?? null
            ]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}

function handleDelete($action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'media':
            // Handle media deletion
            echo json_encode([
                'success' => true,
                'message' => 'Media deleted',
                'id' => $input['id'] ?? null
            ]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}
?>
