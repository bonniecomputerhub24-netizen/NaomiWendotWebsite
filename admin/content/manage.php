<?php
/**
 * Manage Writing - View, Add, Edit, Delete Posts
 * Self-contained page - handles all CRUD operations
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
requireAdminLogin();

$pageTitle = 'Manage Writing';
$flash = '';
$db = getDb();

// ═══════════════════════════════════════════════════════════════
// HANDLE POST REQUESTS (CREATE, UPDATE, DELETE)
// ═══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        // ─────────────────────────────────────────────────────────
        // CREATE POST
        // ─────────────────────────────────────────────────────────
        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $categoryName = trim($_POST['category_name'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            $scheduledFor = !empty($_POST['scheduled_for']) ? $_POST['scheduled_for'] : null;
            
            // Get author ID from database - ensure admin user exists
            $authorId = null;
            try {
                $stmt = $db->query("SELECT id FROM admin_users WHERE username = 'naomi' LIMIT 1");
                $adminUser = $stmt->fetch();
                if ($adminUser) {
                    $authorId = (int)$adminUser['id'];
                } else {
                    // Create admin user if doesn't exist
                    $stmt = $db->prepare("INSERT INTO admin_users (username, password, email, created_at) VALUES ('naomi', :password, 'info@naomiwendot.com', NOW())");
                    $stmt->execute([':password' => password_hash('naomi2024', PASSWORD_DEFAULT)]);
                    $authorId = (int)$db->lastInsertId();
                }
            } catch (Exception $e) {
                // If admin_users table doesn't exist or has issues, set to NULL
                $authorId = null;
            }
            
            // Get content type
            $contentType = $_POST['content_type'] ?? 'typed';
            $videoDuration = !empty($_POST['video_duration']) ? (int)$_POST['video_duration'] : null;
            $videoOrientation = $_POST['video_orientation'] ?? 'landscape';
            
            // Validate video orientation
            $validOrientations = ['landscape', 'portrait', 'square'];
            if (!in_array($videoOrientation, $validOrientations)) {
                $videoOrientation = 'landscape'; // default fallback
            }
            
            // Handle video file upload
            $videoFilePath = null;
            if ($contentType === 'video' && !empty($_FILES['video_file']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/videos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
                
                if (in_array($ext, $allowedExts)) {
                    $filename = 'video_' . uniqid() . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    // Check file size (100MB limit)
                    if ($_FILES['video_file']['size'] <= 100 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $uploadPath)) {
                            $videoFilePath = 'uploads/videos/' . $filename;
                        }
                    } else {
                        throw new Exception('Video file is too large. Maximum size is 100MB.');
                    }
                } else {
                    throw new Exception('Invalid video file format. Allowed: MP4, WebM, OGG, MOV, AVI');
                }
            }
            
            // Handle video poster (cover frame) upload
            $videoPosterPath = null;
            if ($contentType === 'video' && !empty($_FILES['video_poster']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/video-posters/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = strtolower(pathinfo($_FILES['video_poster']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowedExts)) {
                    $filename = 'poster_' . uniqid() . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['video_poster']['tmp_name'], $uploadPath)) {
                        $videoPosterPath = 'uploads/video-posters/' . $filename;
                    }
                } else {
                    throw new Exception('Invalid poster image format. Allowed: JPG, PNG, WebP');
                }
            }
            
            // Auto-generate slug if empty
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
            }
            
            // Get or create category
            $categoryId = null;
            if (!empty($categoryName)) {
                // Check if category exists
                $stmt = $db->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
                $stmt->execute([':name' => $categoryName]);
                $cat = $stmt->fetch();
                
                if ($cat) {
                    $categoryId = $cat['id'];
                } else {
                    // Create new category
                    $catSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $categoryName), '-'));
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, created_at) VALUES (:name, :slug, NOW())");
                    $stmt->execute([':name' => $categoryName, ':slug' => $catSlug]);
                    $categoryId = $db->lastInsertId();
                }
            }
            
            // Handle featured image upload
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/blog/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imagePath = 'uploads/blog/' . $filename;
                }
            }
            
            // Handle handwritten image upload
            $handwrittenPath = null;
            if (!empty($_FILES['handwritten_image']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/handwritten/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['handwritten_image']['name'], PATHINFO_EXTENSION);
                $filename = 'handwritten_' . uniqid() . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['handwritten_image']['tmp_name'], $uploadPath)) {
                    $handwrittenPath = 'uploads/handwritten/' . $filename;
                }
            }
            
            // Handle content based on type
            if ($contentType === 'video') {
                $content = !empty($content) ? $content : '[Video Content - See Video URL]';
            }
            
            // Determine published_at based on status
            $publishedAt = null;
            if ($status === 'published') {
                $publishedAt = date('Y-m-d H:i:s');
            } elseif ($status === 'scheduled' && $scheduledFor) {
                $publishedAt = date('Y-m-d H:i:s', strtotime($scheduledFor));
            }
            
            // Insert into database
            $stmt = $db->prepare("
                INSERT INTO posts (title, slug, category_id, content, excerpt, featured_image, handwritten_image, video_file, video_poster, video_orientation, video_duration, content_type, status, published_at, scheduled_for, author_id, created_at)
                VALUES (:title, :slug, :category_id, :content, :excerpt, :image, :handwritten, :video_file, :video_poster, :video_orientation, :video_duration, :content_type, :status, :published_at, :scheduled_for, :author_id, NOW())
            ");
            
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':category_id' => $categoryId,
                ':content' => $content,
                ':excerpt' => $excerpt,
                ':image' => $imagePath,
                ':handwritten' => $handwrittenPath,
                ':video_file' => $videoFilePath,
                ':video_poster' => $videoPosterPath,
                ':video_orientation' => $videoOrientation,
                ':video_duration' => $videoDuration,
                ':content_type' => $contentType,
                ':status' => $status,
                ':published_at' => $publishedAt,
                ':scheduled_for' => $scheduledFor,
                ':author_id' => $authorId
            ]);
            
            $flash = '✅ Post created successfully!';
            if ($status === 'published') {
                $flash = '✅ Post published successfully!';
            } elseif ($status === 'scheduled') {
                $flash = '📅 Post scheduled successfully!';
            }
        }
        
        // ─────────────────────────────────────────────────────────
        // UPDATE POST
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $categoryName = trim($_POST['category_name'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            $contentType = $_POST['content_type'] ?? 'typed';
            $videoDuration = !empty($_POST['video_duration']) ? (int)$_POST['video_duration'] : null;
            $videoOrientation = $_POST['video_orientation'] ?? 'landscape';
            
            // Validate video orientation
            $validOrientations = ['landscape', 'portrait', 'square'];
            if (!in_array($videoOrientation, $validOrientations)) {
                $videoOrientation = 'landscape'; // default fallback
            }
            
            // Get or create category
            $categoryId = null;
            if (!empty($categoryName)) {
                $stmt = $db->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
                $stmt->execute([':name' => $categoryName]);
                $cat = $stmt->fetch();
                
                if ($cat) {
                    $categoryId = $cat['id'];
                } else {
                    $catSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $categoryName), '-'));
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, created_at) VALUES (:name, :slug, NOW())");
                    $stmt->execute([':name' => $categoryName, ':slug' => $catSlug]);
                    $categoryId = $db->lastInsertId();
                }
            }
            
            // Handle featured image upload (optional - keeps existing if not uploaded)
            $imageUpdate = '';
            $handwrittenUpdate = '';
            $videoFileUpdate = '';
            $videoPosterUpdate = '';
            $params = [
                ':id' => $id,
                ':title' => $title,
                ':slug' => $slug,
                ':category_id' => $categoryId,
                ':content' => $content,
                ':excerpt' => $excerpt,
                ':status' => $status,
                ':content_type' => $contentType,
                ':video_duration' => $videoDuration,
                ':video_orientation' => $videoOrientation
            ];
            
            // Featured image
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/blog/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imageUpdate = ', featured_image = :image';
                    $params[':image'] = 'uploads/blog/' . $filename;
                }
            }
            
            // Handwritten image
            if (!empty($_FILES['handwritten_image']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/handwritten/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = pathinfo($_FILES['handwritten_image']['name'], PATHINFO_EXTENSION);
                $filename = 'handwritten_' . uniqid() . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['handwritten_image']['tmp_name'], $uploadPath)) {
                    $handwrittenUpdate = ', handwritten_image = :handwritten';
                    $params[':handwritten'] = 'uploads/handwritten/' . $filename;
                }
            }
            
            // Video file
            if (!empty($_FILES['video_file']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/videos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
                
                if (in_array($ext, $allowedExts)) {
                    $filename = 'video_' . uniqid() . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    // Check file size (100MB limit)
                    if ($_FILES['video_file']['size'] <= 100 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $uploadPath)) {
                            $videoFileUpdate = ', video_file = :video_file';
                            $params[':video_file'] = 'uploads/videos/' . $filename;
                        }
                    }
                }
            }
            
            // Video poster (cover frame)
            if (!empty($_FILES['video_poster']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/video-posters/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $ext = strtolower(pathinfo($_FILES['video_poster']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowedExts)) {
                    $filename = 'poster_' . uniqid() . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['video_poster']['tmp_name'], $uploadPath)) {
                        $videoPosterUpdate = ', video_poster = :video_poster';
                        $params[':video_poster'] = 'uploads/video-posters/' . $filename;
                    }
                }
            }
            
            // Update database
            $stmt = $db->prepare("
                UPDATE posts 
                SET title = :title, slug = :slug, category_id = :category_id, 
                    content = :content, excerpt = :excerpt, status = :status, content_type = :content_type,
                    video_duration = :video_duration, video_orientation = :video_orientation $imageUpdate $handwrittenUpdate $videoFileUpdate $videoPosterUpdate,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute($params);
            $flash = '✅ Post updated successfully!';
        }
        
        // ─────────────────────────────────────────────────────────
        // DELETE POST
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Get file paths before deleting
            $stmt = $db->prepare("SELECT featured_image, handwritten_image, video_file, video_poster FROM posts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch();
            
            // Delete from database
            $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            // Delete featured image file if exists
            if (!empty($post['featured_image'])) {
                $imagePath = __DIR__ . '/../../' . $post['featured_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete handwritten image file if exists
            if (!empty($post['handwritten_image'])) {
                $handwrittenPath = __DIR__ . '/../../' . $post['handwritten_image'];
                if (file_exists($handwrittenPath)) {
                    unlink($handwrittenPath);
                }
            }
            
            // Delete video file if exists
            if (!empty($post['video_file'])) {
                $videoFilePath = __DIR__ . '/../../' . $post['video_file'];
                if (file_exists($videoFilePath)) {
                    unlink($videoFilePath);
                }
            }
            
            // Delete video poster if exists
            if (!empty($post['video_poster'])) {
                $videoPosterPath = __DIR__ . '/../../' . $post['video_poster'];
                if (file_exists($videoPosterPath)) {
                    unlink($videoPosterPath);
                }
            }
            
            $flash = '🗑️ Post deleted successfully.';
        }
        
        // Redirect to prevent form resubmission
        $_SESSION['flash_message'] = $flash;
        header('Location: manage.php');
        exit;
        
    } catch (Exception $e) {
        $flash = '❌ Error: ' . htmlspecialchars($e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════
// DISPLAY LOGIC (GET REQUESTS)
// ═══════════════════════════════════════════════════════════════

// Flash messages
if (!empty($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Filters and pagination
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterCat = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Predefined categories
$predefinedCategories = [
    'Poems',
    'Articles',
    'Daily Inspirations',
    'Stories',
    'Testimonies',
    'Videos'
];

// Get all categories from DB
$dbCategories = [];
try {
    $db = getDb();
    $catStmt = $db->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
    $dbCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $dbCategories = [];
}

// Build WHERE clause
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(title LIKE :s OR content LIKE :s)';
    $params[':s'] = '%' . $search . '%';
}
if ($filterCat !== '') {
    $where[] = 'category_id = :cat';
    $params[':cat'] = $filterCat;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$totalRows = 0;
try {
    $c = $db->prepare("SELECT COUNT(*) AS cnt FROM posts $whereSql");
    $c->execute($params);
    $totalRows = (int)($c->fetch()['cnt'] ?? 0);
} catch (Exception $e) {
    $totalRows = 0;
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch posts
$posts = [];
$debugInfo = [];
try {
    $db = getDb();
    
    // Check if video_file column exists (for backward compatibility)
    $columnsQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_file'");
    $hasVideoFile = $columnsQuery->rowCount() > 0;
    $debugInfo['hasVideoFile'] = $hasVideoFile;
    
    // Check if video_thumbnail column exists
    $thumbQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_thumbnail'");
    $hasVideoThumbnail = $thumbQuery->rowCount() > 0;
    $debugInfo['hasVideoThumbnail'] = $hasVideoThumbnail;
    
    // Check if video_duration column exists
    $durationQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_duration'");
    $hasVideoDuration = $durationQuery->rowCount() > 0;
    $debugInfo['hasVideoDuration'] = $hasVideoDuration;
    
    // Check if video_poster column exists
    $posterQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_poster'");
    $hasVideoPoster = $posterQuery->rowCount() > 0;
    $debugInfo['hasVideoPoster'] = $hasVideoPoster;
    
    // Check if video_orientation column exists
    $orientationQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_orientation'");
    $hasVideoOrientation = $orientationQuery->rowCount() > 0;
    $debugInfo['hasVideoOrientation'] = $hasVideoOrientation;
    
    // Check if content_type column exists
    $typeQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'content_type'");
    $hasContentType = $typeQuery->rowCount() > 0;
    $debugInfo['hasContentType'] = $hasContentType;
    
    $videoFileSelect = $hasVideoFile ? ', p.video_file' : ', NULL as video_file';
    $videoThumbSelect = $hasVideoThumbnail ? ', p.video_thumbnail' : ', NULL as video_thumbnail';
    $videoDurationSelect = $hasVideoDuration ? ', p.video_duration' : ', NULL as video_duration';
    $videoPosterSelect = $hasVideoPoster ? ', p.video_poster' : ', NULL as video_poster';
    $videoOrientationSelect = $hasVideoOrientation ? ', p.video_orientation' : ', NULL as video_orientation';
    $contentTypeSelect = $hasContentType ? ', p.content_type' : ', NULL as content_type';
    
    $debugInfo['whereSql'] = $whereSql;
    $debugInfo['params'] = $params;
    
    $s = $db->prepare("
        SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
               p.handwritten_image $videoFileSelect $videoThumbSelect $videoDurationSelect $videoPosterSelect $videoOrientationSelect $contentTypeSelect,
               p.status, p.published_at, p.created_at,
               c.name as category_name, c.id as category_id
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        $whereSql
        ORDER BY p.created_at DESC, p.id DESC
        LIMIT :lim OFFSET :off
    ");
    foreach ($params as $k => $v) {
        $s->bindValue($k, $v, PDO::PARAM_STR);
    }
    $s->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $s->bindValue(':off', $offset, PDO::PARAM_INT);
    $s->execute();
    $posts = $s->fetchAll();
    
    $debugInfo['postCount'] = count($posts);
    $debugInfo['totalRows'] = $totalRows;
} catch (Exception $e) {
    $debugInfo['error'] = $e->getMessage();
    error_log("Fetch posts error: " . $e->getMessage());
    $posts = [];
}

// Output debug info only when ?debug=1 is in URL
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo '<div style="position: fixed; top: 100px; right: 20px; background: white; padding: 20px; border: 3px solid red; max-width: 400px; max-height: 500px; overflow: auto; z-index: 9999; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">';
    echo '<h3 style="color: red; margin: 0 0 10px 0;">🐛 DEBUG INFO</h3>';
    echo '<pre style="font-size: 11px; margin: 0;">';
    print_r($debugInfo);
    if (!empty($posts)) {
        echo "\n\n📄 FIRST POST:\n";
        print_r($posts[0]);
    }
    echo '</pre>';
    echo '<button onclick="this.parentElement.remove()" style="margin-top: 10px; padding: 5px 10px; background: red; color: white; border: none; cursor: pointer;">Close</button>';
    echo '</div>';
}

// Get categories for dropdowns
$categories = [];
try {
    $catStmt = $db->query("SELECT id, name, slug FROM categories ORDER BY display_order ASC, name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

$adminName = getAdminName();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- Quill.js CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">

<style>
.cat-badge {
    display: inline-block;
    padding: .15rem .6rem;
    border-radius: 9999px;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    background: rgba(212, 160, 23, .15);
    color: #C59E4F;
    border: 1px solid rgba(212, 160, 23, .3);
}

.status-badge {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 9999px;
    font-size: .65rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-published { background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC; }
.status-draft { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
.status-scheduled { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; }

/* Quill editor wrapper */
.quill-wrap {
    border: 1px solid #E5E7EB;
    border-radius: .5rem;
    overflow: hidden;
    background: #fff;
}

.quill-wrap .ql-toolbar {
    border: none;
    border-bottom: 1px solid #E5E7EB;
    background: #F9FAFB;
}

.quill-wrap .ql-container {
    border: none;
    font-family: 'Inter', sans-serif;
    font-size: .875rem;
}

.quill-wrap .ql-editor {
    min-height: 280px;
    color: #374151;
    line-height: 1.8;
}

.quill-wrap .ql-editor.ql-blank::before {
    color: #9CA3AF;
    font-style: normal;
}

/* Quill toolbar active state */
.ql-toolbar .ql-active,
.ql-toolbar button:hover {
    color: #D4A017 !important;
}

.ql-toolbar .ql-active .ql-stroke,
.ql-toolbar button:hover .ql-stroke {
    stroke: #D4A017 !important;
}

.ql-toolbar .ql-active .ql-fill,
.ql-toolbar button:hover .ql-fill {
    fill: #D4A017 !important;
}

/* Mobile responsive table */
@media (max-width: 768px) {
    .mobile-hide {
        display: none;
    }
    
    table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-buttons button {
        width: 100%;
        text-align: center;
    }
}
</style>

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-3 alert-auto-dismiss">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($flash); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Toolbar -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <!-- Search & Filter -->
                <form method="get" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1">
                    <input 
                        type="text" 
                        name="q" 
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search posts..."
                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                    >
                    <select 
                        name="category" 
                        class="w-full sm:w-auto rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gold focus:outline-none"
                    >
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filterCat == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="w-full sm:w-auto rounded-lg bg-plum px-4 py-2 text-xs font-semibold text-white hover:bg-gold transition-colors">
                        Filter
                    </button>
                    <?php if ($search || $filterCat): ?>
                        <a href="manage.php" class="text-xs text-gray-400 hover:text-gray-600 text-center sm:text-left py-2 sm:py-0">Clear</a>
                    <?php endif; ?>
                </form>
                
                <!-- Add Button -->
                <button 
                    type="button" 
                    data-modal-target="modal-add"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-gold px-4 py-2 text-sm font-semibold text-white hover:bg-plum transition-colors whitespace-nowrap"
                >
                    + Write New Post
                </button>
            </div>
        </div>
        
        <!-- Posts Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-rose border-b border-gold/20">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider mobile-hide">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider mobile-hide">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider mobile-hide">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-plum uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                    No posts found. Click "Write New Post" to create your first piece!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr class="hover:bg-rose/30 transition-colors">
                                    <td class="px-4 py-3 font-medium max-w-xs">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            <?php echo htmlspecialchars($post['slug']); ?>
                                        </div>
                                        <!-- Mobile: Show category and status here -->
                                        <div class="md:hidden mt-2 flex flex-wrap gap-1">
                                            <?php if (!empty($post['category_name'])): ?>
                                                <span class="cat-badge"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                            <?php endif; ?>
                                            <span class="status-badge status-<?php echo htmlspecialchars($post['status']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($post['status'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 mobile-hide">
                                        <?php if (!empty($post['category_name'])): ?>
                                            <span class="cat-badge"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 mobile-hide">
                                        <span class="status-badge status-<?php echo htmlspecialchars($post['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($post['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 mobile-hide">
                                        <?php echo date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="action-buttons inline-flex gap-1">
                                            <button 
                                                type="button"
                                                class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100"
                                                data-modal-target="modal-view"
                                                data-view='<?php echo htmlspecialchars(json_encode($post), ENT_QUOTES); ?>'
                                            >
                                                View
                                            </button>
                                            <button 
                                                type="button"
                                                class="rounded-lg bg-plum px-2 py-1 text-[11px] font-semibold text-white hover:bg-gold"
                                                data-modal-target="modal-edit"
                                                data-edit='<?php echo htmlspecialchars(json_encode($post), ENT_QUOTES); ?>'
                                            >
                                                Edit
                                            </button>
                                            <button 
                                                type="button"
                                                class="rounded-lg bg-red-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-red-600"
                                                data-modal-target="modal-delete"
                                                data-id="<?php echo (int)$post['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-4 py-3 flex items-center justify-between text-xs text-gray-600 border-t border-gray-100">
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?> — <?php echo $totalRows; ?> post(s)</span>
                    <div class="flex gap-1">
                        <?php
                        $queryParams = [];
                        if ($search) $queryParams[] = 'q=' . urlencode($search);
                        if ($filterCat) $queryParams[] = 'category=' . urlencode($filterCat);
                        $base = 'manage.php?' . ($queryParams ? implode('&', $queryParams) . '&' : '');
                        ?>
                        <?php if ($page > 1): ?>
                            <a href="<?php echo $base; ?>page=<?php echo $page - 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo $base; ?>page=<?php echo $page + 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Modals will be added in next file part -->


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- ADD POST MODAL -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="modal-add" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-4xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Write New Post</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <form method="post" action="" enctype="multipart/form-data" class="space-y-4" id="form-add">
            <input type="hidden" name="action" value="create">
            
            <!-- STEP 1: Content Type Selection -->
            <div class="bg-gradient-to-r from-plum/10 to-gold/10 rounded-lg p-4 mb-6">
                <label class="block text-sm font-semibold text-plum mb-3">
                    📝 What type of content are you creating?
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="flex items-start gap-3 p-4 border-2 border-plum bg-plum/5 rounded-lg cursor-pointer hover:border-gold transition-colors content-type-option">
                        <input type="radio" name="content_type" value="typed" class="mt-1 w-5 h-5 text-plum focus:ring-gold" checked onchange="toggleContentFields()">
                        <div>
                            <p class="font-bold text-plum">⌨️ Type Content</p>
                            <p class="text-xs text-gray-600 mt-1">Write a poem, article, or inspiration using the text editor</p>
                        </div>
                    </label>
                    
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gold transition-colors bg-white content-type-option">
                        <input type="radio" name="content_type" value="handwritten" class="mt-1 w-5 h-5 text-gold focus:ring-gold" onchange="toggleContentFields()">
                        <div>
                            <p class="font-bold text-plum">✍️ Upload Handwritten</p>
                            <p class="text-xs text-gray-600 mt-1">Upload a photo/scan of your handwritten piece</p>
                        </div>
                    </label>
                    
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gold transition-colors bg-white content-type-option">
                        <input type="radio" name="content_type" value="video" class="mt-1 w-5 h-5 text-gold focus:ring-gold" onchange="toggleContentFields()">
                        <div>
                            <p class="font-bold text-plum">🎥 Add Video</p>
                            <p class="text-xs text-gray-600 mt-1">Share a short inspirational video</p>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Title & Slug -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="title" 
                        id="add-title" 
                        required
                        placeholder="Enter your title..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                        oninput="autoSlug(this.value, 'add-slug')"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Slug <span class="text-gray-400 text-xs">(auto-generated)</span>
                    </label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="add-slug"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                    >
                </div>
            </div>
            
            <!-- Category & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="category_name" 
                        id="add-category"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                    >
                        <option value="">Select a category...</option>
                        <optgroup label="Default Categories">
                            <option value="Poems">Poems</option>
                            <option value="Articles">Articles</option>
                            <option value="Daily Inspirations">Daily Inspirations</option>
                            <option value="Stories">Stories</option>
                            <option value="Testimonies">Testimonies</option>
                            <option value="Videos">🎥 Videos (video content only)</option>
                        </optgroup>
                        <?php if (!empty($categories)): ?>
                            <optgroup label="Custom Categories">
                                <?php foreach ($categories as $cat): 
                                    // Don't show default categories twice, and filter out unwanted ones
                                    $validCategories = ['Poems', 'Articles', 'Daily Inspirations', 'Stories', 'Testimonies', 'Videos'];
                                    if (!in_array($cat['name'], $validCategories)):
                                ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?> (custom)
                                    </option>
                                <?php 
                                    endif;
                                endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <option value="__custom__">+ Create New Category</option>
                    </select>
                    <input 
                        type="text" 
                        id="add-category-custom"
                        placeholder="Enter new category name..."
                        class="hidden w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none mt-2"
                    >
                    <p class="text-xs text-gray-500 mt-1.5">💡 <strong>Tip:</strong> "Videos" category is for video content only. Other categories are for written/handwritten content.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Status</label>
                    <select 
                        name="status" 
                        id="add-status"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                        <option value="draft">💾 Save as Draft</option>
                        <option value="published" selected>✅ Publish Now</option>
                        <option value="scheduled">📅 Schedule for Later</option>
                    </select>
                </div>
            </div>
            
            <!-- Scheduled Date (shown when status is scheduled) -->
            <div id="add-schedule-wrap" class="hidden">
                <label class="block text-sm font-semibold text-plum mb-2">Publish Date & Time</label>
                <input 
                    type="datetime-local" 
                    name="scheduled_for" 
                    id="add-scheduled"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
            </div>
            
            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">
                    Excerpt <span class="text-gray-400 text-xs">(short summary for previews)</span>
                </label>
                <textarea 
                    name="excerpt" 
                    rows="2"
                    placeholder="Brief description that appears in listings..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                ></textarea>
            </div>
            
            <!-- Content Editor (for typed content) -->
            <div id="typed-content-section">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Content <span class="text-red-500">*</span>
                </label>
                <div class="quill-wrap">
                    <div id="add-content-editor"></div>
                </div>
                <textarea name="content" id="add-content" class="hidden"></textarea>
                <p class="text-xs text-gray-500 mt-2">Use the editor above to format your content</p>
            </div>
            
            <!-- Handwritten Image Upload (for handwritten content) -->
            <div id="handwritten-content-section" class="hidden">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Handwritten Content Image <span class="text-red-500">*</span>
                </label>
                <input 
                    type="file" 
                    name="handwritten_image" 
                    id="add-handwritten-required"
                    accept="image/*,application/pdf" 
                    data-preview="add-handwritten-preview"
                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-plum file:text-white hover:file:bg-gold"
                >
                <img id="add-handwritten-preview" src="" alt="" class="hidden mt-3 h-48 w-64 object-cover rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mt-2">✍️ Upload a clear photo or scan of your handwritten piece</p>
                
                <!-- Optional transcription -->
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Optional Transcription <span class="text-gray-400 text-xs">(helps with searchability)</span>
                    </label>
                    <textarea 
                        name="transcription" 
                        id="add-transcription"
                        rows="4"
                        placeholder="Type what you wrote by hand (optional)..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    ></textarea>
                </div>
            </div>
            
            <!-- Video Content Upload (for video content) -->
            <div id="video-content-section" class="hidden">
                <!-- Video Upload Section -->
                <div id="video-upload-section">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Upload Video File <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="file" 
                        name="video_file" 
                        id="add-video-file"
                        accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo"
                        class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-plum file:text-white hover:file:bg-gold mb-2"
                    >
                    <p class="text-xs text-gray-500 mb-4">
                        📤 Supported formats: MP4, WebM, OGG, MOV, AVI (Max size: 100MB recommended)
                    </p>
                    
                    <!-- File size display -->
                    <div id="add-video-file-info" class="hidden text-xs text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="add-video-file-name"></span>
                    </div>
                    
                    <!-- File size guidance -->
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg mb-4">
                        <p class="text-xs text-amber-900">
                            <strong>💡 Best practices for smooth playback:</strong> Aim to keep videos under 20-30MB where possible for the best visitor experience (especially on mobile). Most phone videos can be trimmed or exported at a lower resolution before uploading. Larger files will still upload successfully but may load slowly for visitors on mobile data.
                        </p>
                    </div>
                    <div id="video-upload-preview" class="hidden mb-4">
                        <video id="video-preview-player" controls class="w-full max-h-64 rounded-lg bg-black">
                            <source src="" type="video/mp4">
                            Your browser does not support video preview.
                        </video>
                        <p class="text-xs text-gray-500 mt-2">Preview of uploaded video</p>
                        
                        <!-- Cover Frame Capture -->
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-plum mb-2">📸 Cover Frame</h4>
                                    <p class="text-xs text-gray-600 mb-3">Pause the video at your preferred moment, then click the button to capture that frame as the video thumbnail.</p>
                                    <button 
                                        type="button" 
                                        id="capture-cover-frame-btn"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gold text-white text-sm font-semibold rounded-lg hover:bg-plum transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Choose Cover Frame
                                    </button>
                                </div>
                                <div id="cover-frame-preview-container" class="hidden">
                                    <img id="cover-frame-preview" src="" alt="Cover frame preview" class="w-32 h-20 object-cover rounded-lg border-2 border-green-500 shadow-sm">
                                    <p class="text-xs text-green-600 font-semibold mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cover frame selected
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden canvas for frame capture -->
                        <canvas id="cover-frame-canvas" style="display: none;"></canvas>
                        
                        <!-- Hidden file input for cover frame -->
                        <input type="file" name="video_poster" id="add-video-poster" style="display: none;">
                        
                        <!-- Hidden orientation input -->
                        <input type="hidden" name="video_orientation" id="add-video-orientation" value="landscape">
                    </div>
                </div>
                
                <!-- Video Duration -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Duration (seconds) <span class="text-gray-400 text-xs">(optional - auto-detected for uploaded videos)</span>
                    </label>
                    <input 
                        type="number" 
                        name="video_duration" 
                        id="add-video-duration"
                        placeholder="120"
                        min="1"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                    <p class="text-xs text-gray-500 mt-1">⏱️ Enter video length in seconds (e.g., 90 for 1:30)</p>
                </div>
                
                <!-- Optional description for video -->
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Video Description <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <textarea 
                        name="video_description" 
                        id="add-video-description"
                        rows="3"
                        placeholder="Describe what this video is about..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">💡 This helps viewers know what to expect</p>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>✨ Cover frame auto-captured!</strong> After uploading, pause the video at your preferred moment and click "Choose Cover Frame" to capture that frame as the thumbnail.
                    </p>
                </div>
            </div>
            
            <!-- Featured Image (optional for all types) -->
            <div id="featured-image-section">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Featured Image <span class="text-gray-400 text-xs">(optional thumbnail)</span>
                </label>
                <input 
                    type="file" 
                    name="image" 
                    accept="image/*" 
                    data-preview="add-img-preview"
                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gold file:text-white hover:file:bg-plum"
                >
                <img id="add-img-preview" src="" alt="" class="hidden mt-3 h-32 w-48 object-cover rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mt-1">Used as thumbnail in listings (not required for handwritten pieces)</p>
            </div>
            
            <!-- Submit Buttons -->
            <div class="pt-4 flex justify-end gap-2 border-t border-gray-200">
                <button 
                    type="button" 
                    data-modal-close
                    class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-lg bg-plum text-sm font-semibold text-white hover:bg-gold transition-colors"
                >
                    Publish Post
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- EDIT POST MODAL -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="modal-edit" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-4xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Edit Post</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <form method="post" action="" enctype="multipart/form-data" class="space-y-4" id="form-edit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            
            <!-- Content Type Selection -->
            <div>
                <label class="block text-sm font-semibold text-plum mb-3">
                    📝 Content Type
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="flex items-start gap-3 p-4 border-2 border-plum bg-plum/5 rounded-lg cursor-pointer hover:border-gold transition-colors content-type-option">
                        <input type="radio" name="content_type" value="typed" class="mt-1 w-5 h-5 text-plum focus:ring-gold" checked onchange="toggleEditContentFields()">
                        <div>
                            <p class="font-bold text-plum">⌨️ Type Content</p>
                            <p class="text-xs text-gray-600 mt-1">Typed text using editor</p>
                        </div>
                    </label>
                    
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gold transition-colors bg-white content-type-option">
                        <input type="radio" name="content_type" value="handwritten" class="mt-1 w-5 h-5 text-gold focus:ring-gold" onchange="toggleEditContentFields()">
                        <div>
                            <p class="font-bold text-plum">✍️ Handwritten</p>
                            <p class="text-xs text-gray-600 mt-1">Scanned handwritten piece</p>
                        </div>
                    </label>
                    
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gold transition-colors bg-white content-type-option">
                        <input type="radio" name="content_type" value="video" class="mt-1 w-5 h-5 text-gold focus:ring-gold" onchange="toggleEditContentFields()">
                        <div>
                            <p class="font-bold text-plum">🎥 Video</p>
                            <p class="text-xs text-gray-600 mt-1">Video content</p>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Title & Slug -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Title <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        id="edit-title" 
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Slug</label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="edit-slug"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                </div>
            </div>
            
            <!-- Category & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="category_name" 
                        id="edit-category"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                    >
                        <option value="">Select a category...</option>
                        <optgroup label="Default Categories">
                            <option value="Poems">Poems</option>
                            <option value="Articles">Articles</option>
                            <option value="Daily Inspirations">Daily Inspirations</option>
                            <option value="Stories">Stories</option>
                            <option value="Testimonies">Testimonies</option>
                            <option value="Videos">🎥 Videos (video content only)</option>
                        </optgroup>
                        <?php if (!empty($categories)): ?>
                            <optgroup label="Custom Categories">
                                <?php foreach ($categories as $cat): 
                                    $validCategories = ['Poems', 'Articles', 'Daily Inspirations', 'Stories', 'Testimonies', 'Videos'];
                                    if (!in_array($cat['name'], $validCategories)):
                                ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?> (custom)
                                    </option>
                                <?php 
                                    endif;
                                endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1.5">💡 <strong>Tip:</strong> "Videos" category is for video content only.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Status</label>
                    <select 
                        name="status" 
                        id="edit-status"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                        <option value="draft">💾 Draft</option>
                        <option value="published">✅ Published</option>
                        <option value="scheduled">📅 Scheduled</option>
                    </select>
                </div>
            </div>
            
            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">Excerpt</label>
                <textarea 
                    name="excerpt" 
                    id="edit-excerpt"
                    rows="2"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                ></textarea>
            </div>
            
            <!-- Content Editor (for non-video posts) -->
            <div id="edit-content-section">
                <label class="block text-sm font-semibold text-plum mb-2">Content</label>
                <div class="quill-wrap">
                    <div id="edit-content-editor"></div>
                </div>
                <textarea name="content" id="edit-content" class="hidden"></textarea>
                <input type="hidden" name="content_type" id="edit-content-type" value="typed">
            </div>
            
            <!-- Featured Image (for non-video posts) -->
            <div id="edit-featured-image-section">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Featured Image <span class="text-gray-400 text-xs">(optional - main thumbnail)</span>
                </label>
                <input 
                    type="file" 
                    name="image" 
                    accept="image/*" 
                    data-preview="edit-img-preview"
                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gold file:text-white hover:file:bg-plum"
                >
                <img id="edit-img-preview" src="" alt="" class="hidden mt-3 h-32 w-48 object-cover rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mt-1">Used as the main image for posts - suitable for all content</p>
            </div>
            
            <!-- Handwritten Image (for non-video posts) -->
            <div id="edit-handwritten-section">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Handwritten Content Image <span class="text-gray-400 text-xs">(optional - for authenticity)</span>
                </label>
                <input 
                    type="file" 
                    name="handwritten_image" 
                    accept="image/*,application/pdf" 
                    data-preview="edit-handwritten-preview"
                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-plum file:text-white hover:file:bg-gold"
                >
                <img id="edit-handwritten-preview" src="" alt="" class="hidden mt-3 h-32 w-48 object-cover rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mt-1">✍️ Upload a scan/photo of handwritten piece - adds authenticity & personal touch</p>
            </div>
            
            <!-- Video Duration (for video posts) -->
            <div id="edit-video-duration-section" class="hidden">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Video Duration (seconds) <span class="text-gray-400 text-xs">(optional)</span>
                </label>
                <input 
                    type="number" 
                    name="video_duration" 
                    id="edit-video-duration"
                    min="1"
                    placeholder="e.g., 90 for 1:30 minutes"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
                <p class="text-xs text-gray-500 mt-1">⏱️ Enter video length in seconds</p>
            </div>
            
            <!-- Replace Video File (for video posts) -->
            <div id="edit-video-file-section" class="hidden">
                <label class="block text-sm font-semibold text-plum mb-2">
                    Replace Video File <span class="text-gray-400 text-xs">(optional - leave empty to keep current video)</span>
                </label>
                <input 
                    type="file" 
                    name="video_file" 
                    id="edit-video-file"
                    accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo"
                    class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-plum file:text-white hover:file:bg-gold"
                >
                <p class="text-xs text-gray-500 mt-1">📤 Upload a new video to replace the current one (Max: 100MB)</p>
            </div>
            
            <!-- Video Preview & Cover Frame Capture (for video posts) -->
            <div id="edit-video-preview-section" class="hidden">
                <!-- Current Video Preview -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Video Preview
                    </label>
                    <video id="edit-video-preview-player" controls class="w-full max-h-64 rounded-lg bg-black">
                        <source src="" type="video/mp4">
                        Your browser does not support video preview.
                    </video>
                </div>
                
                <!-- Current Cover Frame Reference -->
                <div id="edit-current-poster-section" class="hidden mb-4">
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Current Cover Frame
                    </label>
                    <img id="edit-current-poster-img" src="" alt="Current cover frame" class="h-32 w-48 object-cover rounded-lg border-2 border-gray-300">
                    <p class="text-xs text-gray-500 mt-1">Currently saved thumbnail</p>
                </div>
                
                <!-- Cover Frame Capture UI -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-plum mb-2">📸 Update Cover Frame</h4>
                            <p class="text-xs text-gray-600 mb-3">Pause the video at your preferred moment, then click the button to capture that frame as the new thumbnail.</p>
                            <button 
                                type="button" 
                                id="edit-capture-cover-frame-btn"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gold text-white text-sm font-semibold rounded-lg hover:bg-plum transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Choose Cover Frame
                            </button>
                        </div>
                        <div id="edit-cover-frame-preview-container" class="hidden">
                            <img id="edit-cover-frame-preview" src="" alt="New cover frame preview" class="w-32 h-20 object-cover rounded-lg border-2 border-green-500 shadow-sm">
                            <p class="text-xs text-green-600 font-semibold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                New frame selected
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden canvas for frame capture -->
                <canvas id="edit-cover-frame-canvas" style="display: none;"></canvas>
                
                <!-- Hidden file input for cover frame -->
                <input type="file" name="video_poster" id="edit-video-poster" style="display: none;">
                
                <!-- Hidden orientation input -->
                <input type="hidden" name="video_orientation" id="edit-video-orientation" value="landscape">
                
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>✨ Cover frame system!</strong> Use the "Choose Cover Frame" button above to capture your preferred thumbnail from the video.
                    </p>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="pt-4 flex justify-end gap-2 border-t border-gray-200">
                <button 
                    type="button" 
                    data-modal-close
                    class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-lg bg-plum text-sm font-semibold text-white hover:bg-gold transition-colors"
                >
                    Update Post
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW MODAL -->
<div id="modal-view" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-3xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Post Preview</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <span id="view-category" class="cat-badge hidden"></span>
                <span id="view-status" class="status-badge hidden"></span>
            </div>
            <h3 id="view-title" class="text-2xl font-bold text-plum font-playfair"></h3>
            <p class="text-sm text-gray-500">
                Slug: <span id="view-slug" class="font-mono"></span> • 
                <span id="view-date"></span>
            </p>
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-plum mb-2">Excerpt</h4>
                <p id="view-excerpt" class="text-sm text-gray-700"></p>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-plum mb-2">Content</h4>
                <div id="view-content" class="prose prose-sm max-w-none"></div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="modal-delete" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-md w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Confirm Deletion</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <p class="text-sm text-gray-600 mb-2">Are you sure you want to delete this post?</p>
        <p id="delete-title" class="text-sm font-semibold text-red-600 mb-4"></p>
        
        <form method="post" action="" class="flex justify-end gap-2">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete-id">
            <button 
                type="button" 
                data-modal-close
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100"
            >
                Cancel
            </button>
            <button 
                type="submit"
                class="px-4 py-2 rounded-lg bg-red-500 text-sm font-semibold text-white hover:bg-red-600"
            >
                Delete
            </button>
        </form>
    </div>
</div>

<!-- Quill.js -->
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>

<script>
// ══════════════════════════════════════════════════════════════════
// Modal System
// ══════════════════════════════════════════════════════════════════
document.addEventListener('click', function(e) {
    // Open modal
    const modalTrigger = e.target.closest('[data-modal-target]');
    if (modalTrigger) {
        const modalId = modalTrigger.getAttribute('data-modal-target');
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.modal-panel').classList.remove('opacity-0', 'scale-95');
            }, 10);
        }
    }
    
    // Close modal
    const closeBtn = e.target.closest('[data-modal-close]');
    const backdrop = e.target.closest('[data-modal-backdrop]');
    if (closeBtn || backdrop) {
        const modal = e.target.closest('[data-modal]');
        if (modal) {
            const panel = modal.querySelector('.modal-panel');
            panel.classList.add('opacity-0', 'scale-95');
            setTimeout(() => modal.classList.add('hidden'), 150);
        }
    }
});

// ══════════════════════════════════════════════════════════════════
// Content Type Toggle
// ══════════════════════════════════════════════════════════════════
function toggleContentFields() {
    const contentType = document.querySelector('input[name="content_type"]:checked').value;
    const typedSection = document.getElementById('typed-content-section');
    const handwrittenSection = document.getElementById('handwritten-content-section');
    const videoSection = document.getElementById('video-content-section');
    const handwrittenInput = document.getElementById('add-handwritten-required');
    const videoFileInput = document.getElementById('add-video-file');
    
    // Hide all sections first
    typedSection.classList.add('hidden');
    handwrittenSection.classList.add('hidden');
    videoSection.classList.add('hidden');
    
    // Reset required fields
    handwrittenInput.required = false;
    if (videoFileInput) videoFileInput.required = false;
    
    // Reset border colors
    document.querySelectorAll('.content-type-option').forEach(opt => {
        opt.classList.remove('border-plum', 'border-gold', 'bg-plum/5', 'bg-gold/5');
        opt.classList.add('border-gray-300', 'bg-white');
    });
    
    if (contentType === 'typed') {
        typedSection.classList.remove('hidden');
        document.querySelectorAll('input[name="content_type"]')[0].closest('.content-type-option').classList.add('border-plum', 'bg-plum/5');
        document.querySelectorAll('input[name="content_type"]')[0].closest('.content-type-option').classList.remove('bg-white');
    } else if (contentType === 'handwritten') {
        handwrittenSection.classList.remove('hidden');
        handwrittenInput.required = true;
        document.querySelectorAll('input[name="content_type"]')[1].closest('.content-type-option').classList.add('border-gold', 'bg-gold/5');
        document.querySelectorAll('input[name="content_type"]')[1].closest('.content-type-option').classList.remove('bg-white');
    } else if (contentType === 'video') {
        videoSection.classList.remove('hidden');
        // Video file upload is always required for video content
        const videoFileInput = document.getElementById('add-video-file');
        if (videoFileInput) videoFileInput.required = true;
        document.querySelectorAll('input[name="content_type"]')[2].closest('.content-type-option').classList.add('border-gold', 'bg-gold/5');
        document.querySelectorAll('input[name="content_type"]')[2].closest('.content-type-option').classList.remove('bg-white');
    }
}

// ══════════════════════════════════════════════════════════════════
// Content Type Toggle for Edit Modal
// ══════════════════════════════════════════════════════════════════
function toggleEditContentFields() {
    const contentType = document.querySelector('#modal-edit input[name="content_type"]:checked')?.value || 'typed';
    
    // Edit modal doesn't have section toggling like Add modal
    // Just update the visual selection state
    document.querySelectorAll('#modal-edit .content-type-option').forEach(opt => {
        opt.classList.remove('border-plum', 'border-gold', 'bg-plum/5', 'bg-gold/5');
        opt.classList.add('border-gray-300', 'bg-white');
    });
    
    const selectedRadio = document.querySelector(`#modal-edit input[name="content_type"][value="${contentType}"]`);
    if (selectedRadio) {
        const container = selectedRadio.closest('.content-type-option');
        if (contentType === 'typed') {
            container.classList.add('border-plum', 'bg-plum/5');
        } else {
            container.classList.add('border-gold', 'bg-gold/5');
        }
        container.classList.remove('bg-white');
    }
}

// Video file preview with cover frame capture and orientation detection
document.addEventListener('DOMContentLoaded', function() {
    const videoFileInput = document.getElementById('add-video-file');
    if (videoFileInput) {
        videoFileInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('video-upload-preview');
            const videoPlayer = document.getElementById('video-preview-player');
            const fileInfoContainer = document.getElementById('add-video-file-info');
            const fileNameDisplay = document.getElementById('add-video-file-name');
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileURL = URL.createObjectURL(file);
                
                // Display file name and size
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
                const sizeClass = fileSizeMB > 30 ? 'text-amber-700 font-semibold' : 'text-gray-700';
                fileNameDisplay.innerHTML = `<strong>Selected:</strong> <span class="${sizeClass}">${file.name} (${fileSizeMB} MB)</span>`;
                fileInfoContainer.classList.remove('hidden');
                
                videoPlayer.src = fileURL;
                previewContainer.classList.remove('hidden');
                
                // Reset cover frame capture state
                const coverFramePreviewContainer = document.getElementById('cover-frame-preview-container');
                if (coverFramePreviewContainer) {
                    coverFramePreviewContainer.classList.add('hidden');
                }
                
                // Auto-detect duration and orientation
                videoPlayer.addEventListener('loadedmetadata', function() {
                    // Duration detection
                    const durationInput = document.getElementById('add-video-duration');
                    if (durationInput && !durationInput.value) {
                        durationInput.value = Math.round(videoPlayer.duration);
                    }
                    
                    // Orientation detection
                    const orientationInput = document.getElementById('add-video-orientation');
                    if (orientationInput) {
                        const videoWidth = videoPlayer.videoWidth;
                        const videoHeight = videoPlayer.videoHeight;
                        
                        let orientation = 'landscape'; // default
                        if (videoHeight > videoWidth) {
                            orientation = 'portrait';
                        } else if (videoHeight === videoWidth) {
                            orientation = 'square';
                        }
                        
                        orientationInput.value = orientation;
                        console.log('Video orientation detected:', orientation, `(${videoWidth}x${videoHeight})`);
                    }
                    
                    // AUTO-CAPTURE: Generate fallback poster if none captured manually yet
                    // This ensures every video has a poster, preventing black boxes
                    setTimeout(function() {
                        const posterInput = document.getElementById('add-video-poster');
                        if (posterInput && !posterInput.files.length) {
                            console.log('Auto-capturing fallback cover frame...');
                            autoCaptureCoverFrame(videoPlayer, 1); // Capture at 1 second
                        }
                    }, 500); // Small delay to ensure video is ready
                });
            } else {
                // Hide file info if no file selected
                if (fileInfoContainer) {
                    fileInfoContainer.classList.add('hidden');
                }
            }
        });
    }
    
    // ══════════════════════════════════════════════════════════════════
    // Cover Frame Capture - Reusable Function
    // ══════════════════════════════════════════════════════════════════
    function captureCoverFrame(videoPlayer, canvasId, posterInputId, previewImgId, previewContainerId, seekTime = null) {
        const canvas = document.getElementById(canvasId);
        const coverFramePreview = document.getElementById(previewImgId);
        const coverFramePreviewContainer = document.getElementById(previewContainerId);
        const videoPosterInput = document.getElementById(posterInputId);
        
        if (!videoPlayer || !canvas || videoPlayer.readyState < 2) {
            console.warn('Video not ready for frame capture');
            return false;
        }
        
        // If seekTime is provided and different from current time, seek first
        if (seekTime !== null && Math.abs(videoPlayer.currentTime - seekTime) > 0.1) {
            videoPlayer.currentTime = seekTime;
            // Wait for seek to complete
            return new Promise((resolve) => {
                videoPlayer.addEventListener('seeked', function captureAfterSeek() {
                    videoPlayer.removeEventListener('seeked', captureAfterSeek);
                    performCapture();
                    resolve(true);
                }, { once: true });
            });
        } else {
            performCapture();
            return true;
        }
        
        function performCapture() {
            // Set canvas dimensions to match video's native dimensions
            canvas.width = videoPlayer.videoWidth;
            canvas.height = videoPlayer.videoHeight;
            
            // Draw current video frame to canvas
            const ctx = canvas.getContext('2d');
            ctx.drawImage(videoPlayer, 0, 0, canvas.width, canvas.height);
            
            // Convert canvas to blob
            canvas.toBlob(function(blob) {
                if (!blob) {
                    console.error('Failed to capture frame');
                    return;
                }
                
                // Create a File object from the blob
                const timestamp = Date.now();
                const file = new File([blob], `cover-frame-${timestamp}.jpg`, { 
                    type: 'image/jpeg',
                    lastModified: timestamp
                });
                
                // Use DataTransfer to assign the file to the hidden input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                videoPosterInput.files = dataTransfer.files;
                
                // Show preview
                const previewURL = URL.createObjectURL(blob);
                coverFramePreview.src = previewURL;
                coverFramePreviewContainer.classList.remove('hidden');
                
                console.log('Cover frame captured:', file.name, `(${canvas.width}x${canvas.height})`);
            }, 'image/jpeg', 0.92);
        }
    }
    
    // Auto-capture function for fallback poster
    function autoCaptureCoverFrame(videoPlayer, seekTime) {
        captureCoverFrame(
            videoPlayer,
            'cover-frame-canvas',
            'add-video-poster',
            'cover-frame-preview',
            'cover-frame-preview-container',
            seekTime
        );
    }
    
    // Manual cover frame capture button (Add modal)
    const captureCoverFrameBtn = document.getElementById('capture-cover-frame-btn');
    if (captureCoverFrameBtn) {
        captureCoverFrameBtn.addEventListener('click', function() {
            const videoPlayer = document.getElementById('video-preview-player');
            
            if (!videoPlayer || videoPlayer.readyState < 2) {
                alert('Please wait for the video to load before capturing a frame.');
                return;
            }
            
            captureCoverFrame(
                videoPlayer,
                'cover-frame-canvas',
                'add-video-poster',
                'cover-frame-preview',
                'cover-frame-preview-container'
            );
        });
    }
    
    // Manual cover frame capture button (Edit modal)
    const editCaptureCoverFrameBtn = document.getElementById('edit-capture-cover-frame-btn');
    if (editCaptureCoverFrameBtn) {
        editCaptureCoverFrameBtn.addEventListener('click', function() {
            const videoPlayer = document.getElementById('edit-video-preview-player');
            
            if (!videoPlayer || videoPlayer.readyState < 2) {
                alert('Please wait for the video to load before capturing a frame.');
                return;
            }
            
            captureCoverFrame(
                videoPlayer,
                'edit-cover-frame-canvas',
                'edit-video-poster',
                'edit-cover-frame-preview',
                'edit-cover-frame-preview-container'
            );
        });
    }
});

// ══════════════════════════════════════════════════════════════════
// Custom Category Input
// ══════════════════════════════════════════════════════════════════
document.getElementById('add-category').addEventListener('change', function() {
    const customInput = document.getElementById('add-category-custom');
    if (this.value === '__custom__') {
        customInput.classList.remove('hidden');
        customInput.required = true;
        customInput.focus();
    } else {
        customInput.classList.add('hidden');
        customInput.required = false;
    }
});

// Category → Content Type Validation
function validateCategoryContentType(categorySelect, contentTypeInputs, formType) {
    const category = categorySelect.value;
    const selectedContentType = Array.from(contentTypeInputs).find(input => input.checked)?.value;
    
    // Rule: "Videos" category MUST use content_type = 'video'
    if (category === 'Videos' && selectedContentType !== 'video') {
        alert('⚠️ The "Videos" category can only be used with video content.\n\nPlease select "🎥 Add Video" as your content type, or choose a different category.');
        return false;
    }
    
    // Rule: Video content_type should use "Videos" category (warning, not blocking)
    if (selectedContentType === 'video' && category !== 'Videos') {
        const proceed = confirm('💡 You\'re creating video content but selected the "' + category + '" category.\n\nVideo content is typically categorized as "Videos" so it appears on the Videos page.\n\nDo you want to continue anyway?');
        if (!proceed) return false;
    }
    
    return true;
}

// Before form submit, use custom category value if selected
document.getElementById('form-add').addEventListener('submit', function(e) {
    const categorySelect = document.getElementById('add-category');
    const contentTypeInputs = document.querySelectorAll('input[name="content_type"]');
    
    // Validate category-content type match
    if (!validateCategoryContentType(categorySelect, contentTypeInputs, 'add')) {
        e.preventDefault();
        return false;
    }
    const customInput = document.getElementById('add-category-custom');
    
    if (categorySelect.value === '__custom__' && customInput.value.trim()) {
        // Create a hidden input with the custom category name
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'category_name';
        hiddenInput.value = customInput.value.trim();
        this.appendChild(hiddenInput);
        
        // Remove the name attribute from the select to avoid conflict
        categorySelect.removeAttribute('name');
    }
    
    // Handle content based on type
    const contentType = document.querySelector('input[name="content_type"]:checked').value;
    const contentTextarea = document.getElementById('add-content');
    
    if (contentType === 'handwritten') {
        // For handwritten, use transcription as content if provided
        const transcription = document.getElementById('add-transcription').value;
        contentTextarea.value = transcription || '[Handwritten Content - See Image]';
    } else if (contentType === 'video') {
        // For video, use video description as content if provided
        const videoDescription = document.getElementById('add-video-description').value;
        const videoFile = document.getElementById('add-video-file');
        
        // Validate video file upload
        if (!videoFile.files || videoFile.files.length === 0) {
            e.preventDefault();
            alert('Please upload a video file.');
            return false;
        }
        
        contentTextarea.value = videoDescription || '[Video Content - See Video]';
    } else {
        // For typed content, get from Quill editor
        const quillContent = quillAdd.root.innerHTML;
        const quillText = quillAdd.getText().trim();
        
        // Validate typed content has actual text
        if (quillText.length === 0) {
            e.preventDefault();
            alert('Please write some content before publishing.');
            return false;
        }
        
        contentTextarea.value = quillContent;
    }
});

// ══════════════════════════════════════════════════════════════════
// Slug Auto-Generator
// ══════════════════════════════════════════════════════════════════
function autoSlug(val, targetId) {
    const el = document.getElementById(targetId);
    if (!el || el.dataset.manual) return;
    el.value = val.toLowerCase().trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

// Mark slug as manually edited
document.getElementById('add-slug').addEventListener('input', function() {
    this.dataset.manual = '1';
});

// ══════════════════════════════════════════════════════════════════
// Quill Editors
// ══════════════════════════════════════════════════════════════════
const quillToolbar = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'code-block'],
    ['link'],
    [{ align: [] }],
    ['clean']
];

const quillAdd = new Quill('#add-content-editor', {
    theme: 'snow',
    placeholder: 'Start writing your heart out...',
    modules: { toolbar: quillToolbar }
});

const quillEdit = new Quill('#edit-content-editor', {
    theme: 'snow',
    placeholder: 'Edit your content...',
    modules: { toolbar: quillToolbar }
});

// Sync Quill to textarea on submit (already handled in custom submit above)
document.getElementById('form-edit').addEventListener('submit', function(e) {
    // Sync Quill editor content
    document.getElementById('edit-content').value = quillEdit.root.innerHTML;
    
    // Validate category-content type match
    const categoryInput = document.getElementById('edit-category');
    const contentTypeInputs = document.querySelectorAll('#modal-edit input[name="content_type"]');
    
    if (!validateCategoryContentType(categoryInput, contentTypeInputs, 'edit')) {
        e.preventDefault();
        return false;
    }
});

// ══════════════════════════════════════════════════════════════════
// Schedule Date Toggle
// ══════════════════════════════════════════════════════════════════
document.getElementById('add-status').addEventListener('change', function() {
    const wrap = document.getElementById('add-schedule-wrap');
    const input = document.getElementById('add-scheduled');
    if (this.value === 'scheduled') {
        wrap.classList.remove('hidden');
        input.required = true;
    } else {
        wrap.classList.add('hidden');
        input.required = false;
    }
});

// ══════════════════════════════════════════════════════════════════
// Image Preview
// ══════════════════════════════════════════════════════════════════
document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    input.addEventListener('change', function() {
        const previewId = this.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        if (this.files && this.files[0] && preview) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// ══════════════════════════════════════════════════════════════════
// Populate Edit Modal
// ══════════════════════════════════════════════════════════════════
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('[data-edit]');
    if (editBtn) {
        const data = JSON.parse(editBtn.getAttribute('data-edit'));
        // Fix base path - we're in /admin/content/, need to go up 2 levels to site root
        const basePath = '../../';
        const isVideoPost = data.content_type === 'video' && data.video_file;
        
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-title').value = data.title;
        document.getElementById('edit-slug').value = data.slug;
        
        // Set category - ensure exact match with dropdown options
        const categorySelect = document.getElementById('edit-category');
        const categoryValue = data.category_name || '';
        categorySelect.value = categoryValue;
        
        // If category didn't match any option, try to find it case-insensitively
        if (categorySelect.value === '' && categoryValue !== '') {
            // Try each option
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value.toLowerCase() === categoryValue.toLowerCase()) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        // Set content type radio button
        const contentType = data.content_type || 'typed';
        const contentTypeRadio = document.querySelector(`#modal-edit input[name="content_type"][value="${contentType}"]`);
        if (contentTypeRadio) {
            contentTypeRadio.checked = true;
            // Update visual state
            toggleEditContentFields();
        }
        
        document.getElementById('edit-status').value = data.status;
        document.getElementById('edit-excerpt').value = data.excerpt || '';
        quillEdit.root.innerHTML = data.content || '';
        
        // Get all section references
        const editContentSection = document.getElementById('edit-content-section');
        const editFeaturedImageSection = document.getElementById('edit-featured-image-section');
        const editHandwrittenSection = document.getElementById('edit-handwritten-section');
        const editVideoDurationSection = document.getElementById('edit-video-duration-section');
        const editVideoFileSection = document.getElementById('edit-video-file-section');
        const editVideoPreviewSection = document.getElementById('edit-video-preview-section');
        const editVideoPreviewPlayer = document.getElementById('edit-video-preview-player');
        const editCurrentPosterSection = document.getElementById('edit-current-poster-section');
        const editCurrentPosterImg = document.getElementById('edit-current-poster-img');
        const editVideoDuration = document.getElementById('edit-video-duration');
        const editVideoOrientation = document.getElementById('edit-video-orientation');
        const editCoverFramePreviewContainer = document.getElementById('edit-cover-frame-preview-container');
        
        // Reset all sections visibility
        if (editVideoPreviewSection) editVideoPreviewSection.classList.add('hidden');
        if (editCurrentPosterSection) editCurrentPosterSection.classList.add('hidden');
        if (editCoverFramePreviewContainer) editCoverFramePreviewContainer.classList.add('hidden');
        
        // Show/hide sections based on content type
        if (isVideoPost) {
            // Video post: show video-specific fields, hide non-video fields
            if (editContentSection) editContentSection.classList.add('hidden');
            if (editFeaturedImageSection) editFeaturedImageSection.classList.add('hidden');
            if (editHandwrittenSection) editHandwrittenSection.classList.add('hidden');
            if (editVideoDurationSection) editVideoDurationSection.classList.remove('hidden');
            if (editVideoFileSection) editVideoFileSection.classList.remove('hidden');
            if (editVideoPreviewSection) editVideoPreviewSection.classList.remove('hidden');
            
            // Set video player source
            if (editVideoPreviewPlayer && data.video_file) {
                const videoSrc = basePath + data.video_file;
                console.log('Loading video from:', videoSrc);
                editVideoPreviewPlayer.src = videoSrc;
                editVideoPreviewPlayer.load();
            }
            
            // Show current poster if exists
            if (data.video_poster && editCurrentPosterSection && editCurrentPosterImg) {
                editCurrentPosterImg.src = basePath + data.video_poster;
                editCurrentPosterSection.classList.remove('hidden');
            }
            
            // Set duration
            if (editVideoDuration && data.video_duration) {
                editVideoDuration.value = data.video_duration;
            }
            
            // Set orientation
            if (editVideoOrientation && data.video_orientation) {
                editVideoOrientation.value = data.video_orientation;
            }
        } else {
            // Non-video post: show content/image fields, hide video fields
            if (editContentSection) editContentSection.classList.remove('hidden');
            if (editFeaturedImageSection) editFeaturedImageSection.classList.remove('hidden');
            if (editHandwrittenSection) editHandwrittenSection.classList.remove('hidden');
            if (editVideoDurationSection) editVideoDurationSection.classList.add('hidden');
            if (editVideoFileSection) editVideoFileSection.classList.add('hidden');
            
            // Clear image preview
            const preview = document.getElementById('edit-img-preview');
            if (preview) {
                preview.classList.add('hidden');
            }
        }
    }
    
    // View modal
    const viewBtn = e.target.closest('[data-view]');
    if (viewBtn) {
        const data = JSON.parse(viewBtn.getAttribute('data-view'));
        document.getElementById('view-title').textContent = data.title;
        document.getElementById('view-slug').textContent = data.slug;
        document.getElementById('view-date').textContent = new Date(data.published_at || data.created_at).toLocaleDateString();
        document.getElementById('view-excerpt').textContent = data.excerpt || 'No excerpt';
        document.getElementById('view-content').innerHTML = data.content || 'No content';
        
        const catBadge = document.getElementById('view-category');
        if (data.category_name) {
            catBadge.textContent = data.category_name;
            catBadge.classList.remove('hidden');
        } else {
            catBadge.classList.add('hidden');
        }
        
        const statusBadge = document.getElementById('view-status');
        statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        statusBadge.className = 'status-badge status-' + data.status;
        statusBadge.classList.remove('hidden');
    }
    
    // Delete modal
    const delBtn = e.target.closest('[data-modal-target="modal-delete"]');
    if (delBtn) {
        document.getElementById('delete-id').value = delBtn.getAttribute('data-id');
        document.getElementById('delete-title').textContent = delBtn.getAttribute('data-title');
    }
});
</script>
