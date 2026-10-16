<?php
/**
 * Debug Family Member Login Issues
 */

session_start();
require_once __DIR__ . '/../config/db.php';

echo "<h2>Family Login Debug</h2>";

// Show current session
echo "<h3>Current Session</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

try {
    $db = getDb();
    
    // Check if family_members table exists
    $stmt = $db->query("SHOW TABLES LIKE 'family_members'");
    if (!$stmt->fetch()) {
        echo "<p><strong>ERROR:</strong> family_members table does not exist!</p>";
        echo "<p><a href='/admin/migrations/003_family_members.php'>Run Migration</a></p>";
        exit;
    }
    
    echo "<p><strong>✓</strong> family_members table exists</p>";
    
    // Show all family members
    $stmt = $db->query("SELECT id, username, full_name, email, is_active, created_at FROM family_members ORDER BY created_at DESC");
    $members = $stmt->fetchAll();
    
    echo "<h3>Family Members (" . count($members) . ")</h3>";
    
    if (empty($members)) {
        echo "<p>No family members found!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Active</th><th>Created</th></tr>";
        
        foreach ($members as $member) {
            echo "<tr>";
            echo "<td>" . $member['id'] . "</td>";
            echo "<td>" . htmlspecialchars($member['username']) . "</td>";
            echo "<td>" . htmlspecialchars($member['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($member['email'] ?: 'No email') . "</td>";
            echo "<td>" . ($member['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $member['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test email login
    echo "<h3>Test Email Login</h3>";
    if (!empty($members)) {
        foreach ($members as $member) {
            if ($member['email']) {
                echo "<h4>Testing email login for: " . htmlspecialchars($member['email']) . "</h4>";
                
                $stmt = $db->prepare("
                    SELECT * FROM family_members 
                    WHERE (username = ? OR email = ?) AND is_active = 1
                ");
                $stmt->execute([$member['email'], $member['email']]);
                $loginResult = $stmt->fetch();
                
                if ($loginResult) {
                    echo "<p><strong>✓</strong> Email login works - user found: " . htmlspecialchars($loginResult['full_name']) . "</p>";
                } else {
                    echo "<p><strong>✗</strong> Email login failed - user not found or inactive</p>";
                }
            }
        }
    }
    
    // Show photos
    $stmt = $db->query("SELECT COUNT(*) as count FROM family_album_photos");
    $photoCount = $stmt->fetch()['count'];
    echo "<h3>Photos in Database: " . $photoCount . "</h3>";
    
    if ($photoCount > 0) {
        $stmt = $db->query("SELECT id, filename, file_path, uploaded_by_member FROM family_album_photos LIMIT 5");
        $photos = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Filename</th><th>File Path</th><th>Uploaded By Member</th><th>File Exists</th></tr>";
        
        foreach ($photos as $photo) {
            $filePath = __DIR__ . '/../' . $photo['file_path'];
            $fileExists = file_exists($filePath);
            
            echo "<tr>";
            echo "<td>" . $photo['id'] . "</td>";
            echo "<td>" . htmlspecialchars($photo['filename']) . "</td>";
            echo "<td>" . htmlspecialchars($photo['file_path']) . "</td>";
            echo "<td>" . ($photo['uploaded_by_member'] ?: 'Admin') . "</td>";
            echo "<td>" . ($fileExists ? '✓ Yes' : '✗ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='index'>Back to Album</a></p>";
echo "<p><a href='access'>Back to Login</a></p>";
?>