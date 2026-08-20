<?php
/**
 * Like Post API Endpoint
 * Increments the like count for a post
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get post slug from request
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = $input['slug'] ?? '';
    
    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Post slug is required']);
        exit;
    }
    
    $db = getDb();
    
    // Check if post exists
    $checkStmt = $db->prepare("SELECT id, likes FROM posts WHERE slug = :slug AND status = 'published'");
    $checkStmt->execute([':slug' => $slug]);
    $post = $checkStmt->fetch();
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
    
    // Increment likes
    $updateStmt = $db->prepare("UPDATE posts SET likes = likes + 1 WHERE slug = :slug");
    $updateStmt->execute([':slug' => $slug]);
    
    // Get updated count
    $newLikes = $post['likes'] + 1;
    
    echo json_encode([
        'success' => true,
        'message' => 'Post liked successfully',
        'likes' => $newLikes
    ]);
    
} catch (Exception $e) {
    error_log("Like Post Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to like post. Please try again.'
    ]);
}
