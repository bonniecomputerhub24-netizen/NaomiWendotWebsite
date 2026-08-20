<?php
/**
 * Books Management Page
 * View and manage all books
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Check authentication
requireAdminLogin();

$pageTitle = 'Manage Books';

// Fetch all books
$books = [];
try {
    $db = getDb();
    $stmt = $db->query("
        SELECT 
            b.*,
            (SELECT COUNT(*) FROM book_chapters WHERE book_id = b.id) as chapter_count,
            (SELECT COUNT(*) FROM book_chapters WHERE book_id = b.id AND status = 'published') as published_chapters
        FROM books b
        ORDER BY 
            FIELD(status, 'writing', 'draft', 'coming_soon', 'published', 'archived'),
            created_at DESC
    ");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching books: " . $e->getMessage();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $bookId = (int)$_POST['book_id'];
    
    try {
        $db = getDb();
        $stmt = $db->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        
        $_SESSION['flash_message'] = "Book deleted successfully!";
        $_SESSION['flash_type'] = "success";
        header("Location: manage.php");
        exit;
    } catch (Exception $e) {
        $error = "Error deleting book: " . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 font-playfair">Manage Books</h1>
                    <p class="text-gray-600 mt-2">Create and manage your book collection</p>
                </div>
                <a 
                    href="editor.php" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all shadow-md"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Book
                </a>
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

        <!-- Books Grid -->
        <?php if (!empty($books)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($books as $book): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Cover Image -->
                        <div class="relative h-64 bg-gray-200">
                            <?php if ($book['cover_image']): ?>
                                <img 
                                    src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                    alt="<?php echo htmlspecialchars($book['title']); ?>"
                                    class="w-full h-full object-cover"
                                >
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-plum to-purple-800 flex items-center justify-center">
                                    <span class="text-6xl text-white opacity-40">📖</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <?php
                                $statusColors = [
                                    'draft' => 'bg-gray-500',
                                    'writing' => 'bg-blue-600',
                                    'coming_soon' => 'bg-gold text-plum',
                                    'published' => 'bg-green-600',
                                    'archived' => 'bg-red-600'
                                ];
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'writing' => 'Writing',
                                    'coming_soon' => 'Coming Soon',
                                    'published' => 'Published',
                                    'archived' => 'Archived'
                                ];
                                $statusClass = $statusColors[$book['status']] ?? 'bg-gray-500';
                                $statusLabel = $statusLabels[$book['status']] ?? ucfirst($book['status']);
                                ?>
                                <span class="px-3 py-1 <?php echo $statusClass; ?> text-white text-xs font-semibold rounded-full">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Book Details -->
                        <div class="p-5">
                            <!-- Category -->
                            <?php if ($book['category']): ?>
                                <span class="inline-block px-2 py-1 bg-gold bg-opacity-10 text-gold text-xs font-semibold rounded mb-2">
                                    <?php echo htmlspecialchars($book['category']); ?>
                                </span>
                            <?php endif; ?>

                            <!-- Title -->
                            <h3 class="font-playfair font-bold text-plum text-xl mb-1 line-clamp-2">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h3>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php echo (int)$book['chapter_count']; ?> chapters
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <?php echo (int)$book['published_chapters']; ?> published
                                </span>
                            </div>

                            <!-- Price -->
                            <div class="mb-4">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-plum">
                                        $<?php echo number_format($book['price_usd'] ?? $book['price'], 2); ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        / KSh <?php echo number_format($book['price_kes'] ?? ($book['price'] * 130), 0); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <a 
                                    href="chapters.php?book_id=<?php echo $book['id']; ?>" 
                                    class="flex-1 px-4 py-2 bg-plum text-white text-center text-sm font-medium rounded hover:bg-opacity-90 transition-all"
                                >
                                    Chapters
                                </a>
                                <a 
                                    href="editor.php?id=<?php echo $book['id']; ?>" 
                                    class="flex-1 px-4 py-2 border-2 border-plum text-plum text-center text-sm font-medium rounded hover:bg-plum hover:text-white transition-all"
                                >
                                    Edit
                                </a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this book and all its chapters?');" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button 
                                        type="submit"
                                        class="px-3 py-2 text-red-600 hover:bg-red-50 rounded transition-all"
                                        title="Delete Book"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4 opacity-30">📚</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Books Yet</h3>
                <p class="text-gray-500 mb-6">Start writing your first book to see it here.</p>
                <a 
                    href="editor.php" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Your First Book
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
