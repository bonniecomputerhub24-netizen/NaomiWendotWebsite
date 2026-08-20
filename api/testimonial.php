<?php
/**
 * Testimonial Submission API Endpoint
 * Handles reader testimony submissions
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
    $location = trim($_POST['location'] ?? '');
    $testimony = trim($_POST['testimony'] ?? '');
    
    // Validation
    if (empty($name) || empty($testimony)) {
        throw new Exception('Name and testimony are required.');
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }
    
    if (strlen($name) < 2) {
        throw new Exception('Name must be at least 2 characters.');
    }
    
    if (strlen($testimony) < 20) {
        throw new Exception('Testimony must be at least 20 characters.');
    }
    
    // Spam prevention: Check for honeypot field
    if (!empty($_POST['website'])) {
        // Bot detected
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Thank you for sharing your testimony!']);
        exit;
    }
    
    // Rate limiting
    $db = getDb();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM testimonies 
        WHERE name = :name 
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([':name' => $name]);
    $recent = $stmt->fetch();
    
    if ($recent['count'] > 0) {
        throw new Exception('You have already submitted a testimony recently. Please wait 24 hours.');
    }
    
    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO testimonies (name, email, location, testimony, status, created_at)
        VALUES (:name, :email, :location, :testimony, 'pending', NOW())
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':location' => $location,
        ':testimony' => $testimony
    ]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for sharing! Your testimony will be reviewed and published soon.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
