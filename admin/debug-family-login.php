<?php
/**
 * Debug Family Login Issues
 */

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/db.php';

echo "<h2>Family Login Debug</h2>";

try {
    $db = getDb();
    
    // Check if family_members table exists
    $stmt = $db->query("SHOW TABLES LIKE 'family_members'");
    if (!$stmt->fetch()) {
        echo "<p><strong>ERROR:</strong> family_members table does not exist!</p>";
        echo "<p>Run the migration: <a href='migrations/003_family_members.php'>003_family_members.php</a></p>";
        exit;
    }
    
    echo "<p><strong>✓</strong> family_members table exists</p>";
    
    // Show table structure
    $stmt = $db->query("DESCRIBE family_members");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Table Structure</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show all family members
    $stmt = $db->query("SELECT id, username, full_name, email, is_active, created_at FROM family_members ORDER BY created_at DESC");
    $members = $stmt->fetchAll();
    
    echo "<h3>Family Members (" . count($members) . ")</h3>";
    
    if (empty($members)) {
        echo "<p>No family members found!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Active</th><th>Created</th><th>Password Test</th></tr>";
        
        foreach ($members as $member) {
            echo "<tr>";
            echo "<td>" . $member['id'] . "</td>";
            echo "<td>" . htmlspecialchars($member['username']) . "</td>";
            echo "<td>" . htmlspecialchars($member['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($member['email'] ?: 'No email') . "</td>";
            echo "<td>" . ($member['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $member['created_at'] . "</td>";
            
            // Test password hash
            $stmt2 = $db->prepare("SELECT password_hash FROM family_members WHERE id = ?");
            $stmt2->execute([$member['id']]);
            $hash = $stmt2->fetch()['password_hash'];
            
            if ($member['username'] === 'admin') {
                $testPassword = 'family2024';
                $isValid = password_verify($testPassword, $hash);
                echo "<td>Testing 'family2024': " . ($isValid ? "✓ VALID" : "✗ INVALID") . "</td>";
            } else {
                echo "<td>Hash exists: " . (strlen($hash) > 10 ? "✓ Yes" : "✗ No") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test login process
    echo "<h3>Test Login Process</h3>";
    if (!empty($members)) {
        $testMember = $members[0];
        echo "<p>Testing login for: " . htmlspecialchars($testMember['username']) . "</p>";
        
        // Simulate login query
        $stmt = $db->prepare("
            SELECT * FROM family_members 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        $stmt->execute([$testMember['username'], $testMember['username']]);
        $loginResult = $stmt->fetch();
        
        if ($loginResult) {
            echo "<p><strong>✓</strong> Login query works - user found</p>";
            echo "<p>User data: " . htmlspecialchars($loginResult['full_name']) . " (" . $loginResult['username'] . ")</p>";
        } else {
            echo "<p><strong>✗</strong> Login query failed - user not found or inactive</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>