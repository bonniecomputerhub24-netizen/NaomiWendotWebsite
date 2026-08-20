<?php
/**
 * Content Editor - Write & Publish (redirects to manage page)
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

// Redirect to manage page (where the modal-based editor lives)
header('Location: manage.php');
exit;

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $scheduledDate = $_POST['scheduled_date'] ?? null;
    
    // Basic validation
    if (empty($title) || empty($category) || empty($content)) {
        $errorMessage = 'Please fill in all required fields (Title, Category, and Content).';
    } else {
        try {
            // TODO: Save to database
            // For now, just show success message
            
            if ($status === 'published') {
                $successMessage = '✅ Post published successfully! Your readers can now see it.';
            } elseif ($status === 'scheduled') {
                $successMessage = '📅 Post scheduled successfully! It will be published on ' . date('F j, Y', strtotime($scheduledDate));
            } else {
                $successMessage = '💾 Post saved as draft. You can publish it anytime.';
            }
        } catch (Exception $e) {
            $errorMessage = 'An error occurred while saving the post. Please try again.';
        }
    }
}

// TODO: Fetch categories from database
// For now, using hardcoded categories
$categories = [
    ['id' => 1, 'name' => 'Poems', 'slug' => 'poems'],
    ['id' => 2, 'name' => 'Articles', 'slug' => 'articles'],
    ['id' => 3, 'name' => 'Daily Inspirations', 'slug' => 'daily-inspirations'],
    ['id' => 4, 'name' => 'Stories', 'slug' => 'stories'],
    ['id' => 5, 'name' => 'Testimonies', 'slug' => 'testimonies']
];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen">
    <div class="max-w-5xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-plum font-playfair mb-2">Write New Post</h2>
            <p class="text-gray-600">Share your poems, articles, inspirations, and stories with the world.</p>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if ($successMessage): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 alert-auto-dismiss">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold"><?php echo htmlspecialchars($successMessage); ?></p>
                    <a href="manage.php" class="text-sm underline hover:no-underline mt-1 inline-block">View all posts →</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Editor Form -->
        <form method="POST" action="" class="space-y-6" id="editor-form">
            
            <!-- Main Content Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                
                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-semibold text-plum mb-2">
                        Post Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        placeholder="Enter a compelling title..."
                        class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                    >
                    <p class="text-xs text-gray-500 mt-1">Keep it clear and engaging</p>
                </div>
                
                <!-- Category -->
                <div class="mb-6">
                    <label for="category" class="block text-sm font-semibold text-plum mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category" 
                        name="category" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    >
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Choose the type of content you're writing</p>
                </div>
                
                <!-- Content Editor -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-semibold text-plum mb-2">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        required
                        rows="16"
                        placeholder="Start writing your heart out... 

You can use simple HTML tags if needed:
• <p>Paragraphs</p>
• <strong>Bold text</strong>
• <em>Italic text</em>
• <br> for line breaks
• <blockquote>Quotes</blockquote>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent font-mono text-sm"
                    ><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-500">Write naturally - basic HTML is supported</p>
                        <span id="char-count" class="text-xs text-gray-500">0 characters</span>
                    </div>
                </div>
                
                <!-- Excerpt (Optional) -->
                <div class="mb-6">
                    <label for="excerpt" class="block text-sm font-semibold text-plum mb-2">
                        Excerpt <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <textarea 
                        id="excerpt" 
                        name="excerpt" 
                        rows="2"
                        placeholder="Short summary for previews (if left empty, we'll use the first few lines)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    ><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">A brief description that appears in listings</p>
                </div>
                
            </div>
            
            <!-- Publishing Options Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                <h3 class="text-lg font-bold text-plum mb-4 font-playfair flex items-center gap-2">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Publishing Options
                </h3>
                
                <div class="space-y-4">
                    
                    <!-- Status Radio Buttons -->
                    <div>
                        <label class="block text-sm font-semibold text-plum mb-3">What would you like to do?</label>
                        <div class="space-y-3">
                            <!-- Save as Draft -->
                            <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-plum transition-colors">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="draft" 
                                    class="mt-1 w-4 h-4 text-plum focus:ring-gold"
                                    checked
                                >
                                <div class="flex-1">
                                    <p class="font-semibold text-plum">💾 Save as Draft</p>
                                    <p class="text-sm text-gray-600">Keep working on it later, not visible to readers yet</p>
                                </div>
                            </label>
                            
                            <!-- Publish Now -->
                            <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gold transition-colors">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="published" 
                                    class="mt-1 w-4 h-4 text-gold focus:ring-gold"
                                >
                                <div class="flex-1">
                                    <p class="font-semibold text-plum">✅ Publish Now</p>
                                    <p class="text-sm text-gray-600">Make it live immediately for everyone to read</p>
                                </div>
                            </label>
                            
                            <!-- Schedule for Later -->
                            <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="scheduled" 
                                    class="mt-1 w-4 h-4 text-blue-500 focus:ring-blue-500"
                                    id="schedule-radio"
                                >
                                <div class="flex-1">
                                    <p class="font-semibold text-plum">📅 Schedule for Later</p>
                                    <p class="text-sm text-gray-600 mb-3">Choose a date and time to publish automatically</p>
                                    <input 
                                        type="datetime-local" 
                                        name="scheduled_date" 
                                        id="scheduled_date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                                        disabled
                                    >
                                </div>
                            </label>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-between items-center bg-white rounded-xl shadow-sm p-6">
                <a href="manage.php" class="text-gray-600 hover:text-plum transition-colors text-sm font-medium">
                    ← Cancel and go back
                </a>
                <div class="flex gap-3">
                    <button 
                        type="button"
                        onclick="document.querySelector('input[value=draft]').checked = true; document.getElementById('editor-form').submit();"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-plum hover:text-plum transition-all"
                    >
                        💾 Save Draft
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-plum text-white rounded-lg font-semibold hover:bg-opacity-90 transition-all flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Publish Post
                    </button>
                </div>
            </div>
            
        </form>
        
        <!-- Writing Tips Card -->
        <div class="bg-gradient-to-r from-gold to-yellow-500 rounded-xl p-6 text-white mt-6">
            <h3 class="font-bold text-lg mb-3 font-playfair flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Writing Tips
            </h3>
            <ul class="space-y-2 text-sm text-white text-opacity-95">
                <li>✨ <strong>Write from the heart</strong> - authenticity connects with readers</li>
                <li>📖 <strong>Use short paragraphs</strong> - easier to read on phones</li>
                <li>💭 <strong>Save drafts often</strong> - your work won't be lost</li>
                <li>🙏 <strong>Include Scripture</strong> when it fits - it anchors the message</li>
                <li>🎯 <strong>Start strong</strong> - capture attention in the first sentence</li>
            </ul>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Editor JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter
    const contentTextarea = document.getElementById('content');
    const charCount = document.getElementById('char-count');
    
    function updateCharCount() {
        const count = contentTextarea.value.length;
        charCount.textContent = count.toLocaleString() + ' characters';
    }
    
    if (contentTextarea && charCount) {
        contentTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count
    }
    
    // Schedule date enable/disable
    const scheduleRadio = document.getElementById('schedule-radio');
    const scheduledDate = document.getElementById('scheduled_date');
    const allRadios = document.querySelectorAll('input[name="status"]');
    
    allRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (scheduleRadio.checked) {
                scheduledDate.disabled = false;
                scheduledDate.required = true;
            } else {
                scheduledDate.disabled = true;
                scheduledDate.required = false;
            }
        });
    });
    
    // Unsaved changes warning
    let formChanged = false;
    const form = document.getElementById('editor-form');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', () => formChanged = true);
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
    
    form.addEventListener('submit', () => formChanged = false);
    
    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    
    contentTextarea.addEventListener('input', function() {
        // Optional: auto-expand (commented out as might be too aggressive)
        // autoResize(this);
    });
});
</script>
