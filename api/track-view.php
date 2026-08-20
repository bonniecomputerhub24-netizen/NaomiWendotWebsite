<?php
/**
 * Track Video View API Endpoint
 * Increments video_views count for video posts (once per session per video)
 */

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate post_id
if (!isset($input['post_id']) || !is_numeric($input['post_id']) || $input['post_id'] <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post_id']);
    exit;
}

$postId = (int)$input['post_id'];

// Connect to database
require_once __DIR__ . '/../config/db.php';

try {
    // Verify post exists and is a video
    $checkStmt = $pdo->prepare("
        SELECT id, video_views 
        FROM posts 
        WHERE id = :id AND content_type = 'video'
    ");
    $checkStmt->execute(['id' => $postId]);
    $post = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Video post not found']);
        exit;
    }
    
    // Increment video_views
    $updateStmt = $pdo->prepare("
        UPDATE posts 
        SET video_views = video_views + 1 
        WHERE id = :id
    ");
    $updateStmt->execute(['id' => $postId]);
    
    // Get new count
    $newCount = (int)$post['video_views'] + 1;
    
    echo json_encode([
        'success' => true,
        'views' => $newCount
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
