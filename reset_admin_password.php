<?php
/**
 * Reset Admin Password Script
 * Access via: http://localhost/build_mate/reset_admin_password.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin Password</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔐 Reset Admin Password</h1>
";

// Load config
$configFile = __DIR__ . '/settings/config.php';
$config = require $configFile;
$dbConfig = $config['db'];

// Connect using socket
$socketPath = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';

try {
    if (file_exists($socketPath)) {
        $dsn = "mysql:unix_socket={$socketPath};dbname={$dbConfig['name']};charset=utf8mb4";
    } else {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<div class='success'>✅ Connected to database</div>";
    
    // Get current admin
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role='admin' LIMIT 1");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "<div class='error'>❌ No admin user found in database</div>";
        exit;
    }
    
    echo "<div class='info'>";
    echo "<strong>Current Admin:</strong><br>";
    echo "ID: " . htmlspecialchars($admin['id']) . "<br>";
    echo "Name: " . htmlspecialchars($admin['name']) . "<br>";
    echo "Email: " . htmlspecialchars($admin['email']) . "<br>";
    echo "</div>";
    
    // Check if we should reset password
    if (isset($_POST['reset'])) {
        $newPassword = $_POST['password'] ?? 'admin123';
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $admin['id']]);
        
        echo "<div class='success'>";
        echo "✅ <strong>Password Updated Successfully!</strong><br><br>";
        echo "<strong>New Login Credentials:</strong><br>";
        echo "Email: <code>" . htmlspecialchars($admin['email']) . "</code><br>";
        echo "Password: <code>" . htmlspecialchars($newPassword) . "</code><br><br>";
        echo "<a href='/build_mate/login' style='color: #007bff; font-weight: bold;'>→ Go to Login Page</a>";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<strong>⚠️ Security Note:</strong> Delete this file after use:<br>";
        echo "<code>rm /Applications/XAMPP/xamppfiles/htdocs/build_mate/reset_admin_password.php</code>";
        echo "</div>";
    } else {
        // Show form
        echo "<form method='POST'>";
        echo "<div class='info'>";
        echo "<p><strong>Choose a new password for the admin account:</strong></p>";
        echo "<input type='text' name='password' value='admin123' style='width: 100%; padding: 10px; font-size: 16px; margin: 10px 0;'>";
        echo "<br>";
        echo "<button type='submit' name='reset' class='btn'>Reset Password</button>";
        echo "</div>";
        echo "</form>";
        
        echo "<div class='info'>";
        echo "<strong>💡 Tip:</strong> The default password 'admin123' is suggested for easy testing.<br>";
        echo "Change it to something secure after logging in!";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body></html>";
?>
