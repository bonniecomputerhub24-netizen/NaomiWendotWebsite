<?php
/**
 * Book Editor - Create/Edit Book Metadata
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$pageTitle = 'Book Editor';
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $bookId !== null;

// Fetch book data if editing
$book = null;
if ($isEdit) {
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
        $error = "Error fetching book: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');
    $price = floatval($_POST['price'] ?? 0); // Legacy field
    $price_usd = floatval($_POST['price_usd'] ?? 0);
    $price_kes = floatval($_POST['price_kes'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $category = trim($_POST['category'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $pages = $_POST['pages'] ? (int)$_POST['pages'] : null;
    $pre_order = isset($_POST['pre_order']) ? 1 : 0;
    $preview_available = isset($_POST['preview_available']) ? 1 : 0;
    
    // Auto-generate slug if empty
    if (empty($slug) && !empty($title)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }
    
    // Validation
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if ($price < 0) $errors[] = "Price must be positive";
    
    if (empty($errors)) {
        try {
            $db = getDb();
            
            if ($isEdit) {
                // Update existing book
                $stmt = $db->prepare("
                    UPDATE books SET
                        title = ?, subtitle = ?, slug = ?, description = ?,
                        cover_image = ?, price = ?, price_usd = ?, price_kes = ?, 
                        status = ?, category = ?,
                        isbn = ?, pages = ?, pre_order = ?, preview_available = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $subtitle, $slug, $description,
                    $cover_image, $price_usd, $price_usd, $price_kes, 
                    $status, $category,
                    $isbn, $pages, $pre_order, $preview_available,
                    $bookId
                ]);
                
                $_SESSION['flash_message'] = "Book updated successfully!";
            } else {
                // Insert new book
                $stmt = $db->prepare("
                    INSERT INTO books (
                        title, subtitle, slug, description, cover_image,
                        price, price_usd, price_kes, status, category, 
                        isbn, pages, pre_order, preview_available
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $subtitle, $slug, $description, $cover_image,
                    $price_usd, $price_usd, $price_kes, $status, $category, 
                    $isbn, $pages, $pre_order, $preview_available
                ]);
                
                $bookId = $db->lastInsertId();
                $_SESSION['flash_message'] = "Book created successfully!";
            }
            
            $_SESSION['flash_type'] = "success";
            header("Location: chapters.php?book_id=" . $bookId);
            exit;
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "A book with this slug already exists.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Pre-fill form data
$formData = $book ?? [
    'title' => $_POST['title'] ?? '',
    'subtitle' => $_POST['subtitle'] ?? '',
    'slug' => $_POST['slug'] ?? '',
    'description' => $_POST['description'] ?? '',
    'cover_image' => $_POST['cover_image'] ?? '',
    'price' => $_POST['price'] ?? '0.00',
    'status' => $_POST['status'] ?? 'draft',
    'category' => $_POST['category'] ?? '',
    'isbn' => $_POST['isbn'] ?? '',
    'pages' => $_POST['pages'] ?? '',
    'pre_order' => $_POST['pre_order'] ?? 0,
    'preview_available' => $_POST['preview_available'] ?? 0
];

include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="manage.php" class="text-gray-600 hover:text-plum">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 font-playfair">
                    <?php echo $isEdit ? 'Edit Book' : 'Create New Book'; ?>
                </h1>
            </div>
            <p class="text-gray-600">
                <?php echo $isEdit ? 'Update book information and metadata' : 'Enter book details to get started'; ?>
            </p>
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

        <!-- Book Editor Form -->
        <form method="POST" class="max-w-4xl">
            <div class="bg-white rounded-lg shadow-md p-8 space-y-6">
                
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="title" 
                        value="<?php echo htmlspecialchars($formData['title']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        required
                        oninput="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                    >
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Subtitle
                    </label>
                    <input 
                        type="text" 
                        name="subtitle" 
                        value="<?php echo htmlspecialchars($formData['subtitle']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        placeholder="Optional subtitle or tagline"
                    >
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="slug"
                        name="slug" 
                        value="<?php echo htmlspecialchars($formData['slug']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent font-mono text-sm"
                        required
                        pattern="[a-z0-9-]+"
                    >
                    <p class="text-xs text-gray-500 mt-1">URL-friendly version (lowercase, hyphens only). Auto-generated from title.</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        name="description" 
                        rows="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        placeholder="Book description for the shop page..."
                    ><?php echo htmlspecialchars($formData['description']); ?></textarea>
                </div>

                <!-- Two Column Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Category
                        </label>
                        <select 
                            name="category" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        >
                            <option value="">Select Category</option>
                            <option value="Poetry" <?php echo $formData['category'] === 'Poetry' ? 'selected' : ''; ?>>Poetry</option>
                            <option value="Devotional" <?php echo $formData['category'] === 'Devotional' ? 'selected' : ''; ?>>Devotional</option>
                            <option value="Biography" <?php echo $formData['category'] === 'Biography' ? 'selected' : ''; ?>>Biography</option>
                            <option value="Non-Fiction" <?php echo $formData['category'] === 'Non-Fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
                            <option value="Fiction" <?php echo $formData['category'] === 'Fiction' ? 'selected' : ''; ?>>Fiction</option>
                            <option value="Children" <?php echo $formData['category'] === 'Children' ? 'selected' : ''; ?>>Children</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="status" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        >
                            <option value="draft" <?php echo $formData['status'] === 'draft' ? 'selected' : ''; ?>>📝 Draft</option>
                            <option value="writing" <?php echo $formData['status'] === 'writing' ? 'selected' : ''; ?>>✍️ Writing</option>
                            <option value="coming_soon" <?php echo $formData['status'] === 'coming_soon' ? 'selected' : ''; ?>>🔜 Coming Soon</option>
                            <option value="published" <?php echo $formData['status'] === 'published' ? 'selected' : ''; ?>>✅ Published</option>
                            <option value="archived" <?php echo $formData['status'] === 'archived' ? 'selected' : ''; ?>>📦 Archived</option>
                        </select>
                    </div>

                    <!-- Price USD -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Price (USD) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                id="price_usd"
                                name="price_usd" 
                                value="<?php echo htmlspecialchars($formData['price_usd'] ?? $formData['price']); ?>"
                                step="0.01"
                                min="0"
                                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                                required
                            >
                        </div>
                    </div>

                    <!-- Price KES -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Price (KES) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">KSh</span>
                            <input 
                                type="number" 
                                id="price_kes"
                                name="price_kes" 
                                value="<?php echo htmlspecialchars($formData['price_kes'] ?? ($formData['price'] * 130)); ?>"
                                step="1"
                                min="0"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                                required
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Auto-calculates from USD (1 USD ≈ 130 KES)</p>
                    </div>

                    <!-- Pages -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Pages
                        </label>
                        <input 
                            type="number" 
                            name="pages" 
                            value="<?php echo htmlspecialchars($formData['pages']); ?>"
                            min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                            placeholder="Estimated page count"
                        >
                    </div>

                    <!-- ISBN -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            ISBN
                        </label>
                        <input 
                            type="text" 
                            name="isbn" 
                            value="<?php echo htmlspecialchars($formData['isbn']); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                            placeholder="978-0-123456-78-9"
                        >
                    </div>
                </div>

                <!-- Cover Image URL -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Cover Image URL
                    </label>
                    <input 
                        type="url" 
                        name="cover_image" 
                        value="<?php echo htmlspecialchars($formData['cover_image']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gold focus:border-transparent"
                        placeholder="https://example.com/cover.jpg"
                    >
                    <p class="text-xs text-gray-500 mt-1">Paste an image URL (Unsplash, Imgur, etc.) Recommended: 600x900px</p>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-3">
                    <label class="flex items-center gap-3">
                        <input 
                            type="checkbox" 
                            name="pre_order" 
                            value="1"
                            <?php echo $formData['pre_order'] ? 'checked' : ''; ?>
                            class="w-5 h-5 text-gold focus:ring-gold border-gray-300 rounded"
                        >
                        <span class="text-sm text-gray-700">
                            <strong>Pre-order Available</strong> - Allow readers to pre-order before publication
                        </span>
                    </label>

                    <label class="flex items-center gap-3">
                        <input 
                            type="checkbox" 
                            name="preview_available" 
                            value="1"
                            <?php echo $formData['preview_available'] ? 'checked' : ''; ?>
                            class="w-5 h-5 text-gold focus:ring-gold border-gray-300 rounded"
                        >
                        <span class="text-sm text-gray-700">
                            <strong>Preview Available</strong> - Show preview chapters to readers
                        </span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-4 pt-6 border-t">
                    <button 
                        type="submit" 
                        class="px-8 py-3 bg-gold text-plum font-semibold rounded-lg hover:bg-opacity-90 transition-all shadow-md"
                    >
                        <?php echo $isEdit ? 'Update Book' : 'Create Book'; ?>
                    </button>
                    <a 
                        href="manage.php" 
                        class="px-8 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all"
                    >
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Auto-calculate KES price from USD
document.addEventListener('DOMContentLoaded', function() {
    const usdInput = document.getElementById('price_usd');
    const kesInput = document.getElementById('price_kes');
    const conversionRate = 130; // 1 USD = 130 KES (approximate)
    
    if (usdInput && kesInput) {
        usdInput.addEventListener('input', function() {
            const usdValue = parseFloat(this.value) || 0;
            const kesValue = Math.round(usdValue * conversionRate);
            kesInput.value = kesValue;
        });
    }
});
</script>
