<?php
/**
 * Family Member Management
 * Admin interface for managing family member accounts
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Family Members";
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Create new family member
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'family';
        $canUpload = isset($_POST['can_upload']) ? 1 : 0;
        $canDeleteOwn = isset($_POST['can_delete_own']) ? 1 : 0;
        $canDeleteAny = isset($_POST['can_delete_any']) ? 1 : 0;
        
        if (empty($username) || empty($password) || empty($fullName)) {
            $error = 'Username, password, and full name are required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $db = getDb();
                
                // Check if username already exists
                $stmt = $db->prepare("SELECT id FROM family_members WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists.';
                } else {
                    // Create the account
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("
                        INSERT INTO family_members 
                        (username, password_hash, full_name, email, role, can_upload, can_delete_own, can_delete_any, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $username,
                        $hashedPassword,
                        $fullName,
                        $email ?: null,
                        $role,
                        $canUpload,
                        $canDeleteOwn,
                        $canDeleteAny,
                        $_SESSION['admin_username'] ?? 'admin'
                    ]);
                    
                    $message = "Family member '{$fullName}' created successfully!";
                }
            } catch (PDOException $e) {
                error_log("Create family member error: " . $e->getMessage());
                $error = "Database error occurred.";
            }
        }
    }
    
    // Toggle member active status
    elseif ($action === 'toggle_status') {
        $memberId = (int)($_POST['member_id'] ?? 0);
        
        try {
            $db = getDb();
            $stmt = $db->prepare("UPDATE family_members SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$memberId]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Member status updated successfully.";
            }
        } catch (PDOException $e) {
            error_log("Toggle status error: " . $e->getMessage());
            $error = "Database error occurred.";
        }
    }
    
    // Reset password
    elseif ($action === 'reset_password') {
        $memberId = (int)($_POST['member_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        
        if (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $db = getDb();
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("UPDATE family_members SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $memberId]);
                
                if ($stmt->rowCount() > 0) {
                    $message = "Password reset successfully.";
                }
            } catch (PDOException $e) {
                error_log("Reset password error: " . $e->getMessage());
                $error = "Database error occurred.";
            }
        }
    }
}
// Get family members
$familyMembers = [];
$tableExists = false;
try {
    $db = getDb();
    
    // First check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'family_members'");
    $tableExists = $stmt->fetch() !== false;
    
    if ($tableExists) {
        $stmt = $db->query("
            SELECT fm.*, 
                   COUNT(fap.id) as photo_count,
                   MAX(fap.created_at) as last_upload
            FROM family_members fm
            LEFT JOIN family_album_photos fap ON fm.id = fap.uploaded_by_member
            GROUP BY fm.id
            ORDER BY fm.created_at DESC
        ");
        $familyMembers = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Family members fetch error: " . $e->getMessage());
    $error = "Failed to load family members: " . $e->getMessage();
}

// Get statistics
$stats = [
    'total_members' => count($familyMembers),
    'active_members' => count(array_filter($familyMembers, fn($m) => $m['is_active'])),
    'total_photos' => array_sum(array_column($familyMembers, 'photo_count'))
];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="flex">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 ml-64 pt-20">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <a href="manage.php" class="text-plum hover:text-gold transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <div>
                                <h1 class="text-3xl font-bold text-plum font-montserrat flex items-center gap-3">
                                    <svg class="w-8 h-8 text-plum" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Family Members
                                </h1>
                                <p class="text-gray-600 mt-1">Manage family member accounts and permissions</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button 
                                onclick="showCreateModal()"
                                class="bg-plum text-cream px-4 py-2 rounded-lg font-medium hover:bg-opacity-90 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Family Member
                            </button>
                        </div>
                    </div>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-r from-plum to-plum/90 text-cream p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-cream/70 text-sm">Total Members</p>
                            <p class="text-2xl font-bold"><?php echo $stats['total_members']; ?></p>
                        </div>
                        <svg class="w-8 h-8 text-cream/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/70 text-sm">Active Members</p>
                            <p class="text-2xl font-bold"><?php echo $stats['active_members']; ?></p>
                        </div>
                        <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-gold to-gold/90 text-plum p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-plum/70 text-sm">Photos Shared</p>
                            <p class="text-2xl font-bold"><?php echo $stats['total_photos']; ?></p>
                        </div>
                        <svg class="w-8 h-8 text-plum/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <?php if (!$tableExists): ?>
                <!-- Migration Required Notice -->
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded mb-6">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-red-900">Migration Required</h3>
                            <p class="text-red-700 mt-1">The family_members table doesn't exist. Please run the migration first.</p>
                            <a href="../migrations/003_family_members.php" class="inline-block mt-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                Run Family Members Migration
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-700"><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Family Members Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                    <h2 class="text-xl font-bold">Family Members</h2>
                </div>

                <?php if (empty($familyMembers)): ?>
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Family Members</h3>
                        <p class="text-gray-500 mb-4">Create family member accounts to allow access to the album.</p>
                        <button 
                            onclick="showCreateModal()"
                            class="bg-plum text-cream px-6 py-2 rounded-lg font-medium hover:bg-opacity-90"
                        >
                            Add First Family Member
                        </button>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($familyMembers as $member): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($member['full_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @<?php echo htmlspecialchars($member['username']); ?>
                                            </div>
                                            <?php if ($member['email']): ?>
                                                <div class="text-xs text-gray-400">
                                                    <?php echo htmlspecialchars($member['email']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $roleColors = [
                                            'parent' => 'bg-plum/20 text-plum',
                                            'child' => 'bg-gold/20 text-plum',
                                            'family' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $roleColor = $roleColors[$member['role']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $roleColor; ?>">
                                            <?php echo ucfirst($member['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="space-y-1">
                                            <?php if ($member['can_upload']): ?>
                                                <div class="flex items-center text-green-600">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-xs">Upload</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($member['can_delete_own']): ?>
                                                <div class="flex items-center text-blue-600">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-xs">Delete Own</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($member['can_delete_any']): ?>
                                                <div class="flex items-center text-red-600">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-xs">Delete Any</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>
                                            <div class="text-sm"><?php echo $member['photo_count']; ?> photos shared</div>
                                            <?php if ($member['last_login_at']): ?>
                                                <div class="text-xs">Last login: <?php echo date('M j, Y', strtotime($member['last_login_at'])); ?></div>
                                            <?php else: ?>
                                                <div class="text-xs text-orange-500">Never logged in</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <button 
                                                type="submit"
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $member['is_active'] ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'; ?> transition-colors"
                                            >
                                                <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <button 
                                                onclick="showPasswordModal(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['full_name']); ?>')"
                                                class="text-gold hover:text-plum font-medium"
                                            >
                                                Reset Password
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<!-- Create Member Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Family Member</h3>
        <form method="POST" id="createForm">
            <input type="hidden" name="action" value="create">
            
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Username *
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                        placeholder="johndoe"
                    />
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password *
                    </label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                        placeholder="Minimum 6 characters"
                    />
                </div>
                
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name *
                    </label>
                    <input 
                        type="text" 
                        id="full_name"
                        name="full_name" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                        placeholder="John Doe"
                    />
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email (Optional)
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                        placeholder="john@example.com"
                    />
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Family Role
                    </label>
                    <select 
                        id="role"
                        name="role" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                    >
                        <option value="family">Family Member</option>
                        <option value="child">Child</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="can_upload" checked class="text-plum focus:ring-plum border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Can upload photos</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="can_delete_own" checked class="text-plum focus:ring-plum border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Can delete own photos</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="can_delete_any" class="text-plum focus:ring-plum border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Can delete any photo</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button 
                    type="button" 
                    onclick="closeCreateModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-plum text-cream rounded-lg hover:bg-opacity-90"
                >
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reset Password</h3>
        <form method="POST" id="passwordForm">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="member_id" id="passwordMemberId">
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    Reset password for <span id="passwordMemberName" class="font-medium"></span>
                </p>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password *
                </label>
                <input 
                    type="password" 
                    id="new_password"
                    name="new_password" 
                    required
                    minlength="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum"
                    placeholder="Minimum 6 characters"
                />
            </div>
            
            <div class="flex justify-end gap-3">
                <button 
                    type="button" 
                    onclick="closePasswordModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-gold text-plum rounded-lg hover:bg-opacity-90"
                >
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
function showCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createForm').reset();
}

function showPasswordModal(memberId, memberName) {
    document.getElementById('passwordMemberId').value = memberId;
    document.getElementById('passwordMemberName').textContent = memberName;
    document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.getElementById('passwordForm').reset();
}

// Close modals when clicking outside
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});

document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closePasswordModal();
});
</script>