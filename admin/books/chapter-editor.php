<?php
/**
 * Chapter Editor - Write/Edit Book Chapters
 * Features: Auto-save, Word count, Markdown support
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$pageTitle = 'Chapter Editor';
$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : null;
$chapterId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $chapterId !== null;

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

// Fetch chapter data if editing
$chapter = null;
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM book_chapters WHERE id = ? AND book_id = ?");
        $stmt->execute([$chapterId, $bookId]);
        $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chapter) {
            $_SESSION['flash_message'] = "Chapter not found.";
            $_SESSION['flash_type'] = "error";
            header("Location: chapters.php?book_id=" . $bookId);
            exit;
        }
    } catch (Exception $e) {
        $error = "Error fetching chapter: " . $e->getMessage();
    }
}

// Get next chapter number
$nextChapterNumber = 1;
try {
    $stmt = $db->prepare("SELECT MAX(chapter_number) as max_num FROM book_chapters WHERE book_id = ?");
    $stmt->execute([$bookId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextChapterNumber = ($result['max_num'] ?? 0) + 1;
} catch (Exception $e) {
    // Use default
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapterNumber = (int)($_POST['chapter_number'] ?? $nextChapterNumber);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $isPreview = isset($_POST['is_preview']) ? 1 : 0;
    
    // Auto-generate slug if empty
    if (empty($slug) && !empty($title)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }
    
    // Calculate word count
    $wordCount = str_word_count(strip_tags($content));
    
    // Validation
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if ($chapterNumber < 1) $errors[] = "Chapter number must be positive";
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Update existing chapter
                $stmt = $db->prepare("
                    UPDATE book_chapters SET
                        chapter_number = ?, title = ?, slug = ?, content = ?,
                        word_count = ?, status = ?, is_preview = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND book_id = ?
                ");
                $stmt->execute([
                    $chapterNumber, $title, $slug, $content,
                    $wordCount, $status, $isPreview,
                    $chapterId, $bookId
                ]);
                
                $_SESSION['flash_message'] = "Chapter updated successfully!";
            } else {
                // Insert new chapter
                $stmt = $db->prepare("
                    INSERT INTO book_chapters (
                        book_id, chapter_number, title, slug, content,
                        word_count, status, is_preview, display_order
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $bookId, $chapterNumber, $title, $slug, $content,
                    $wordCount, $status, $isPreview, $chapterNumber
                ]);
                
                $_SESSION['flash_message'] = "Chapter created successfully!";
            }
            
            $_SESSION['flash_type'] = "success";
            header("Location: chapters.php?book_id=" . $bookId);
            exit;
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "A chapter with this number or slug already exists for this book.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Pre-fill form data
$formData = $chapter ?? [
    'chapter_number' => $_POST['chapter_number'] ?? $nextChapterNumber,
    'title' => $_POST['title'] ?? '',
    'slug' => $_POST['slug'] ?? '',
    'content' => $_POST['content'] ?? '',
    'status' => $_POST['status'] ?? 'draft',
    'is_preview' => $_POST['is_preview'] ?? 0,
    'word_count' => 0
];

include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="chapters.php?book_id=<?php echo $bookId; ?>" class="text-gray-600 hover:text-plum">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 font-playfair">
                        <?php echo $isEdit ? 'Edit Chapter' : 'New Chapter'; ?>
                    </h1>
                    <p class="text-sm text-gray-600">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </p>
                </div>
                <div id="word-count" class="text-sm text-gray-600">
                    <strong><?php echo number_format((int)$formData['word_count']); ?></strong> words
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Chapter Editor Form -->
        <form method="POST" id="chapter-form">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Main Editor Column -->
                <div class="lg:col-span-3 space-y-6">
                    
                    <!-- Chapter Title -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <input 
                            type="text" 
                            name="title" 
                            id="chapter-title"
                            value="<?php echo htmlspecialchars($formData['title']); ?>"
                            class="w-full text-3xl font-playfair font-bold text-plum border-0 focus:ring-0 p-0"
                            placeholder="Chapter Title..."
                            required
                            oninput="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                        >
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-6 py-3 border-b flex items-center justify-between">
                            <p class="text-sm text-gray-600">
                                💡 <strong>Tip:</strong> Use Markdown for formatting (## for headings, **bold**, *italic*, etc.)
                            </p>
                            <button 
                                type="button"
                                onclick="document.getElementById('markdown-help').classList.toggle('hidden')"
                                class="text-sm text-blue-600 hover:underline"
                            >
                                Markdown Guide
                            </button>
                        </div>
                        
                        <!-- Markdown Help -->
                        <div id="markdown-help" class="hidden bg-blue-50 px-6 py-4 border-b text-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="font-semibold mb-2">Basic Formatting:</p>
                                    <ul class="space-y-1 text-xs">
                                        <li><code># Heading 1</code></li>
                                        <li><code>## Heading 2</code></li>
                                        <li><code>**bold text**</code></li>
                                        <li><code>*italic text*</code></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold mb-2">Advanced:</p>
                                    <ul class="space-y-1 text-xs">
                                        <li><code>> Blockquote</code></li>
                                        <li><code>- Bullet point</code></li>
                                        <li><code>---</code> (horizontal line)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <textarea 
                            name="content" 
                            id="chapter-content"
                            rows="25"
                            class="w-full px-6 py-4 border-0 focus:ring-0 font-mono text-sm leading-relaxed"
                            placeholder="Start writing your chapter here...

# Chapter Title

Your content goes here. You can use Markdown for formatting.

## Section Heading

Regular paragraph text.

**Bold text** for emphasis.

*Italic text* for thoughts or quotes.

> Blockquote for Bible verses or special quotes"
                        ><?php echo htmlspecialchars($formData['content']); ?></textarea>
                    </div>
                </div>

                <!-- Sidebar Column -->
                <div class="space-y-6">
                    
                    <!-- Publish Settings -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Publish Settings</h3>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select 
                                name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent text-sm"
                            >
                                <option value="draft" <?php echo $formData['status'] === 'draft' ? 'selected' : ''; ?>>📝 Draft</option>
                                <option value="in_progress" <?php echo $formData['status'] === 'in_progress' ? 'selected' : ''; ?>>✍️ In Progress</option>
                                <option value="review" <?php echo $formData['status'] === 'review' ? 'selected' : ''; ?>>👀 Review</option>
                                <option value="published" <?php echo $formData['status'] === 'published' ? 'selected' : ''; ?>>✅ Published</option>
                            </select>
                        </div>

                        <!-- Free Preview Toggle -->
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    name="is_preview" 
                                    value="1"
                                    <?php echo $formData['is_preview'] ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-gold focus:ring-gold border-gray-300 rounded"
                                >
                                <span class="text-sm text-gray-700">
                                    Show as free preview
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">
                                Readers can read this chapter without purchasing
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2 pt-4 border-t">
                            <button 
                                type="submit" 
                                class="w-full px-4 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all"
                            >
                                <?php echo $isEdit ? '💾 Save Chapter' : '🚀 Create Chapter'; ?>
                            </button>
                            <a 
                                href="chapters.php?book_id=<?php echo $bookId; ?>" 
                                class="block w-full px-4 py-2 text-center border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all"
                            >
                                Cancel
                            </a>
                        </div>
                    </div>

                    <!-- Chapter Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Chapter Details</h3>
                        
                        <!-- Chapter Number -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Chapter Number
                            </label>
                            <input 
                                type="number" 
                                name="chapter_number" 
                                value="<?php echo (int)$formData['chapter_number']; ?>"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent text-sm"
                                required
                            >
                        </div>

                        <!-- Slug -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Slug
                            </label>
                            <input 
                                type="text" 
                                id="slug"
                                name="slug" 
                                value="<?php echo htmlspecialchars($formData['slug']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent text-sm font-mono"
                                required
                                pattern="[a-z0-9-]+"
                            >
                            <p class="text-xs text-gray-500 mt-1">URL-friendly (auto-generated)</p>
                        </div>
                    </div>

                    <!-- Auto-save Indicator -->
                    <div class="bg-blue-50 rounded-lg p-4 text-sm">
                        <p class="text-blue-900 font-medium mb-1">💡 Remember to Save</p>
                        <p class="text-blue-700 text-xs">
                            Your work is not automatically saved. Click "Save Chapter" before leaving.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Word Count Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentField = document.getElementById('chapter-content');
    const wordCountDisplay = document.getElementById('word-count');
    
    function updateWordCount() {
        const text = contentField.value.trim();
        const wordCount = text ? text.split(/\s+/).length : 0;
        wordCountDisplay.innerHTML = '<strong>' + wordCount.toLocaleString() + '</strong> words';
    }
    
    contentField.addEventListener('input', updateWordCount);
    updateWordCount(); // Initial count
});

// Warn before leaving with unsaved changes
let formChanged = false;
document.getElementById('chapter-form').addEventListener('input', function() {
    formChanged = true;
});

document.getElementById('chapter-form').addEventListener('submit', function() {
    formChanged = false;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
