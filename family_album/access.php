<?php
/**
 * Family Member Login Page
 * Private family-only access to the album
 */

session_start();

// Debug path information
$configPath = __DIR__ . '/../config/db.php';
$functionsPath = __DIR__ . '/../includes/functions.php';

if (!file_exists($configPath)) {
    die("Config file not found at: " . $configPath . " (Current dir: " . __DIR__ . ")");
}

require_once $configPath;
require_once $functionsPath;

$error = '';
$loggedOut = isset($_GET['logged_out']);

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $loginIdentifier = trim($_POST['username'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';
    
    // Debug logging (remove in production)
    error_log("Login attempt: identifier=" . $loginIdentifier);
    
    if (empty($loginIdentifier) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        try {
            $db = getDb();
            // Check if login identifier is username or email
            $stmt = $db->prepare("
                SELECT * FROM family_members 
                WHERE (username = ? OR email = ?) AND is_active = 1
            ");
            $stmt->execute([$loginIdentifier, $loginIdentifier]);
            $member = $stmt->fetch();
            
            // Debug logging
            error_log("Query result: " . ($member ? "User found: " . $member['username'] : "User not found"));
            
            if ($member && password_verify($password, $member['password_hash'])) {
                // Debug logging
                error_log("Password verified for user: " . $member['username']);
                
                // Login successful
                $_SESSION['family_member_id'] = $member['id'];
                $_SESSION['family_member_username'] = $member['username'];
                $_SESSION['family_member_name'] = $member['full_name'];
                $_SESSION['family_member_role'] = $member['role'];
                $_SESSION['family_member_permissions'] = [
                    'can_upload' => $member['can_upload'],
                    'can_delete_own' => $member['can_delete_own'],
                    'can_delete_any' => $member['can_delete_any']
                ];
                
                // Update login stats
                $updateStmt = $db->prepare("
                    UPDATE family_members 
                    SET last_login_at = NOW(), login_count = login_count + 1 
                    WHERE id = ?
                ");
                $updateStmt->execute([$member['id']]);
                
                header('Location: index');
                exit;
            } else {
                // Debug logging
                if ($member) {
                    error_log("Password verification failed for user: " . $member['username']);
                } else {
                    error_log("User not found for identifier: " . $loginIdentifier);
                }
                $error = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            error_log("Family Login Error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}

$pageTitle = "Family Album Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — Naomi Wendot</title>
    
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
                    },
                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif'],
                        century: ['Century Gothic', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: linear-gradient(135deg, #FFFDF5 0%, #FDEAEA 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-br from-plum to-plum/80 shadow-lg">
                <svg class="h-8 w-8 text-cream" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-plum font-montserrat">
                Family Album
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Welcome home! Sign in to access our private family memories.
            </p>
        </div>

        <!-- Messages -->
        <?php if ($loggedOut): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-blue-700">You have been logged out successfully. Please sign in again to continue.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent transition-all"
                        placeholder="Enter your username or email"
                        required
                        autofocus
                    />
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-plum focus:border-transparent transition-all"
                        placeholder="Enter your password"
                        required
                    />
                </div>
                
                <button 
                    type="submit" 
                    name="login"
                    class="w-full bg-gradient-to-r from-plum to-plum/90 text-cream py-3 px-4 rounded-lg font-medium hover:from-plum/90 hover:to-plum transition-all duration-300 transform hover:-translate-y-1 shadow-lg"
                >
                    Sign In to Family Album
                </button>
            </form>
        </div>

        <!-- Help Section -->
        <div class="bg-gradient-to-r from-rose to-cream border border-gold/30 rounded-lg p-6 text-center">
            <h3 class="text-lg font-semibold text-plum mb-2 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Need Help?
            </h3>
            <p class="text-sm text-gray-700 mb-3">
                This is a private family album. If you don't have login credentials, please contact the family administrator.
            </p>
            <p class="text-xs text-gray-600">
                Only family members can access this album and share memories together.
            </p>
        </div>

        <!-- Back to Main Site -->
        <div class="text-center">
            <a href="/" class="text-plum hover:text-gold font-medium transition-colors text-sm">
                ← Back to Main Website
            </a>
        </div>
    </div>
</body>
</html>