<?php
/**
 * Password Hash Generator
 * Use this to generate password hashes for the database
 * DELETE THIS FILE after use for security!
 */

$generated = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $generated = $hash;
    }
}

// Generate hash for 'naomi2024' on page load
$defaultHash = password_hash('naomi2024', PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
        
        <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
            <p class="font-bold">⚠️ DELETE THIS FILE AFTER USE!</p>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Password Hash Generator</h1>
        
        <!-- Default Hash for naomi2024 -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-blue-900 mb-3">Hash for 'naomi2024':</h2>
            <div class="bg-white p-3 rounded border border-blue-300 mb-4">
                <code class="text-xs break-all text-blue-800"><?php echo $defaultHash; ?></code>
            </div>
            <h3 class="font-bold text-blue-900 mb-2">Run this SQL command:</h3>
            <div class="bg-gray-800 p-4 rounded">
                <code class="text-green-400 text-sm break-all">
UPDATE admin_users SET password = '<?php echo $defaultHash; ?>' WHERE username = 'naomi';
                </code>
            </div>
            <button onclick="copySQL()" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Copy SQL Command
            </button>
        </div>
        
        <!-- Custom Password Generator -->
        <div class="border-t pt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Generate Custom Hash</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Enter Password:</label>
                    <input 
                        type="text" 
                        name="password" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter password to hash"
                    >
                </div>
                <button type="submit" class="w-full bg-gray-800 text-white py-3 rounded-lg font-semibold hover:bg-gray-700">
                    Generate Hash
                </button>
            </form>
            
            <?php if ($generated): ?>
                <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-bold text-green-900 mb-2">Generated Hash:</h3>
                    <div class="bg-white p-3 rounded border border-green-300">
                        <code class="text-xs break-all"><?php echo $generated; ?></code>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 text-center">
            <a href="login.php" class="text-blue-600 hover:underline">Go to Login</a>
        </div>
        
    </div>
    
    <script>
    function copySQL() {
        const sql = `UPDATE admin_users SET password = '<?php echo $defaultHash; ?>' WHERE username = 'naomi';`;
        navigator.clipboard.writeText(sql).then(() => {
            alert('SQL command copied to clipboard!');
        });
    }
    </script>
</body>
</html>
