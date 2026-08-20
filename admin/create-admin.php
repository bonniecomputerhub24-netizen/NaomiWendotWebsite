<?php
/**
 * Create New Admin User
 * One-time script to create admin accounts
 * DELETE THIS FILE after creating your admin users for security!
 */

require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
        $error = 'All fields are required';
    } else {
        try {
            $db = getDb();
            
            // Check if username or email already exists
            $checkStmt = $db->prepare("SELECT id FROM admin_users WHERE username = :username OR email = :email");
            $checkStmt->execute([':username' => $username, ':email' => $email]);
            
            if ($checkStmt->fetch()) {
                $error = 'Username or email already exists';
            } else {
                // Create new admin user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("
                    INSERT INTO admin_users (username, password, email, full_name, created_at, is_active)
                    VALUES (:username, :password, :email, :full_name, NOW(), 1)
                ");
                
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':email' => $email,
                    ':full_name' => $fullName
                ]);
                
                $message = "Admin user created successfully! Username: $username";
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get existing admins
try {
    $db = getDb();
    $admins = $db->query("SELECT id, username, email, full_name, created_at FROM admin_users ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        plum: '#4A1942',
                        gold: '#D4A017',
                        cream: '#FFFDF5'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Warning Banner -->
        <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
            <p class="font-bold">⚠️ SECURITY WARNING</p>
            <p class="text-sm">Delete this file (create-admin.php) after creating your admin users!</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Create Admin Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-plum mb-4">Create Admin User</h2>
                
                <?php if ($message): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                        <p class="text-xs text-gray-500 mt-1">At least 6 characters</p>
                    </div>
                    
                    <button type="submit" class="w-full bg-plum text-white py-3 rounded-lg font-semibold hover:bg-opacity-90">
                        Create Admin User
                    </button>
                </form>
            </div>
            
            <!-- Existing Admins -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-plum mb-4">Existing Admins</h2>
                
                <?php if (empty($admins)): ?>
                    <p class="text-gray-500">No admin users found</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($admins as $admin): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="font-bold text-plum"><?php echo htmlspecialchars($admin['full_name']); ?></p>
                                <p class="text-sm text-gray-600">Username: <?php echo htmlspecialchars($admin['username']); ?></p>
                                <p class="text-sm text-gray-600">Email: <?php echo htmlspecialchars($admin['email']); ?></p>
                                <p class="text-xs text-gray-400">Created: <?php echo htmlspecialchars($admin['created_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <a href="login.php" class="block text-center bg-gold text-plum py-2 rounded-lg font-semibold hover:bg-opacity-90">
                        Go to Login
                    </a>
                </div>
            </div>
            
        </div>
        
        <!-- Password Hash Tool -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-xl font-bold text-plum mb-4">Password Hash Generator (for SQL)</h3>
            <div class="flex gap-2">
                <input type="text" id="plainPassword" placeholder="Enter password" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                <button onclick="generateHash()" class="bg-plum text-white px-6 py-2 rounded-lg font-semibold hover:bg-opacity-90">
                    Generate Hash
                </button>
            </div>
            <div id="hashResult" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                <p class="text-sm font-semibold mb-2">SQL Update Command:</p>
                <code id="hashCode" class="text-xs break-all"></code>
            </div>
        </div>
        
    </div>
    
    <script>
    async function generateHash() {
        const password = document.getElementById('plainPassword').value;
        if (!password) {
            alert('Please enter a password');
            return;
        }
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=hash&password=' + encodeURIComponent(password)
            });
            const data = await response.json();
            
            if (data.hash) {
                document.getElementById('hashResult').classList.remove('hidden');
                document.getElementById('hashCode').textContent = 
                    `UPDATE admin_users SET password = '${data.hash}' WHERE username = 'naomi';`;
            }
        } catch (e) {
            alert('Error generating hash');
        }
    }
    </script>
    
    <?php
    // Handle hash generation via AJAX
    if (isset($_POST['action']) && $_POST['action'] === 'hash') {
        header('Content-Type: application/json');
        echo json_encode(['hash' => password_hash($_POST['password'], PASSWORD_DEFAULT)]);
        exit;
    }
    ?>
</body>
</html>
