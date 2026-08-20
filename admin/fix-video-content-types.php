<?php
/**
 * Fix Video Content Types
 * Updates posts that have video files but content_type='typed' or NULL
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Video Content Types - Naomi Wendot Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
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
        .badge-fixed { background: #28a745; color: white; }
        .badge-broken { background: #dc3545; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #D4A017; color: #4A1942; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .btn:hover { opacity: 0.9; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Video Content Types</h1>
        <p>This script finds posts with video files but incorrect <code>content_type</code> and fixes them.</p>

<?php
try {
    $db = getDb();
    
    echo '<div class="info"><strong>🔍 Scanning for broken video posts...</strong></div>';
    
    // Find posts that have video_file but content_type is NOT 'video'
    $stmt = $db->query("
        SELECT id, title, slug, video_file, content_type, category_id,
               (SELECT name FROM categories WHERE id = posts.category_id) as category_name
        FROM posts
        WHERE video_file IS NOT NULL 
        AND video_file != ''
        AND (content_type IS NULL OR content_type != 'video')
    ");
    
    $brokenPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($brokenPosts)) {
        echo '<div class="success">';
        echo '<strong>✅ No broken video posts found!</strong><br>';
        echo 'All posts with video files have correct <code>content_type = \'video\'</code>.';
        echo '</div>';
    } else {
        echo '<div class="warning">';
        echo '<strong>⚠️ Found ' . count($brokenPosts) . ' broken video post(s)</strong><br>';
        echo 'These posts have video files but <code>content_type</code> is not set to "video".';
        echo '</div>';
        
        echo '<table>';
        echo '<tr><th>ID</th><th>Title</th><th>Current Type</th><th>Video File</th><th>Category</th></tr>';
        foreach ($brokenPosts as $post) {
            echo '<tr>';
            echo '<td>' . $post['id'] . '</td>';
            echo '<td>' . htmlspecialchars($post['title']) . '</td>';
            echo '<td><span class="badge badge-broken">' . htmlspecialchars($post['content_type'] ?? 'NULL') . '</span></td>';
            echo '<td><code>' . htmlspecialchars(basename($post['video_file'])) . '</code></td>';
            echo '<td>' . htmlspecialchars($post['category_name'] ?? 'None') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<div class="info"><strong>🔨 Fixing posts...</strong></div>';
        
        // Fix each broken post
        $fixed = 0;
        $updateStmt = $db->prepare("
            UPDATE posts 
            SET content_type = 'video' 
            WHERE id = :id
        ");
        
        foreach ($brokenPosts as $post) {
            $updateStmt->execute([':id' => $post['id']]);
            echo '<p><span class="badge badge-fixed">FIXED</span> <strong>' . htmlspecialchars($post['title']) . '</strong> (ID: ' . $post['id'] . ') → <code>content_type = \'video\'</code></p>';
            $fixed++;
        }
        
        echo '<div class="success">';
        echo '<strong>✅ Fixed ' . $fixed . ' video post(s)!</strong><br>';
        echo 'All posts with video files now have <code>content_type = \'video\'</code>.';
        echo '</div>';
    }
    
    // Verification query
    echo '<div class="info"><strong>📊 Verification - All Video Posts:</strong></div>';
    
    $verifyStmt = $db->query("
        SELECT id, title, slug, content_type, video_file IS NOT NULL as has_video_file,
               (SELECT name FROM categories WHERE id = posts.category_id) as category_name
        FROM posts
        WHERE video_file IS NOT NULL AND video_file != ''
        ORDER BY id DESC
    ");
    
    $allVideoPosts = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allVideoPosts)) {
        echo '<p class="text-gray-500">No video posts found in the database.</p>';
    } else {
        echo '<table>';
        echo '<tr><th>ID</th><th>Title</th><th>Content Type</th><th>Has Video File</th><th>Category</th></tr>';
        foreach ($allVideoPosts as $post) {
            $isCorrect = $post['content_type'] === 'video';
            $badge = $isCorrect ? 'badge-fixed' : 'badge-broken';
            $badgeText = $isCorrect ? 'CORRECT' : 'BROKEN';
            
            echo '<tr>';
            echo '<td>' . $post['id'] . '</td>';
            echo '<td><a href="../public/piece-single.php?slug=' . urlencode($post['slug']) . '" target="_blank" style="color:#4A1942; text-decoration:none;">' . htmlspecialchars($post['title']) . '</a></td>';
            echo '<td><span class="badge ' . $badge . '">' . htmlspecialchars($post['content_type']) . '</span></td>';
            echo '<td>' . ($post['has_video_file'] ? '✅ Yes' : '❌ No') . '</td>';
            echo '<td>' . htmlspecialchars($post['category_name'] ?? 'None') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '<div class="success">';
    echo '<p><strong>🎉 All done!</strong></p>';
    echo '<p>Your video posts should now display correctly on:</p>';
    echo '<ul>';
    echo '<li>✅ Homepage (<code>/public/index.php</code>)</li>';
    echo '<li>✅ Videos page (<code>/body-of-work/videos.php</code>)</li>';
    echo '<li>✅ Single post pages (<code>/public/piece-single.php</code>)</li>';
    echo '</ul>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>

        <a href="content/manage.php" class="btn">← Back to Content Management</a>
        <a href="../body-of-work/videos.php" class="btn" target="_blank">View Videos Page →</a>
    </div>
</body>
</html>
