<?php
/**
 * Contact Messages Manager
 * View and manage contact form submissions
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
requireAdminLogin();

$pageTitle = 'Contact Messages';
$flash = '';
$db = getDb();

// ═══════════════════════════════════════════════════════════════
// HANDLE POST REQUESTS (DELETE, MARK AS READ)
// ═══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $flash = '🗑️ Message deleted successfully.';
        }
        
        if ($action === 'mark_read') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $flash = '✅ Message marked as read.';
        }
        
        $_SESSION['flash_message'] = $flash;
        header('Location: messages.php');
        exit;
        
    } catch (Exception $e) {
        $flash = '❌ Error: ' . htmlspecialchars($e->getMessage());
    }
}

// Flash messages
if (!empty($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get total count
$totalRows = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM contact_messages");
    $totalRows = (int)($stmt->fetch()['cnt'] ?? 0);
} catch (Exception $e) {
    $totalRows = 0;
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch messages
$messages = [];
try {
    $stmt = $db->prepare("
        SELECT id, name, email, message, status, created_at
        FROM contact_messages
        ORDER BY created_at DESC
        LIMIT :lim OFFSET :off
    ");
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    $messages = [];
}

// Get unread count
$unreadCount = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM contact_messages WHERE status = 'unread'");
    $unreadCount = (int)($stmt->fetch()['cnt'] ?? 0);
} catch (Exception $e) {
    $unreadCount = 0;
}

$adminName = getAdminName();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
.message-unread {
    background: #FEF3C7;
    border-left: 4px solid #D4A017;
}

.message-read {
    background: #F9FAFB;
}

@media (max-width: 768px) {
    .mobile-hide {
        display: none;
    }
}
</style>

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-plum font-playfair mb-2">Contact Messages</h2>
                <p class="text-gray-600">Messages from visitors via the contact form</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Unread Messages</p>
                <p class="text-3xl font-bold text-gold"><?php echo $unreadCount; ?></p>
            </div>
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
        
        <!-- Messages List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php if (empty($messages)): ?>
                <div class="text-center py-16">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">No messages yet</p>
                    <p class="text-gray-400 text-sm mt-2">Messages from the contact form will appear here</p>
                </div>
            <?php else: ?>
                <div class="space-y-0">
                    <?php foreach ($messages as $msg): 
                        $isUnread = ($msg['status'] === 'unread');
                    ?>
                        <div class="border-b border-gray-200 last:border-b-0 p-5 hover:bg-gray-50 transition-colors <?php echo $isUnread ? 'message-unread' : 'message-read'; ?>">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-plum to-gold flex items-center justify-center text-white font-bold text-lg">
                                        <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <h3 class="font-bold text-plum flex items-center gap-2">
                                                <?php echo htmlspecialchars($msg['name']); ?>
                                                <?php if ($isUnread): ?>
                                                    <span class="inline-block w-2 h-2 bg-gold rounded-full" title="Unread"></span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="hover:text-gold">
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="text-right mobile-hide">
                                            <p class="text-xs text-gray-500">
                                                <?php echo date('M j, Y', strtotime($msg['created_at'])); ?>
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <p class="text-gray-700 mb-3 whitespace-pre-wrap">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </p>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a 
                                            href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: Your message to Naomi Wendot"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-plum text-white rounded-lg text-xs font-semibold hover:bg-opacity-90 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                            </svg>
                                            Reply
                                        </a>
                                        
                                        <?php if ($isUnread): ?>
                                            <form method="post" action="" class="inline">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                <button 
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition-colors"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Mark Read
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <button 
                                            type="button"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:bg-red-600 transition-colors"
                                            data-modal-target="modal-delete"
                                            data-id="<?php echo $msg['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($msg['name']); ?>"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>
                                        
                                        <span class="text-xs text-gray-500 md:hidden">
                                            <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-4 py-3 flex items-center justify-between text-xs text-gray-600 border-t border-gray-100">
                        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?> — <?php echo $totalRows; ?> message(s)</span>
                        <div class="flex gap-1">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Prev</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- DELETE MODAL -->
<div id="modal-delete" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-md w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Confirm Deletion</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <p class="text-sm text-gray-600 mb-2">Are you sure you want to delete this message from:</p>
        <p id="delete-name" class="text-sm font-semibold text-red-600 mb-4"></p>
        
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

<script>
// Modal System
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
        
        // Set delete data
        if (modalId === 'modal-delete') {
            document.getElementById('delete-id').value = modalTrigger.getAttribute('data-id');
            document.getElementById('delete-name').textContent = modalTrigger.getAttribute('data-name');
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
</script>

