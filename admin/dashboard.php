<?php
/**
 * Admin Dashboard
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireAdminLogin();

$pageTitle = 'Dashboard';

// Fetch real stats from database
$db = getDb();
$stats = [
    'total_posts' => 0,
    'total_testimonies' => 0,
    'total_subscribers' => 0,
    'pending_testimonies' => 0
];

try {
    // Total published posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    $stats['total_posts'] = (int)$stmt->fetch()['count'];
    
    // Total approved testimonies
    $stmt = $db->query("SELECT COUNT(*) as count FROM testimonies WHERE status = 'approved'");
    $stats['total_testimonies'] = (int)$stmt->fetch()['count'];
    
    // Total active newsletter subscribers
    $stmt = $db->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'");
    $stats['total_subscribers'] = (int)$stmt->fetch()['count'];
    
    // Pending testimonies
    $stmt = $db->query("SELECT COUNT(*) as count FROM testimonies WHERE status = 'pending'");
    $stats['pending_testimonies'] = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}

// Recent activity
$recentActivity = [];
try {
    // Get recent posts
    $stmt = $db->query("
        SELECT 'post' as type, title as message, created_at as time 
        FROM posts 
        WHERE status = 'published' 
        ORDER BY published_at DESC 
        LIMIT 3
    ");
    $recentPosts = $stmt->fetchAll();
    
    // Get recent testimonies
    $stmt = $db->query("
        SELECT 'testimony' as type, CONCAT(name, ' submitted a testimony') as message, created_at as time 
        FROM testimonies 
        WHERE status = 'pending' 
        ORDER BY created_at DESC 
        LIMIT 2
    ");
    $recentTestimonies = $stmt->fetchAll();
    
    // Combine and sort
    $recentActivity = array_merge($recentPosts, $recentTestimonies);
    usort($recentActivity, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    $recentActivity = array_slice($recentActivity, 0, 5);
    
    if (empty($recentActivity)) {
        $recentActivity = [
            ['type' => 'welcome', 'message' => 'Welcome to your admin panel!', 'time' => 'Just now']
        ];
    }
} catch (Exception $e) {
    error_log("Recent activity error: " . $e->getMessage());
    $recentActivity = [
        ['type' => 'welcome', 'message' => 'Welcome to your admin panel!', 'time' => 'Just now']
    ];
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-plum font-playfair mb-2">Dashboard</h2>
            <p class="text-gray-600">Welcome back! Here's what's happening with your writing platform.</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <!-- Total Posts -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-plum hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-plum bg-opacity-10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-plum" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-plum mb-1"><?php echo number_format($stats['total_posts']); ?></h3>
                <p class="text-sm text-gray-600">Total Posts</p>
            </div>
            
            <!-- Total Testimonies -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gold hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gold bg-opacity-10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-plum mb-1"><?php echo number_format($stats['total_testimonies']); ?></h3>
                <p class="text-sm text-gray-600">Testimonies</p>
            </div>
            
            <!-- Newsletter Subscribers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-plum mb-1"><?php echo number_format($stats['total_subscribers']); ?></h3>
                <p class="text-sm text-gray-600">Subscribers</p>
            </div>
            
            <!-- Pending Testimonies -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-plum mb-1"><?php echo number_format($stats['pending_testimonies']); ?></h3>
                <p class="text-sm text-gray-600">Pending Approval</p>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Actions -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-plum mb-4 font-playfair flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Write New Post -->
                        <a href="content/editor.php" class="group block p-4 border-2 border-gray-200 rounded-lg hover:border-gold hover:shadow-md transition-all">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-plum bg-opacity-10 rounded-lg flex items-center justify-center group-hover:bg-gold group-hover:bg-opacity-10 transition-colors">
                                    <svg class="w-5 h-5 text-plum group-hover:text-gold transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-plum mb-1">Write New Post</h4>
                                    <p class="text-sm text-gray-600">Create a new poem, article, or inspiration</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Manage Posts -->
                        <a href="content/manage.php" class="group block p-4 border-2 border-gray-200 rounded-lg hover:border-gold hover:shadow-md transition-all">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-plum bg-opacity-10 rounded-lg flex items-center justify-center group-hover:bg-gold group-hover:bg-opacity-10 transition-colors">
                                    <svg class="w-5 h-5 text-plum group-hover:text-gold transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-plum mb-1">Manage Writing</h4>
                                    <p class="text-sm text-gray-600">Edit or delete published posts</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Review Testimonies -->
                        <a href="testimonies/manage.php" class="group block p-4 border-2 border-gray-200 rounded-lg hover:border-gold hover:shadow-md transition-all">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-plum bg-opacity-10 rounded-lg flex items-center justify-center group-hover:bg-gold group-hover:bg-opacity-10 transition-colors">
                                    <svg class="w-5 h-5 text-plum group-hover:text-gold transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-plum mb-1">Review Testimonies</h4>
                                    <p class="text-sm text-gray-600">Approve or manage reader testimonies</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Send Newsletter -->
                        <a href="newsletter/compose.php" class="group block p-4 border-2 border-gray-200 rounded-lg hover:border-gold hover:shadow-md transition-all">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-plum bg-opacity-10 rounded-lg flex items-center justify-center group-hover:bg-gold group-hover:bg-opacity-10 transition-colors">
                                    <svg class="w-5 h-5 text-plum group-hover:text-gold transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-plum mb-1">Send Newsletter</h4>
                                    <p class="text-sm text-gray-600">Compose message to subscribers</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-plum mb-4 font-playfair flex items-center gap-2">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Recent Activity
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="flex items-start gap-3 pb-4 border-b border-gray-100 last:border-0">
                                <div class="w-2 h-2 bg-gold rounded-full mt-2 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700">
                                        <?php 
                                        if ($activity['type'] === 'post') {
                                            echo '📝 Published: ' . htmlspecialchars($activity['message']);
                                        } elseif ($activity['type'] === 'testimony') {
                                            echo '💬 ' . htmlspecialchars($activity['message']);
                                        } else {
                                            echo htmlspecialchars($activity['message']);
                                        }
                                        ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php 
                                        if ($activity['time'] === 'Just now') {
                                            echo 'Just now';
                                        } else {
                                            $time = strtotime($activity['time']);
                                            $diff = time() - $time;
                                            if ($diff < 60) {
                                                echo 'Just now';
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . ' minutes ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hours ago';
                                            } else {
                                                echo date('M j, Y', $time);
                                            }
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Welcome Guide Card -->
        <div class="bg-gradient-to-r from-plum to-purple-900 rounded-xl shadow-lg p-6 md:p-8 text-white">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-xl md:text-2xl font-bold font-playfair mb-2">Welcome to Your Writing Platform! 📝</h3>
                    <p class="text-cream text-sm md:text-base opacity-90 mb-4">
                        You can now write, publish, and manage all your content from this simple admin panel. Start by creating your first post or exploring the features.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="content/editor.php" class="inline-flex items-center gap-2 bg-gold text-plum px-5 py-2 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Create First Post
                        </a>
                        <a href="../public/index.php" target="_blank" class="inline-flex items-center gap-2 bg-white bg-opacity-20 text-white px-5 py-2 rounded-lg font-semibold hover:bg-opacity-30 transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            View Website
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <svg class="w-32 h-32 text-gold opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
