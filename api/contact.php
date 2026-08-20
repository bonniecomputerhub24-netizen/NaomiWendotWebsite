<?php
/**
 * Contact Form API Endpoint
 * Handles contact form submissions
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('All fields are required.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }
    
    if (strlen($name) < 2) {
        throw new Exception('Name must be at least 2 characters.');
    }
    
    if (strlen($message) < 10) {
        throw new Exception('Message must be at least 10 characters.');
    }
    
    // Spam prevention: Check for honeypot field
    if (!empty($_POST['website'])) {
        // Bot detected
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Thank you for your message!']);
        exit;
    }
    
    // Rate limiting: Check if same email submitted recently
    $db = getDb();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM contact_messages 
        WHERE email = :email 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([':email' => $email]);
    $recent = $stmt->fetch();
    
    if ($recent['count'] > 2) {
        throw new Exception('Too many messages. Please wait before sending another.');
    }
    
    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO contact_messages (name, email, message, created_at)
        VALUES (:name, :email, :message, NOW())
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message
    ]);
    
    // TODO: Send email notification to admin
    // mail('info@naomiwendot.com', 'New Contact Form Message', ...);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! Naomi will respond as soon as possible.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
