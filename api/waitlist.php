<?php
/**
 * Waitlist API Endpoint
 * Handles Nature & Bible Verses book waitlist signups
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
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    // Validation
    if (empty($email)) {
        throw new Exception('Email address is required.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }
    
    // Spam prevention
    if (!empty($_POST['website'])) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Thank you for joining the waitlist!']);
        exit;
    }
    
    $db = getDb();
    
    // Check if already on waitlist
    $stmt = $db->prepare("SELECT id FROM waitlist WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->fetch()) {
        throw new Exception('This email is already on the waitlist.');
    }
    
    // Insert into waitlist
    $stmt = $db->prepare("
        INSERT INTO waitlist (email, name, subscribed_at)
        VALUES (:email, :name, NOW())
    ");
    
    $stmt->execute([
        ':email' => $email,
        ':name' => $name
    ]);
    
    // TODO: Send confirmation email
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'You\'re on the list! We\'ll notify you when Nature & Bible Verses launches.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
