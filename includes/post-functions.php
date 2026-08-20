<?php
/**
 * Post Helper Functions
 * Functions to fetch and display posts from database
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Check if video columns exist in database (cached)
 */
function hasVideoColumns() {
    static $hasColumns = null;
    
    if ($hasColumns === null) {
        try {
            $db = getDb();
            $columnsQuery = $db->query("SHOW COLUMNS FROM posts LIKE 'video_file'");
            $hasColumns = $columnsQuery->rowCount() > 0;
        } catch (Exception $e) {
            error_log("hasVideoColumns check error: " . $e->getMessage());
            $hasColumns = false;
        }
    }
    
    return $hasColumns;
}

/**
 * Get featured post for homepage hero
 */
function getFeaturedPost() {
    try {
        $db = getDb();
        
        $hasVideoFile = hasVideoColumns();
        
        $videoFields = $hasVideoFile 
            ? 'p.video_file, p.video_thumbnail, p.video_duration, p.video_poster, p.video_orientation, p.content_type,'
            : 'NULL as video_file, NULL as video_thumbnail, NULL as video_duration, NULL as video_poster, NULL as video_orientation, NULL as content_type,';
        
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.handwritten_image, $videoFields 
                   p.status, p.published_at, p.created_at, p.views, p.likes,
                   c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published'
            ORDER BY p.published_at DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("getFeaturedPost error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get latest posts
 * @param int $limit Number of posts to fetch (ignored if $perPage is set)
 * @param string $categorySlug Optional category filter
 * @param int $page Current page number (for pagination)
 * @param int|null $perPage Posts per page (null = no pagination, use $limit)
 * @return array Posts array, or paginated result array with ['posts', 'total', 'pages', 'current_page']
 */
function getLatestPosts($limit = 10, $categorySlug = null, $page = 1, $perPage = null) {
    try {
        $db = getDb();
        
        $hasVideoFile = hasVideoColumns();
        
        $videoFields = $hasVideoFile 
            ? 'p.video_file, p.video_thumbnail, p.video_duration, p.video_poster, p.video_orientation, p.content_type,'
            : 'NULL as video_file, NULL as video_thumbnail, NULL as video_duration, NULL as video_poster, NULL as video_orientation, NULL as content_type,';
        
        $whereclause = "WHERE p.status = 'published'";
        $params = [];
        
        if ($categorySlug) {
            $whereclause .= " AND c.slug = :category";
            $params[':category'] = $categorySlug;
        }
        
        // Pagination mode
        if ($perPage !== null) {
            $page = max(1, (int)$page);
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countStmt = $db->prepare("
                SELECT COUNT(*) as total
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                $whereclause
            ");
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetch()['total'];
            $totalPages = max(1, ceil($total / $perPage));
            
            // Get posts with pagination
            $stmt = $db->prepare("
                SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                       p.handwritten_image, $videoFields 
                       p.status, p.published_at, p.created_at, p.views, p.likes,
                       c.name as category_name, c.slug as category_slug
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                $whereclause
                ORDER BY p.published_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'posts' => $stmt->fetchAll(),
                'total' => $total,
                'pages' => $totalPages,
                'current_page' => $page
            ];
        }
        
        // Simple limit mode (backward compatible)
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.handwritten_image, $videoFields 
                   p.status, p.published_at, p.created_at, p.views, p.likes,
                   c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            $whereclause
            ORDER BY p.published_at DESC
            LIMIT :limit
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getLatestPosts error: " . $e->getMessage());
        return $perPage !== null ? ['posts' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1] : [];
    }
}

/**
 * Get all published posts with optional filters
 */
function getAllPosts($search = '', $categoryId = null, $page = 1, $perPage = 12, $contentType = '') {
    try {
        $db = getDb();
        
        $hasVideoFile = hasVideoColumns();
        
        $videoFields = $hasVideoFile 
            ? 'p.video_file, p.video_thumbnail, p.video_duration, p.video_views, p.video_poster, p.video_orientation, p.content_type,'
            : 'NULL as video_file, NULL as video_thumbnail, NULL as video_duration, NULL as video_views, NULL as video_poster, NULL as video_orientation, NULL as content_type,';
        
        $offset = ($page - 1) * $perPage;
        
        $where = ["p.status = 'published'"];
        $params = [];
        
        if ($search) {
            $where[] = "(p.title LIKE :search OR p.content LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if ($categoryId) {
            $where[] = "p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        
        if ($contentType) {
            $where[] = "p.content_type = :content_type";
            $params[':content_type'] = $contentType;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countStmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM posts p
            WHERE $whereClause
        ");
        
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetch()['total'];
        
        // Get posts
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.handwritten_image, $videoFields 
                   p.status, p.published_at, p.created_at, p.views, p.likes,
                   c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereClause
            ORDER BY p.published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    } catch (Exception $e) {
        error_log("getAllPosts error: " . $e->getMessage());
        return [
            'posts' => [],
            'total' => 0,
            'pages' => 0,
            'current_page' => 1
        ];
    }
}

/**
 * Get post by slug
 */
function getPostBySlug($slug) {
    try {
        $db = getDb();
        
        $hasVideoFile = hasVideoColumns();
        
        $videoFields = $hasVideoFile 
            ? 'p.video_file, p.video_thumbnail, p.video_duration, p.video_poster, p.video_orientation, p.content_type,'
            : 'NULL as video_file, NULL as video_thumbnail, NULL as video_duration, NULL as video_poster, NULL as video_orientation, NULL as content_type,';
        
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.handwritten_image, $videoFields 
                   p.status, p.published_at, p.created_at, p.views, p.likes,
                   c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = :slug AND p.status = 'published'
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();
        
        // Increment view count
        if ($post) {
            $updateStmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
            $updateStmt->execute([':id' => $post['id']]);
        }
        
        return $post;
    } catch (Exception $e) {
        error_log("getPostBySlug error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get approved testimonials
 */
function getApprovedTestimonials($limit = 3) {
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT name, testimony, location
            FROM testimonies
            WHERE status = 'approved'
            ORDER BY reviewed_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getApprovedTestimonials error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all categories
 */
function getCategories() {
    try {
        $db = getDb();
        $stmt = $db->query("
            SELECT id, name, slug, description, icon, display_order
            FROM categories
            ORDER BY display_order ASC, name ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getCategories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Format post excerpt
 */
function formatExcerpt($post, $length = 150) {
    $excerpt = $post['excerpt'] ?? '';
    
    // Handle video placeholder excerpts - replace with friendly text
    if (!empty($excerpt)) {
        // Check for video content placeholders
        if (in_array(trim($excerpt), ['[Video Content]', '[Video Content - See Video]', '[Video Content - See Video URL]'])) {
            // Return empty string to omit excerpt line (cleaner than showing generic text)
            return '';
        }
        return htmlspecialchars($excerpt);
    }
    
    // Generate from content
    $text = strip_tags($post['content'] ?? '');
    
    // Check if content is also a placeholder
    if (in_array(trim($text), ['[Video Content]', '[Video Content - See Video]', '[Video Content - See Video URL]'])) {
        return '';
    }
    
    if (strlen($text) <= $length) {
        return htmlspecialchars($text);
    }
    
    return htmlspecialchars(substr($text, 0, $length)) . '...';
}

/**
 * Format post date
 */
function formatPostDate($post) {
    $date = $post['published_at'] ?? $post['created_at'];
    return date('jS F Y', strtotime($date));
}

/**
 * Get category badge class
 */
function getCategoryBadgeClass($categorySlug) {
    $classes = [
        'poems' => 'bg-plum bg-opacity-10 text-plum',
        'articles' => 'bg-gold bg-opacity-10 text-gold',
        'daily-inspirations' => 'bg-rose border border-plum text-plum',
        'stories' => 'bg-green-100 text-green-800',
        'testimonies' => 'bg-transparent border border-gold text-gold',
        'videos' => 'bg-red-100 text-red-800'
    ];
    
    return $classes[$categorySlug] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Get video posts specifically
 * @param int $limit Number of videos to fetch
 */
function getVideoContent($limit = 6) {
    try {
        $db = getDb();
        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.handwritten_image, p.video_file, p.video_thumbnail, p.video_duration, 
                   p.content_type, p.status, p.published_at, p.created_at, p.views, p.likes,
                   c.name as category_name, c.slug as category_slug
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.content_type = 'video'
            ORDER BY p.published_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getVideoContent error: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if post is video content
 * @param array $post Post data
 * @return bool
 */
function isVideoPost($post) {
    return isset($post['content_type']) && $post['content_type'] === 'video' && !empty($post['video_file']);
}

/**
 * Get video source for display
 * @param array $post Post data
 * @return array ['type' => 'file', 'source' => 'path']
 */
function getVideoSource($post) {
    if (!empty($post['video_file'])) {
        return ['type' => 'file', 'source' => $post['video_file']];
    }
    return null;
}
