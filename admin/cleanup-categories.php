<?php
/**
 * Cleanup Categories - Remove unwanted categories and update descriptions
 * Run this script to clean up the categories table
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Categories - Naomi Wendot Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #4A1942; border-bottom: 3px solid #D4A017; padding-bottom: 10px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; color: #856404; }
        .info { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 15px 0; border-radius: 5px; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4A1942; color: white; }
        tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-delete { background: #dc3545; color: white; }
        .badge-keep { background: #28a745; color: white; }
        .badge-update { background: #17a2b8; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #D4A017; color: #4A1942; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .btn:hover { opacity: 0.9; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Cleanup Categories</h1>
        <p>This script removes unwanted categories and ensures all valid categories have proper descriptions.</p>

<?php
try {
    $db = getDb();
    
    echo '<div class="info"><strong>📋 Current categories:</strong></div>';
    
    // Get existing categories
    $stmt = $db->query("SELECT id, slug, name, description, display_order FROM categories ORDER BY display_order ASC");
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>ID</th><th>Slug</th><th>Name</th><th>Description</th><th>Order</th></tr>';
    foreach ($existing as $cat) {
        echo '<tr>';
        echo '<td>' . $cat['id'] . '</td>';
        echo '<td><code>' . htmlspecialchars($cat['slug']) . '</code></td>';
        echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
        echo '<td>' . htmlspecialchars($cat['description'] ?? '<em>None</em>') . '</td>';
        echo '<td>' . $cat['display_order'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Define valid categories (the 6 that have frontend pages)
    $validSlugs = ['poems', 'articles', 'daily-inspirations', 'stories', 'testimonies', 'videos'];
    
    // Find categories to delete
    $toDelete = [];
    foreach ($existing as $cat) {
        if (!in_array($cat['slug'], $validSlugs)) {
            $toDelete[] = $cat;
        }
    }
    
    if (!empty($toDelete)) {
        echo '<div class="warning"><strong>⚠️ Found ' . count($toDelete) . ' unwanted categor' . (count($toDelete) > 1 ? 'ies' : 'y') . ' to remove:</strong></div>';
        
        foreach ($toDelete as $cat) {
            // Check if category has posts
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE category_id = :id");
            $checkStmt->execute([':id' => $cat['id']]);
            $postCount = $checkStmt->fetch()['count'];
            
            if ($postCount > 0) {
                echo '<div class="warning">';
                echo '<strong>⚠️ Cannot delete "' . htmlspecialchars($cat['name']) . '" (slug: <code>' . htmlspecialchars($cat['slug']) . '</code>)</strong><br>';
                echo 'This category has ' . $postCount . ' post(s). Please reassign these posts to a valid category first.';
                echo '</div>';
            } else {
                // Delete category
                $deleteStmt = $db->prepare("DELETE FROM categories WHERE id = :id");
                $deleteStmt->execute([':id' => $cat['id']]);
                
                echo '<p><span class="badge badge-delete">DELETED</span> <strong>' . htmlspecialchars($cat['name']) . '</strong> (slug: <code>' . htmlspecialchars($cat['slug']) . '</code>) - No posts found</p>';
            }
        }
    } else {
        echo '<div class="success"><strong>✅ No unwanted categories found!</strong></div>';
    }
    
    // Update descriptions for valid categories
    echo '<div class="info"><strong>📝 Updating descriptions...</strong></div>';
    
    $categoryDescriptions = [
        'poems' => 'Freestyle, heartfelt expressions of faith, hope, and everyday beauty',
        'articles' => 'Thoughtful reflections on life, faith, and personal growth',
        'daily-inspirations' => 'Short, Holy Spirit-inspired messages to start your day with hope',
        'stories' => 'Narratives of faith, courage, and transformation',
        'testimonies' => 'Real stories of God\'s grace, protection, and answered prayer',
        'videos' => 'Short inspirational videos by Naomi'
    ];
    
    $updated = 0;
    foreach ($categoryDescriptions as $slug => $description) {
        $updateStmt = $db->prepare("
            UPDATE categories 
            SET description = :description 
            WHERE slug = :slug AND (description IS NULL OR description = '' OR description = 'N/A')
        ");
        $updateStmt->execute([':slug' => $slug, ':description' => $description]);
        
        if ($updateStmt->rowCount() > 0) {
            echo '<p><span class="badge badge-update">UPDATED</span> Description for <strong>' . ucwords(str_replace('-', ' ', $slug)) . '</strong></p>';
            $updated++;
        }
    }
    
    if ($updated === 0) {
        echo '<p>All categories already have descriptions.</p>';
    }
    
    echo '<div class="success">';
    echo '<strong>✅ Cleanup Complete!</strong><br>';
    echo 'Categories removed: ' . (count($toDelete) - count(array_filter($toDelete, function($c) use ($db) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE category_id = :id");
        $stmt->execute([':id' => $c['id']]);
        return $stmt->fetch()['count'] > 0;
    }))) . '<br>';
    echo 'Descriptions updated: ' . $updated;
    echo '</div>';
    
    echo '<div class="info"><strong>📊 Final category list:</strong></div>';
    
    $stmt = $db->query("SELECT id, slug, name, description, display_order FROM categories ORDER BY display_order ASC");
    $finalCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>Order</th><th>Slug</th><th>Name</th><th>Description</th></tr>';
    foreach ($finalCategories as $cat) {
        $isValid = in_array($cat['slug'], $validSlugs);
        $rowStyle = $isValid ? '' : 'style="background: #fff3cd;"';
        echo '<tr ' . $rowStyle . '>';
        echo '<td>' . $cat['display_order'] . '</td>';
        echo '<td><code>' . htmlspecialchars($cat['slug']) . '</code>' . (!$isValid ? ' <span class="badge badge-delete">INVALID</span>' : '') . '</td>';
        echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
        echo '<td>' . htmlspecialchars($cat['description'] ?? '<em>None</em>') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<div class="success">';
    echo '<p><strong>🎉 Your categories are now clean and consistent!</strong></p>';
    echo '<p><strong>Valid categories (with frontend pages):</strong></p>';
    echo '<ul>';
    echo '<li>✅ Poems → <code>/body-of-work/poems.php</code></li>';
    echo '<li>✅ Articles → <code>/body-of-work/articles.php</code></li>';
    echo '<li>✅ Daily Inspirations → <code>/body-of-work/daily-inspirations.php</code></li>';
    echo '<li>✅ Stories → <code>/body-of-work/stories.php</code></li>';
    echo '<li>✅ Testimonies → <code>/body-of-work/testimonies.php</code></li>';
    echo '<li>✅ Videos → <code>/body-of-work/videos.php</code></li>';
    echo '</ul>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>

        <a href="content/manage.php" class="btn">← Back to Content Management</a>
    </div>
</body>
</html>
