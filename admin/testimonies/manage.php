<?php
/**
 * Manage Testimonies - View, Approve, Reject, Delete
 * Self-contained page - handles all CRUD operations
 * Naomi Wendot Admin Panel
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';
requireAdminLogin();

$pageTitle = 'Manage Testimonies';
$flash = '';
$db = getDb();

// ═══════════════════════════════════════════════════════════════
// HANDLE POST REQUESTS (APPROVE, REJECT, DELETE, CREATE)
// ═══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        // ─────────────────────────────────────────────────────────
        // APPROVE TESTIMONY
        // ─────────────────────────────────────────────────────────
        if ($action === 'approve') {
            $id = (int)($_POST['id'] ?? 0);
            $adminId = 1; // TODO: Get from session
            
            $stmt = $db->prepare("
                UPDATE testimonies 
                SET status = 'approved', reviewed_at = NOW(), reviewed_by = :admin_id
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id, ':admin_id' => $adminId]);
            
            $flash = '✅ Testimony approved successfully!';
        }
        
        // ─────────────────────────────────────────────────────────
        // REJECT TESTIMONY
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'reject') {
            $id = (int)($_POST['id'] ?? 0);
            $adminId = 1; // TODO: Get from session
            
            $stmt = $db->prepare("
                UPDATE testimonies 
                SET status = 'rejected', reviewed_at = NOW(), reviewed_by = :admin_id
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id, ':admin_id' => $adminId]);
            
            $flash = '❌ Testimony rejected.';
        }
        
        // ─────────────────────────────────────────────────────────
        // DELETE TESTIMONY
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM testimonies WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $flash = '🗑️ Testimony deleted successfully.';
        }
        
        // ─────────────────────────────────────────────────────────
        // CREATE TESTIMONY (Manual Add)
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $testimony = trim($_POST['testimony'] ?? '');
            $status = $_POST['status'] ?? 'approved'; // Manual adds are usually pre-approved
            $adminId = 1; // TODO: Get from session
            
            $stmt = $db->prepare("
                INSERT INTO testimonies (name, email, location, testimony, status, reviewed_at, reviewed_by, created_at)
                VALUES (:name, :email, :location, :testimony, :status, NOW(), :admin_id, NOW())
            ");
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':location' => $location,
                ':testimony' => $testimony,
                ':status' => $status,
                ':admin_id' => $adminId
            ]);
            
            $flash = '✅ Testimony added successfully!';
        }
        
        // ─────────────────────────────────────────────────────────
        // UPDATE TESTIMONY
        // ─────────────────────────────────────────────────────────
        elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $testimony = trim($_POST['testimony'] ?? '');
            $status = $_POST['status'] ?? 'approved';
            
            $stmt = $db->prepare("
                UPDATE testimonies 
                SET name = :name, email = :email, location = :location, 
                    testimony = :testimony, status = :status
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':email' => $email,
                ':location' => $location,
                ':testimony' => $testimony,
                ':status' => $status
            ]);
            
            $flash = '✅ Testimony updated successfully!';
        }
        
        // Redirect to prevent form resubmission
        $_SESSION['flash_message'] = $flash;
        header('Location: manage.php');
        exit;
        
    } catch (Exception $e) {
        $flash = '❌ Error: ' . htmlspecialchars($e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════
// DISPLAY LOGIC (GET REQUESTS)
// ═══════════════════════════════════════════════════════════════

// Flash messages
if (!empty($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Filters and pagination
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$where = [];
$params = [];
if ($filterStatus !== '') {
    $where[] = 'status = :status';
    $params[':status'] = $filterStatus;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$totalRows = 0;
try {
    $c = $db->prepare("SELECT COUNT(*) AS cnt FROM testimonies $whereSql");
    $c->execute($params);
    $totalRows = (int)($c->fetch()['cnt'] ?? 0);
} catch (Exception $e) {
    $totalRows = 0;
}

$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch testimonies
$testimonies = [];
try {
    $s = $db->prepare("
        SELECT id, name, email, location, testimony, status, created_at, reviewed_at
        FROM testimonies
        $whereSql
        ORDER BY 
            CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'approved' THEN 2
                WHEN status = 'rejected' THEN 3
            END,
            created_at DESC
        LIMIT :lim OFFSET :off
    ");
    foreach ($params as $k => $v) {
        $s->bindValue($k, $v, PDO::PARAM_STR);
    }
    $s->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $s->bindValue(':off', $offset, PDO::PARAM_INT);
    $s->execute();
    $testimonies = $s->fetchAll();
} catch (Exception $e) {
    $testimonies = [];
}

// Count by status for stats
$stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
try {
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM testimonies GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = (int)$row['count'];
    }
} catch (Exception $e) {
    // Keep defaults
}

$adminName = getAdminName();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
.status-badge {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 9999px;
    font-size: .65rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
.status-approved { background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC; }
.status-rejected { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

/* Mobile responsive */
@media (max-width: 768px) {
    .mobile-hide {
        display: none;
    }
    
    table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
}
</style>

<!-- Main Content -->
<main class="flex-1 md:ml-64 p-4 md:p-6 lg:p-8 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-plum font-playfair mb-2">Manage Testimonies</h2>
            <p class="text-gray-600">Review, approve, and manage reader testimonies</p>
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-yellow-700 uppercase">Pending Review</p>
                        <p class="text-2xl font-bold text-yellow-900"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-200 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-700 uppercase">Approved</p>
                        <p class="text-2xl font-bold text-green-900"><?php echo $stats['approved']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-700 uppercase">Rejected</p>
                        <p class="text-2xl font-bold text-red-900"><?php echo $stats['rejected']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-200 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Filter -->
                <form method="get" class="flex items-center gap-2">
                    <select 
                        name="status" 
                        class="w-full sm:w-auto rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-gold focus:outline-none"
                        onchange="this.form.submit()"
                    >
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>⏱️ Pending</option>
                        <option value="approved" <?php echo $filterStatus === 'approved' ? 'selected' : ''; ?>>✅ Approved</option>
                        <option value="rejected" <?php echo $filterStatus === 'rejected' ? 'selected' : ''; ?>>❌ Rejected</option>
                    </select>
                    <?php if ($filterStatus): ?>
                        <a href="manage.php" class="text-xs text-gray-400 hover:text-gray-600">Clear</a>
                    <?php endif; ?>
                </form>
                
                <!-- Add Button -->
                <button 
                    type="button" 
                    data-modal-target="modal-add"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-gold px-4 py-2 text-sm font-semibold text-white hover:bg-plum transition-colors"
                >
                    + Add Testimony Manually
                </button>
            </div>
        </div>
        
        <!-- Testimonies Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-rose border-b border-gold/20">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider">Name & Location</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider">Testimony</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider mobile-hide">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-plum uppercase tracking-wider mobile-hide">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-plum uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($testimonies)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                    No testimonies found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($testimonies as $t): ?>
                                <tr class="hover:bg-rose/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-plum"><?php echo htmlspecialchars($t['name']); ?></div>
                                        <?php if (!empty($t['location'])): ?>
                                            <div class="text-xs text-gray-500">📍 <?php echo htmlspecialchars($t['location']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($t['email'])): ?>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($t['email']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 max-w-md">
                                        <p class="text-sm text-gray-700 line-clamp-2">
                                            <?php echo htmlspecialchars(substr($t['testimony'], 0, 120)); ?><?php echo strlen($t['testimony']) > 120 ? '...' : ''; ?>
                                        </p>
                                        <!-- Mobile: Show status here -->
                                        <div class="md:hidden mt-2">
                                            <span class="status-badge status-<?php echo htmlspecialchars($t['status']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($t['status'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 mobile-hide">
                                        <span class="status-badge status-<?php echo htmlspecialchars($t['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($t['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 mobile-hide">
                                        <?php echo date('M j, Y', strtotime($t['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex flex-col sm:flex-row gap-1">
                                            <button 
                                                type="button"
                                                class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 whitespace-nowrap"
                                                data-modal-target="modal-view"
                                                data-view='<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES); ?>'
                                            >
                                                View
                                            </button>
                                            
                                            <?php if ($t['status'] === 'pending'): ?>
                                                <form method="post" action="" class="inline">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    <button 
                                                        type="submit"
                                                        class="rounded-lg bg-green-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-green-600 whitespace-nowrap w-full sm:w-auto"
                                                        onclick="return confirm('Approve this testimony?')"
                                                    >
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="post" action="" class="inline">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    <button 
                                                        type="submit"
                                                        class="rounded-lg bg-gray-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-gray-600 whitespace-nowrap w-full sm:w-auto"
                                                        onclick="return confirm('Reject this testimony?')"
                                                    >
                                                        Reject
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button 
                                                    type="button"
                                                    class="rounded-lg bg-plum px-2 py-1 text-[11px] font-semibold text-white hover:bg-gold whitespace-nowrap"
                                                    data-modal-target="modal-edit"
                                                    data-edit='<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES); ?>'
                                                >
                                                    Edit
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button 
                                                type="button"
                                                class="rounded-lg bg-red-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-red-600 whitespace-nowrap"
                                                data-modal-target="modal-delete"
                                                data-id="<?php echo $t['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($t['name']); ?>"
                                            >
                                                Delete
                                            </button>
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
                <div class="px-4 py-3 flex items-center justify-between text-xs text-gray-600 border-t border-gray-100">
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?> — <?php echo $totalRows; ?> testimony(ies)</span>
                    <div class="flex gap-1">
                        <?php
                        $queryParams = [];
                        if ($filterStatus) $queryParams[] = 'status=' . urlencode($filterStatus);
                        $base = 'manage.php?' . ($queryParams ? implode('&', $queryParams) . '&' : '');
                        ?>
                        <?php if ($page > 1): ?>
                            <a href="<?php echo $base; ?>page=<?php echo $page - 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo $base; ?>page=<?php echo $page + 1; ?>" class="px-3 py-1 rounded bg-rose hover:bg-gold/20">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Modals will be added next -->


<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- ADD TESTIMONY MODAL -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="modal-add" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-2xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Add Testimony</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <form method="post" action="" class="space-y-4">
            <input type="hidden" name="action" value="create">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        required
                        placeholder="Person's name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:ring-2 focus:ring-gold focus:outline-none"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Location</label>
                    <input 
                        type="text" 
                        name="location" 
                        placeholder="City, Country"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">
                    Email <span class="text-gray-400 text-xs">(optional)</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="email@example.com"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">
                    Testimony <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="testimony" 
                    rows="6"
                    required
                    placeholder="Share their testimony..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                ></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">Status</label>
                <select 
                    name="status" 
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
                    <option value="approved">✅ Approved (Publish immediately)</option>
                    <option value="pending">⏱️ Pending (Review later)</option>
                </select>
            </div>
            
            <div class="pt-4 flex justify-end gap-2 border-t border-gray-200">
                <button 
                    type="button" 
                    data-modal-close
                    class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-lg bg-plum text-sm font-semibold text-white hover:bg-gold transition-colors"
                >
                    Add Testimony
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- EDIT TESTIMONY MODAL -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="modal-edit" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-2xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Edit Testimony</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <form method="post" action="" class="space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="edit-name"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-plum mb-2">Location</label>
                    <input 
                        type="text" 
                        name="location" 
                        id="edit-location"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                    >
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    id="edit-email"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">Testimony</label>
                <textarea 
                    name="testimony" 
                    id="edit-testimony"
                    rows="6"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                ></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-plum mb-2">Status</label>
                <select 
                    name="status" 
                    id="edit-status"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gold focus:outline-none"
                >
                    <option value="approved">✅ Approved</option>
                    <option value="pending">⏱️ Pending</option>
                    <option value="rejected">❌ Rejected</option>
                </select>
            </div>
            
            <div class="pt-4 flex justify-end gap-2 border-t border-gray-200">
                <button 
                    type="button" 
                    data-modal-close
                    class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-lg bg-plum text-sm font-semibold text-white hover:bg-gold transition-colors"
                >
                    Update Testimony
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW TESTIMONY MODAL -->
<div id="modal-view" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-2xl w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Testimony Details</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 id="view-name" class="text-xl font-semibold text-plum"></h3>
                    <p id="view-location" class="text-sm text-gray-600"></p>
                    <p id="view-email" class="text-xs text-gray-400"></p>
                </div>
                <span id="view-status" class="status-badge"></span>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <p class="text-xs text-gray-500 mb-2">Submitted on <span id="view-date"></span></p>
                <div id="view-testimony" class="text-gray-700 leading-relaxed whitespace-pre-wrap"></div>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="modal-delete" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div data-modal-backdrop class="absolute inset-0 bg-black/40"></div>
    <div class="modal-panel relative bg-white rounded-2xl shadow-lg max-w-md w-full mx-4 p-6 opacity-0 scale-95 transition-all duration-150">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-plum font-playfair">Confirm Deletion</h2>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        
        <p class="text-sm text-gray-600 mb-2">Are you sure you want to delete this testimony?</p>
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
// ══════════════════════════════════════════════════════════════════
// Modal System
// ══════════════════════════════════════════════════════════════════
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
    
    // View modal
    const viewBtn = e.target.closest('[data-view]');
    if (viewBtn) {
        const data = JSON.parse(viewBtn.getAttribute('data-view'));
        document.getElementById('view-name').textContent = data.name;
        document.getElementById('view-location').textContent = data.location ? '📍 ' + data.location : '';
        document.getElementById('view-email').textContent = data.email || '';
        document.getElementById('view-date').textContent = new Date(data.created_at).toLocaleDateString();
        document.getElementById('view-testimony').textContent = data.testimony;
        
        const statusBadge = document.getElementById('view-status');
        statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        statusBadge.className = 'status-badge status-' + data.status;
    }
    
    // Edit modal
    const editBtn = e.target.closest('[data-edit]');
    if (editBtn) {
        const data = JSON.parse(editBtn.getAttribute('data-edit'));
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-name').value = data.name;
        document.getElementById('edit-location').value = data.location || '';
        document.getElementById('edit-email').value = data.email || '';
        document.getElementById('edit-testimony').value = data.testimony;
        document.getElementById('edit-status').value = data.status;
    }
    
    // Delete modal
    const delBtn = e.target.closest('[data-modal-target="modal-delete"]');
    if (delBtn) {
        document.getElementById('delete-id').value = delBtn.getAttribute('data-id');
        document.getElementById('delete-name').textContent = delBtn.getAttribute('data-name');
    }
});
</script>
