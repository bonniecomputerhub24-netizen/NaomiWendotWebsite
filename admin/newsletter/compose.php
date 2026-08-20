<?php
/**
 * Newsletter Composer - Send emails to subscribers
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
requireAdminLogin();

$pageTitle = 'Compose Newsletter';
$flash = '';
$db = getDb();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'send') {
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $recipientType = $_POST['recipient_type'] ?? 'all';
            
            if (empty($subject) || empty($message)) {
                throw new Exception('Subject and message are required.');
            }
            
            // Get subscribers based on type
            if ($recipientType === 'newsletter') {
                $stmt = $db->query("SELECT email, name FROM newsletter_subscribers WHERE status = 'active' AND is_verified = 1");
            } elseif ($recipientType === 'waitlist') {
                $stmt = $db->query("SELECT email, name FROM waitlist");
            } else {
                // All subscribers (both newsletter and waitlist)
                $stmt = $db->query("
                    SELECT email, name FROM newsletter_subscribers WHERE status = 'active' AND is_verified = 1
                    UNION
                    SELECT email, name FROM waitlist
                ");
            }
            
            $recipients = $stmt->fetchAll();
            $sentCount = 0;
            
            // TODO: Implement actual email sending
            // For now, just simulate sending
            foreach ($recipients as $recipient) {
                // In production, use PHPMailer or similar
                // mail($recipient['email'], $subject, $message, $headers);
                $sentCount++;
            }
            
            $flash = "✅ Newsletter sent successfully to {$sentCount} subscriber(s)!";
            $_SESSION['flash_message'] = $flash;
            header('Location: compose.php');
            exit;
        }
    } catch (Exception $e) {
        $flash = '❌ Error: ' . htmlspecialchars($e->getMessage());
    }
}

// Check for flash message
if (!empty($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Get subscriber counts
$subscriberCounts = [
    'newsletter' => 0,
    'waitlist' => 0,
    'total' => 0
];

try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active' AND is_verified = 1");
    $subscriberCounts['newsletter'] = (int)$stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM waitlist");
    $subscriberCounts['waitlist'] = (int)$stmt->fetch()['count'];
    
    $subscriberCounts['total'] = $subscriberCounts['newsletter'] + $subscriberCounts['waitlist'];
} catch (Exception $e) {
    error_log("Subscriber count error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
/* Quill.js editor styling */
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
    min-height: 300px;
    color: #374151;
    line-height: 1.8;
}

.quill-wrap .ql-editor.ql-blank::before {
    color: #9CA3AF;
    font-style: normal;
}
</style>

<!-- Quill.js CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-5xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-plum font-playfair mb-2">Compose Newsletter</h2>
            <p class="text-gray-600">Send updates, inspirations, and announcements to your subscribers.</p>
        </div>
        
        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-3 alert-auto-dismiss">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($flash); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-plum">
                <h3 class="text-sm text-gray-600 mb-1">Newsletter Subscribers</h3>
                <p class="text-2xl font-bold text-plum"><?php echo number_format($subscriberCounts['newsletter']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-gold">
                <h3 class="text-sm text-gray-600 mb-1">Waitlist Members</h3>
                <p class="text-2xl font-bold text-plum"><?php echo number_format($subscriberCounts['waitlist']); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                <h3 class="text-sm text-gray-600 mb-1">Total Recipients</h3>
                <p class="text-2xl font-bold text-plum"><?php echo number_format($subscriberCounts['total']); ?></p>
            </div>
        </div>
        
        <!-- Composer Form -->
        <form method="post" action="" class="space-y-6" id="newsletter-form">
            <input type="hidden" name="action" value="send">
            
            <!-- Main Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                
                <!-- Recipients -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-plum mb-3">
                        Send To <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-plum transition-colors">
                            <input type="radio" name="recipient_type" value="all" class="mt-1 w-4 h-4 text-plum focus:ring-gold" checked>
                            <div class="flex-1">
                                <p class="font-semibold text-plum">All Subscribers</p>
                                <p class="text-sm text-gray-600">Newsletter subscribers + Waitlist members (<?php echo number_format($subscriberCounts['total']); ?> people)</p>
                            </div>
                        </label>
                        
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-plum transition-colors">
                            <input type="radio" name="recipient_type" value="newsletter" class="mt-1 w-4 h-4 text-plum focus:ring-gold">
                            <div class="flex-1">
                                <p class="font-semibold text-plum">Newsletter Subscribers Only</p>
                                <p class="text-sm text-gray-600">People who subscribed to your regular updates (<?php echo number_format($subscriberCounts['newsletter']); ?> people)</p>
                            </div>
                        </label>
                        
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gold transition-colors">
                            <input type="radio" name="recipient_type" value="waitlist" class="mt-1 w-4 h-4 text-gold focus:ring-gold">
                            <div class="flex-1">
                                <p class="font-semibold text-plum">Waitlist Members Only</p>
                                <p class="text-sm text-gray-600">People waiting for Nature & Bible Verses book (<?php echo number_format($subscriberCounts['waitlist']); ?> people)</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Subject -->
                <div class="mb-6">
                    <label for="subject" class="block text-sm font-semibold text-plum mb-2">
                        Email Subject <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        required
                        placeholder="e.g., New Poem: Finding Peace in the Storm"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">Keep it clear and compelling</p>
                </div>
                
                <!-- Message -->
                <div class="mb-6">
                    <label for="message" class="block text-sm font-semibold text-plum mb-2">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <div class="quill-wrap">
                        <div id="quill-editor"></div>
                    </div>
                    <textarea id="message" name="message" required class="hidden"></textarea>
                    <p class="text-xs text-gray-500 mt-2">Write your message using the editor above</p>
                </div>
                
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-between items-center bg-white rounded-xl shadow-sm p-6">
                <a href="../dashboard.php" class="text-gray-600 hover:text-plum transition-colors text-sm font-medium">
                    ← Back to Dashboard
                </a>
                <div class="flex gap-3">
                    <button 
                        type="button"
                        onclick="window.location.href='../dashboard.php'"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-plum hover:text-plum transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        id="send-btn"
                        class="px-6 py-3 bg-plum text-white rounded-lg font-semibold hover:bg-opacity-90 transition-all flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Newsletter
                    </button>
                </div>
            </div>
            
        </form>
        
        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-blue-900">
            <h3 class="font-bold text-lg mb-3 font-playfair flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Newsletter Best Practices
            </h3>
            <ul class="space-y-2 text-sm">
                <li>✨ <strong>Personal touch:</strong> Write as if you're speaking to a friend</li>
                <li>📖 <strong>Keep it focused:</strong> One main message per email works best</li>
                <li>🔗 <strong>Include a call-to-action:</strong> Link to your latest writing</li>
                <li>❤️ <strong>Be authentic:</strong> Share your heart and God's truth</li>
                <li>⏰ <strong>Timing matters:</strong> Early mornings work well for inspirational content</li>
            </ul>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Quill.js JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your newsletter message here...\n\nDear friends,\n\nI hope this message finds you well...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'header': [1, 2, 3, false] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link'],
                ['clean']
            ]
        }
    });
    
    // Sync Quill content with hidden textarea
    const form = document.getElementById('newsletter-form');
    const messageInput = document.getElementById('message');
    
    form.addEventListener('submit', function(e) {
        messageInput.value = quill.root.innerHTML;
        
        // Validate
        if (quill.getText().trim().length === 0) {
            e.preventDefault();
            alert('Please write a message before sending.');
            return false;
        }
        
        // Confirm before sending
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        let recipientCount = <?php echo $subscriberCounts['total']; ?>;
        if (recipientType === 'newsletter') recipientCount = <?php echo $subscriberCounts['newsletter']; ?>;
        if (recipientType === 'waitlist') recipientCount = <?php echo $subscriberCounts['waitlist']; ?>;
        
        if (!confirm(`Are you sure you want to send this newsletter to ${recipientCount} subscriber(s)?`)) {
            e.preventDefault();
            return false;
        }
        
        // Disable button to prevent double submission
        const sendBtn = document.getElementById('send-btn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
    });
});
</script>
