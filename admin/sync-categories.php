<?php
/**
 * Synchronize Categories - Ensure all frontend category pages have database entries
 * Run this script ONCE to add missing categories (Stories, Testimonies, Videos)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Categories - Naomi Wendot Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #4A1942; border-bottom: 3px solid #D4A017; padding-bottom: 10px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24; }
        .info { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 15px 0; border-radius: 5px; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4A1942; color: white; }
        tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-new { background: #28a745; color: white; }
        .badge-existing { background: #17a2b8; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #D4A017; color: #4A1942; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Synchronize Categories</h1>
        <p>This script ensures all frontend category pages have corresponding database entries.</p>

<?php
try {
    $db = getDb();
    
    echo '<div class="info"><strong>📋 Checking existing categories...</strong></div>';
    
    // Get existing categories
    $stmt = $db->query("SELECT id, slug, name, display_order FROM categories ORDER BY display_order ASC");
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>ID</th><th>Slug</th><th>Name</th><th>Display Order</th></tr>';
    foreach ($existing as $cat) {
        echo '<tr>';
        echo '<td>' . $cat['id'] . '</td>';
        echo '<td><code>' . htmlspecialchars($cat['slug']) . '</code></td>';
        echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
        echo '<td>' . $cat['display_order'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<div class="info"><strong>➕ Adding missing categories...</strong></div>';
    
    // Define required categories to match frontend pages and admin dropdown
    $requiredCategories = [
        ['slug' => 'poems', 'name' => 'Poems', 'description' => 'Freestyle, heartfelt expressions of faith, hope, and everyday beauty', 'icon' => '✍️', 'display_order' => 1],
        ['slug' => 'articles', 'name' => 'Articles', 'description' => 'Thoughtful reflections on life, faith, and personal growth', 'icon' => '📖', 'display_order' => 2],
        ['slug' => 'daily-inspirations', 'name' => 'Daily Inspirations', 'description' => 'Short, Holy Spirit-inspired messages to start your day with hope', 'icon' => '☀️', 'display_order' => 3],
        ['slug' => 'stories', 'name' => 'Stories', 'description' => 'Narratives of faith, courage, and transformation', 'icon' => '📜', 'display_order' => 4],
        ['slug' => 'testimonies', 'name' => 'Testimonies', 'description' => 'Real stories of God\'s grace, protection, and answered prayer', 'icon' => '✨', 'display_order' => 5],
        ['slug' => 'videos', 'name' => 'Videos', 'description' => 'Short inspirational videos by Naomi', 'icon' => '🎥', 'display_order' => 6]
    ];
    
    $added = 0;
    $updated = 0;
    
    foreach ($requiredCategories as $cat) {
        // Check if category exists
        $stmt = $db->prepare("SELECT id, display_order FROM categories WHERE slug = :slug");
        $stmt->execute([':slug' => $cat['slug']]);
        $existingCat = $stmt->fetch();
        
        if ($existingCat) {
            // Update display order if different
            if ($existingCat['display_order'] != $cat['display_order']) {
                $updateStmt = $db->prepare("UPDATE categories SET display_order = :order WHERE slug = :slug");
                $updateStmt->execute([':order' => $cat['display_order'], ':slug' => $cat['slug']]);
                echo '<p>✅ Updated <strong>' . htmlspecialchars($cat['name']) . '</strong> display order to ' . $cat['display_order'] . '</p>';
                $updated++;
            } else {
                echo '<p><span class="badge badge-existing">EXISTS</span> ' . htmlspecialchars($cat['name']) . ' (slug: ' . htmlspecialchars($cat['slug']) . ')</p>';
            }
        } else {
            // Insert new category
            $insertStmt = $db->prepare("
                INSERT INTO categories (slug, name, description, icon, display_order, created_at) 
                VALUES (:slug, :name, :description, :icon, :display_order, NOW())
            ");
            $insertStmt->execute([
                ':slug' => $cat['slug'],
                ':name' => $cat['name'],
                ':description' => $cat['description'],
                ':icon' => $cat['icon'],
                ':display_order' => $cat['display_order']
            ]);
            echo '<p><span class="badge badge-new">NEW</span> <strong>' . htmlspecialchars($cat['name']) . '</strong> (slug: ' . htmlspecialchars($cat['slug']) . ') added successfully!</p>';
            $added++;
        }
    }
    
    echo '<div class="success">';
    echo '<strong>✅ Synchronization Complete!</strong><br>';
    echo 'Categories added: ' . $added . '<br>';
    echo 'Categories updated: ' . $updated . '<br>';
    echo 'Total categories: ' . count($requiredCategories);
    echo '</div>';
    
    echo '<div class="info"><strong>📊 Final category list:</strong></div>';
    
    $stmt = $db->query("SELECT id, slug, name, display_order, description FROM categories ORDER BY display_order ASC");
    $finalCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table>';
    echo '<tr><th>Order</th><th>Slug</th><th>Name</th><th>Description</th></tr>';
    foreach ($finalCategories as $cat) {
        echo '<tr>';
        echo '<td>' . $cat['display_order'] . '</td>';
        echo '<td><code>' . htmlspecialchars($cat['slug']) . '</code></td>';
        echo '<td>' . htmlspecialchars($cat['name']) . '</td>';
        echo '<td>' . htmlspecialchars($cat['description'] ?? 'N/A') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<p><strong>🎉 Your frontend category pages are now fully synchronized with the database!</strong></p>';
    echo '<p>You can now:</p>';
    echo '<ul>';
    echo '<li>Create posts in any of these 6 categories from the admin panel</li>';
    echo '<li>Browse posts by visiting dedicated category pages (e.g., /body-of-work/stories.php)</li>';
    echo '<li>Navigation dropdown will show all categories dynamically</li>';
    echo '</ul>';
    
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
