<?php
/**
 * Track Video Like API
 * Increments the like count for a video post (one-time per session, client-side enforced)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $postId = isset($input['post_id']) ? (int)$input['post_id'] : 0;
    
    // Validate post ID
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
        exit;
    }
    
    $db = getDb();
    
    // Verify post exists and is a video
    $stmt = $db->prepare("SELECT id, likes FROM posts WHERE id = :id AND content_type = 'video' LIMIT 1");
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Video post not found']);
        exit;
    }
    
    // Increment like count
    $updateStmt = $db->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :id");
    $updateStmt->execute([':id' => $postId]);
    
    // Get updated count
    $newLikes = (int)$post['likes'] + 1;
    
    echo json_encode([
        'success' => true,
        'likes' => $newLikes
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log("track-like.php error: " . $e->getMessage());
}
