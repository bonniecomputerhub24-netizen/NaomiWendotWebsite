<?php
/**
 * Book Chapters Management
 * View and manage chapters for a specific book
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$pageTitle = 'Manage Chapters';
$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : null;

if (!$bookId) {
    $_SESSION['flash_message'] = "Book ID is required.";
    $_SESSION['flash_type'] = "error";
    header("Location: manage.php");
    exit;
}

// Fetch book details
try {
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        $_SESSION['flash_message'] = "Book not found.";
        $_SESSION['flash_type'] = "error";
        header("Location: manage.php");
        exit;
    }
} catch (Exception $e) {
    die("Error fetching book: " . $e->getMessage());
}

// Fetch all chapters for this book
$chapters = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM book_chapters 
        WHERE book_id = ? 
        ORDER BY display_order ASC, chapter_number ASC
    ");
    $stmt->execute([$bookId]);
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching chapters: " . $e->getMessage();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $chapterId = (int)$_POST['chapter_id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM book_chapters WHERE id = ? AND book_id = ?");
        $stmt->execute([$chapterId, $bookId]);
        
        $_SESSION['flash_message'] = "Chapter deleted successfully!";
        $_SESSION['flash_type'] = "success";
        header("Location: chapters.php?book_id=" . $bookId);
        exit;
    } catch (Exception $e) {
        $error = "Error deleting chapter: " . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="manage.php" class="text-gray-600 hover:text-plum">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 font-playfair">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </h1>
                    <p class="text-gray-600 mt-1">Manage chapters for this book</p>
                </div>
                <a 
                    href="chapter-editor.php?book_id=<?php echo $bookId; ?>" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all shadow-md"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Chapter
                </a>
            </div>

            <!-- Book Info Card -->
            <div class="bg-white rounded-lg shadow-sm p-5 grid grid-cols-1 md:grid-cols-4 gap-4 border-l-4 border-gold">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Status</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php
                        $statusEmojis = [
                            'draft' => '📝',
                            'writing' => '✍️',
                            'coming_soon' => '🔜',
                            'published' => '✅',
                            'archived' => '📦'
                        ];
                        echo $statusEmojis[$book['status']] . ' ' . ucfirst(str_replace('_', ' ', $book['status']));
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Chapters</p>
                    <p class="text-sm font-medium text-gray-900"><?php echo count($chapters); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Published</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo count(array_filter($chapters, fn($c) => $c['status'] === 'published')); ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Quick Actions</p>
                    <a href="editor.php?id=<?php echo $bookId; ?>" class="text-sm text-blue-600 hover:underline">Edit Book Info →</a>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                <?php 
                echo htmlspecialchars($_SESSION['flash_message']); 
                unset($_SESSION['flash_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Chapters List -->
        <?php if (!empty($chapters)): ?>
            <div class="space-y-4">
                <?php foreach ($chapters as $chapter): ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Chapter Number Badge -->
                                <div class="flex-shrink-0 w-16 h-16 bg-plum text-cream rounded-lg flex items-center justify-center font-bold text-xl">
                                    <?php echo (int)$chapter['chapter_number']; ?>
                                </div>

                                <!-- Chapter Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4 mb-2">
                                        <div class="flex-1">
                                            <h3 class="font-playfair font-bold text-plum text-xl mb-1">
                                                <?php echo htmlspecialchars($chapter['title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500 font-mono">
                                                /{<?php echo htmlspecialchars($chapter['slug']); ?>
                                            </p>
                                        </div>

                                        <!-- Status Badge -->
                                        <div>
                                            <?php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-700',
                                                'in_progress' => 'bg-blue-100 text-blue-700',
                                                'review' => 'bg-yellow-100 text-yellow-700',
                                                'published' => 'bg-green-100 text-green-700'
                                            ];
                                            $statusClass = $statusColors[$chapter['status']] ?? 'bg-gray-100 text-gray-700';
                                            ?>
                                            <span class="px-3 py-1 <?php echo $statusClass; ?> text-xs font-semibold rounded-full">
                                                <?php echo ucfirst(str_replace('_', ' ', $chapter['status'])); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Chapter Stats -->
                                    <div class="flex items-center gap-6 text-sm text-gray-600 mb-3">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <?php echo number_format((int)$chapter['word_count']); ?> words
                                        </span>
                                        <?php if ($chapter['is_preview']): ?>
                                            <span class="flex items-center gap-1 text-gold font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Free Preview
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-gray-400">
                                            Updated <?php echo date('M j, Y', strtotime($chapter['updated_at'])); ?>
                                        </span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <a 
                                            href="chapter-editor.php?id=<?php echo $chapter['id']; ?>&book_id=<?php echo $bookId; ?>" 
                                            class="inline-flex items-center gap-1 px-4 py-2 bg-plum text-white text-sm font-medium rounded hover:bg-opacity-90 transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this chapter?');" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="chapter_id" value="<?php echo $chapter['id']; ?>">
                                            <button 
                                                type="submit"
                                                class="inline-flex items-center gap-1 px-4 py-2 text-red-600 hover:bg-red-50 rounded transition-all text-sm font-medium"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4 opacity-30">📝</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Chapters Yet</h3>
                <p class="text-gray-500 mb-6">Start writing your first chapter to see it here.</p>
                <a 
                    href="chapter-editor.php?book_id=<?php echo $bookId; ?>" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Write First Chapter
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
