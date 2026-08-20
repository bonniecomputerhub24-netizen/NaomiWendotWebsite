<?php
/**
 * Newsletter Subscription API Endpoint
 * Handles newsletter signup
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
    $context = trim($_POST['context'] ?? 'default');
    
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
        echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
        exit;
    }
    
    $db = getDb();
    
    // Check if already subscribed
    $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['status'] === 'active') {
            throw new Exception('This email is already subscribed.');
        } else {
            // Reactivate subscription
            $stmt = $db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'active', 
                    subscribed_at = NOW(),
                    unsubscribed_at = NULL
                WHERE email = :email
            ");
            $stmt->execute([':email' => $email]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back! Your subscription has been reactivated.'
            ]);
            exit;
        }
    }
    
    // Generate verification token
    $token = bin2hex(random_bytes(32));
    
    // Insert new subscriber
    $stmt = $db->prepare("
        INSERT INTO newsletter_subscribers (email, name, status, subscribed_at, verification_token, is_verified)
        VALUES (:email, :name, 'active', NOW(), :token, 1)
    ");
    
    $stmt->execute([
        ':email' => $email,
        ':name' => $name,
        ':token' => $token
    ]);
    
    // TODO: Send welcome email
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing! You\'re now on the list.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
