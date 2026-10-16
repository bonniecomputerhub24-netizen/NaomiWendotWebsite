<?php
/**
 * Migration: Create Family Members System
 * File: 003_family_members.php
 * 
 * Creates a family member login system replacing the access request system
 * - family_members: Store family member accounts with login credentials
 * - Updates family_album_photos to track who uploaded each photo
 * 
 * Safe to re-run - checks table existence before creating
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../../config/db.php';

$pageTitle = "Family Members Migration";
$messages = [];
$errors = [];

// Function to check if table exists
function tableExists($db, $tableName) {
    try {
        $stmt = $db->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute([':tableName' => $tableName]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to create table safely
function createTable($db, $tableName, $sql) {
    try {
        $db->exec($sql);
        return ['success' => true, 'message' => "✅ Created table {$tableName}"];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "❌ Failed to create {$tableName}: " . $e->getMessage()];
    }
}

// Run migration if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $db = getDb();
        
        // Create family_members table
        if (!tableExists($db, 'family_members')) {
            $familyMembersSQL = "
                CREATE TABLE family_members (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Login username',
                    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password',
                    full_name VARCHAR(100) NOT NULL COMMENT 'Full display name',
                    email VARCHAR(255) DEFAULT NULL COMMENT 'Email address',
                    role ENUM('parent', 'child', 'family') DEFAULT 'family' COMMENT 'Family role',
                    avatar_path VARCHAR(255) DEFAULT NULL COMMENT 'Path to profile photo',
                    can_upload BOOLEAN DEFAULT TRUE COMMENT 'Can upload photos',
                    can_delete_own BOOLEAN DEFAULT TRUE COMMENT 'Can delete own photos',
                    can_delete_any BOOLEAN DEFAULT FALSE COMMENT 'Can delete any photo',
                    is_active BOOLEAN DEFAULT TRUE COMMENT 'Account is active',
                    last_login_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Last login time',
                    login_count INT DEFAULT 0 COMMENT 'Number of logins',
                    created_by VARCHAR(100) DEFAULT 'admin' COMMENT 'Who created this account',
                    notes TEXT DEFAULT NULL COMMENT 'Admin notes about this member',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_username (username),
                    INDEX idx_active (is_active),
                    INDEX idx_role (role)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
                COMMENT='Family members with login access to the album'
            ";
            
            $result = createTable($db, 'family_members', $familyMembersSQL);
            if ($result['success']) {
                $messages[] = $result['message'];
            } else {
                $errors[] = $result['message'];
            }
        } else {
            $messages[] = "ℹ️ family_members table already exists";
        }
        
        // Add uploaded_by_member column to family_album_photos if it doesn't exist
        try {
            $stmt = $db->query("SHOW COLUMNS FROM family_album_photos LIKE 'uploaded_by_member'");
            if (!$stmt->fetch()) {
                $db->exec("
                    ALTER TABLE family_album_photos 
                    ADD COLUMN uploaded_by_member INT DEFAULT NULL COMMENT 'ID of family member who uploaded' AFTER uploaded_by,
                    ADD INDEX idx_uploaded_by_member (uploaded_by_member),
                    ADD FOREIGN KEY (uploaded_by_member) REFERENCES family_members(id) ON DELETE SET NULL
                ");
                $messages[] = "✅ Added uploaded_by_member column to family_album_photos";
            } else {
                $messages[] = "ℹ️ uploaded_by_member column already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "❌ Failed to add uploaded_by_member column: " . $e->getMessage();
        }
        
        // Create default admin family member if none exist
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM family_members");
            $count = $stmt->fetch()['count'];
            
            if ($count == 0) {
                // Create default admin family member
                $defaultPassword = 'family2024'; // They should change this
                $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("
                    INSERT INTO family_members 
                    (username, password_hash, full_name, role, can_delete_any, created_by, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    'admin',
                    $hashedPassword,
                    'Family Administrator',
                    'parent',
                    true,
                    'system',
                    'Default admin account - please change password'
                ]);
                
                $messages[] = "✅ Created default admin account (username: admin, password: family2024)";
                $messages[] = "⚠️ <strong>IMPORTANT:</strong> Please change the default password immediately!";
            }
        } catch (PDOException $e) {
            $errors[] = "❌ Failed to create default admin: " . $e->getMessage();
        }
        
        // Summary message
        if (empty($errors)) {
            $messages[] = "<strong>✅ Family Members migration completed successfully!</strong>";
        } else {
            $errors[] = "<strong>⚠️ Migration completed with errors</strong>";
        }
        
    } catch (Exception $e) {
        $errors[] = "❌ Migration failed: " . $e->getMessage();
    }
}

// Get current family members
try {
    $db = getDb();
    if (tableExists($db, 'family_members')) {
        $stmt = $db->query("SELECT id, username, full_name, role, is_active, last_login_at, created_at FROM family_members ORDER BY created_at ASC");
        $familyMembers = $stmt->fetchAll();
    } else {
        $familyMembers = [];
    }
} catch (PDOException $e) {
    $familyMembers = [];
    $errors[] = "⚠️ Could not fetch family members: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Naomi Wendot Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        plum: '#4A1942',
                        gold: '#D4A017',
                        cream: '#FFFDF5',
                        rose: '#FDEAEA',
                        charcoal: '#1C1C1C'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="flex">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 ml-64">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-plum mb-2">👨‍👩‍👧‍👦 Family Members Migration</h1>
                    <p class="text-gray-600">Create family member login system for private album access</p>
                </div>

                <!-- Feature Overview -->
                <div class="mb-6 bg-gradient-to-r from-rose to-cream border border-gold/30 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold text-plum mb-2">🔒 What this creates:</h2>
                    <ul class="list-disc list-inside text-gray-700 text-sm space-y-1">
                        <li><strong>Family Member Accounts:</strong> Username/password login for family members only</li>
                        <li><strong>Role-Based Access:</strong> Parents, children, and family members with different permissions</li>
                        <li><strong>Photo Upload Rights:</strong> Family members can upload their own photos to share</li>
                        <li><strong>Private System:</strong> No public access requests - admin manages all accounts</li>
                        <li><strong>Photo Attribution:</strong> Track which family member uploaded each photo</li>
                    </ul>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($messages)): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-green-900 mb-3">Migration Results</h3>
                        <ul class="space-y-2">
                            <?php foreach ($messages as $message): ?>
                                <li class="text-green-800"><?php echo $message; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-6 rounded">
                        <h3 class="text-lg font-semibold text-red-900 mb-3">Errors</h3>
                        <ul class="space-y-2">
                            <?php foreach ($errors as $error): ?>
                                <li class="text-red-800"><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Run Migration Button -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-plum mb-4">🚀 Run Migration</h2>
                    <p class="text-gray-600 mb-4">
                        This will create the family member system and set up a default admin account.
                    </p>
                    
                    <form method="POST" onsubmit="return confirm('Create Family Members system?');">
                        <button 
                            type="submit" 
                            name="run_migration" 
                            class="bg-plum hover:bg-opacity-90 text-cream font-semibold px-6 py-3 rounded-lg transition-colors flex items-center gap-2"
                        >
                            👨‍👩‍👧‍👦 Create Family System
                        </button>
                    </form>
                </div>

                <!-- Current Family Members -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-plum to-plum/90 text-cream">
                        <h2 class="text-xl font-bold">Current Family Members</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (!empty($familyMembers)): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Username</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Full Name</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Role</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Last Login</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($familyMembers as $member): ?>
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($member['username']); ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($member['full_name']); ?></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php 
                                                        echo $member['role'] === 'parent' ? 'bg-plum/20 text-plum' : 
                                                            ($member['role'] === 'child' ? 'bg-gold/20 text-plum' : 'bg-gray-100 text-gray-800'); 
                                                    ?>">
                                                        <?php echo ucfirst($member['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php 
                                                        echo $member['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; 
                                                    ?>">
                                                        <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <?php echo $member['last_login_at'] ? date('M j, Y', strtotime($member['last_login_at'])) : 'Never'; ?>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <?php echo date('M j, Y', strtotime($member['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                </svg>
                                <p>No family members found. Run the migration to create the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="../dashboard.php" class="text-plum hover:text-gold font-medium transition-colors">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>