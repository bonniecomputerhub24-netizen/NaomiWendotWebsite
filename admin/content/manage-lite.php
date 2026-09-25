<?php
/**
 * Lightweight Content Manager - For servers with resource limits
 * Simplified version without heavy modals
 */

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '90');
error_reporting(0);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
requireAdminLogin();

$db = getDb();
$pageTitle = 'Manage Writing (Lite)';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Filters
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterCat = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build WHERE clause
$where = [];
$params = [];

if ($filterStatus !== '') {
    $where[] = 'status = :status';
    $params[':status'] = $filterStatus;
}

if ($filterCat !== '') {
    $where[] = 'category_id = :cat';
    $params[':cat'] = $filterCat;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$totalStmt = $db->prepare("SELECT COUNT(*) as cnt FROM posts $whereSql");
$totalStmt->execute($params);
$totalRows = (int)($totalStmt->fetch()['cnt'] ?? 0);
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch posts - MINIMAL data
$stmt = $db->prepare("
    SELECT p.id, p.title, p.slug, p.status, p.created_at,
           c.name as category_name
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereSql
    ORDER BY p.id DESC
    LIMIT :lim OFFSET :off
");

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Fetch categories
$catStmt = $db->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 md:ml-64 p-4 md:p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-plum font-playfair">Manage Writing</h1>
                <p class="text-sm text-gray-600 mt-1">Lightweight version for better performance</p>
            </div>
            <a href="editor.php" class="px-4 py-2 bg-gold text-white rounded-lg font-semibold hover:bg-opacity-90">
                + New Post
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-3">
                <select name="status" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">All Status</option>
                    <option value="published" <?php echo $filterStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $filterStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
                
                <select name="category" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filterCat == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="px-4 py-2 bg-plum text-white rounded-lg text-sm">
                    Filter
                </button>
                
                <a href="?" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm">
                    Clear
                </a>
            </form>
        </div>

        <!-- Posts List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No posts found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-sm text-gray-900">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        /<?php echo htmlspecialchars($post['slug']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">Published</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="editor.php?id=<?php echo $post['id']; ?>" 
                                           class="px-3 py-1 text-xs bg-plum text-white rounded hover:bg-opacity-90">
                                            Edit
                                        </a>
                                        <a href="?delete=<?php echo $post['id']; ?>" 
                                           onclick="return confirm('Delete this post?')"
                                           class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                            Delete
                                        </a>
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
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-600">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    (<?php echo $totalRows; ?> total posts)
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $filterStatus; ?>&category=<?php echo $filterCat; ?>" 
                           class="px-3 py-2 bg-white border rounded-lg text-sm hover:bg-gray-50">
                            ← Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $filterStatus; ?>&category=<?php echo $filterCat; ?>" 
                           class="px-3 py-2 bg-plum text-white rounded-lg text-sm hover:bg-opacity-90">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>💡 Tip:</strong> This is a lightweight version optimized for servers with resource limits. 
                For full features with modals, <a href="manage.php" class="underline">switch to standard view</a>.
            </p>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
