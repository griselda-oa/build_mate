<?php
/**
 * Generate Password Hashes for Test Accounts
 * Access via: http://localhost/build_mate/generate_password_hashes.php
 */

$passwords = [
    'admin123',
    'supplier123',
    'customer123',
    'buyer123',
    'logistics123'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Password Hashes</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .hash-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .password { color: #28a745; font-weight: bold; }
        .hash { color: #666; word-break: break-all; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>🔐 Password Hashes for Test Accounts</h1>
    <p>Copy these hashes into your SQL script:</p>
";

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<div class='hash-box'>";
    echo "<div class='password'>Password: {$password}</div>";
    echo "<div class='hash'>Hash: {$hash}</div>";
    echo "</div>";
}

echo "
    <hr>
    <h2>📝 SQL UPDATE Statements</h2>
    <pre style='background: white; padding: 15px; border-radius: 5px;'>";

echo "-- Update all user passwords\n\n";

$users = [
    ['admin@buildmate.com', 'admin123'],
    ['supplier@buildmate.com', 'supplier123'],
    ['customer@buildmate.com', 'customer123'],
    ['buyer@buildmate.com', 'buyer123'],
    ['logistics@buildmate.com', 'logistics123']
];

foreach ($users as $user) {
    $hash = password_hash($user[1], PASSWORD_DEFAULT);
    echo "UPDATE users SET password_hash = '{$hash}' WHERE email = '{$user[0]}';\n";
}

echo "</pre>
</body>
</html>";
?>
