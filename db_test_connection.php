<?php
/**
 * Database Connection Test Script
 * Access via: http://localhost/build_mate/db_test_connection.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .test-item { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 Database Connection Test</h1>
    <p>Testing connection to Build Mate database...</p>
";

// Load config
$configFile = __DIR__ . '/settings/config.php';
if (!file_exists($configFile)) {
    echo "<div class='error'>❌ Config file not found: {$configFile}</div>";
    exit;
}

$config = require $configFile;
$dbConfig = $config['db'];

echo "<div class='info'>";
echo "<h2>📋 Configuration Details</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Host</td><td>" . htmlspecialchars($dbConfig['host']) . "</td></tr>";
echo "<tr><td>Port</td><td>" . htmlspecialchars($dbConfig['port']) . "</td></tr>";
echo "<tr><td>Database</td><td>" . htmlspecialchars($dbConfig['name']) . "</td></tr>";
echo "<tr><td>User</td><td>" . htmlspecialchars($dbConfig['user']) . "</td></tr>";
echo "<tr><td>Password</td><td>" . (empty($dbConfig['pass']) ? '(empty)' : '****** (set)') . "</td></tr>";
echo "</table>";
echo "</div>";

// Test 1: Check if mysqli extension is loaded
echo "<div class='test-item'>";
echo "<h2>Test 1: PHP mysqli Extension</h2>";
if (extension_loaded('mysqli')) {
    echo "<div class='success'>✅ mysqli extension is loaded</div>";
} else {
    echo "<div class='error'>❌ mysqli extension is NOT loaded. Please enable it in php.ini</div>";
    exit;
}
echo "</div>";

// Test 2: Try to connect
echo "<div class='test-item'>";
echo "<h2>Test 2: Database Connection</h2>";

$mysqli = new mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['pass'],
    $dbConfig['name'],
    $dbConfig['port']
);

if ($mysqli->connect_error) {
    echo "<div class='error'>";
    echo "❌ <strong>Connection Failed!</strong><br>";
    echo "<strong>Error Code:</strong> " . $mysqli->connect_errno . "<br>";
    echo "<strong>Error Message:</strong> " . htmlspecialchars($mysqli->connect_error) . "<br><br>";
    
    echo "<h3>💡 Troubleshooting Tips:</h3>";
    echo "<ul>";
    
    if ($mysqli->connect_errno == 1045) {
        echo "<li><strong>Access Denied (1045):</strong> Username or password is incorrect</li>";
        echo "<li>Try connecting via phpMyAdmin to verify credentials</li>";
        echo "<li>Check if user 'goa' exists and has correct password</li>";
    } elseif ($mysqli->connect_errno == 2002) {
        echo "<li><strong>Connection Refused (2002):</strong> MySQL is not running or wrong port</li>";
        echo "<li>Check XAMPP Control Panel - MySQL should be running</li>";
        echo "<li>Try port 3306 instead of 3307</li>";
    } elseif ($mysqli->connect_errno == 1049) {
        echo "<li><strong>Unknown Database (1049):</strong> Database 'goa' doesn't exist</li>";
        echo "<li>Create the database in phpMyAdmin</li>";
    } elseif ($mysqli->connect_errno == 2003) {
        echo "<li><strong>Can't Connect (2003):</strong> MySQL server is not reachable</li>";
        echo "<li>Verify MySQL is running in XAMPP</li>";
    }
    
    echo "</ul>";
    echo "</div>";
    exit;
}

echo "<div class='success'>✅ <strong>Connection Successful!</strong></div>";
echo "<div class='info'>";
echo "<strong>Server Info:</strong> " . htmlspecialchars($mysqli->server_info) . "<br>";
echo "<strong>Host Info:</strong> " . htmlspecialchars($mysqli->host_info) . "<br>";
echo "<strong>Protocol Version:</strong> " . $mysqli->protocol_version . "<br>";
echo "<strong>Character Set:</strong> " . $mysqli->character_set_name();
echo "</div>";
echo "</div>";

// Test 3: Check if tables exist
echo "<div class='test-item'>";
echo "<h2>Test 3: Database Tables</h2>";

$result = $mysqli->query("SHOW TABLES");
if ($result) {
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    if (count($tables) > 0) {
        echo "<div class='success'>✅ Found " . count($tables) . " tables in database</div>";
        echo "<div class='info'>";
        echo "<strong>Tables:</strong><br>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Check for required tables
        $requiredTables = ['users', 'products', 'orders', 'categories', 'suppliers'];
        $missingTables = array_diff($requiredTables, $tables);
        
        if (empty($missingTables)) {
            echo "<div class='success'>✅ All required tables exist</div>";
        } else {
            echo "<div class='error'>⚠️ Missing required tables: " . implode(', ', $missingTables) . "</div>";
        }
    } else {
        echo "<div class='error'>⚠️ Database is empty - no tables found</div>";
        echo "<div class='info'>You need to import your database schema</div>";
    }
} else {
    echo "<div class='error'>❌ Error checking tables: " . htmlspecialchars($mysqli->error) . "</div>";
}
echo "</div>";

// Test 4: Check users table
echo "<div class='test-item'>";
echo "<h2>Test 4: Users Table</h2>";

$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<div class='success'>✅ Users table accessible</div>";
    echo "<div class='info'><strong>Total users:</strong> " . $row['count'] . "</div>";
    
    // Check for admin users
    $adminResult = $mysqli->query("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 5");
    if ($adminResult && $adminResult->num_rows > 0) {
        echo "<div class='success'>✅ Found admin users:</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        while ($admin = $adminResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['name']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>⚠️ No admin users found in database</div>";
    }
} else {
    echo "<div class='error'>❌ Error accessing users table: " . htmlspecialchars($mysqli->error) . "</div>";
}
echo "</div>";

// Test 5: Check PDO Connection (what the app actually uses)
echo "<div class='test-item'>";
echo "<h2>Test 5: PDO Connection (Application Method)</h2>";

try {
    $host = $dbConfig['host'];
    
    // Try to find socket file
    $socketPaths = [
        '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        '/tmp/mysql.sock',
        '/var/mysql/mysql.sock',
    ];
    
    $socketPath = null;
    foreach ($socketPaths as $path) {
        if (file_exists($path)) {
            $socketPath = $path;
            echo "<div class='success'>✅ Found MySQL socket: " . htmlspecialchars($path) . "</div>";
            break;
        }
    }
    
    // Build DSN
    if ($socketPath && ($host === 'localhost' || $host === '127.0.0.1')) {
        $dsn = sprintf(
            'mysql:unix_socket=%s;dbname=%s;charset=utf8mb4',
            $socketPath,
            $dbConfig['name']
        );
        echo "<div class='info'><strong>Using UNIX socket connection (recommended for XAMPP)</strong></div>";
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $dbConfig['port'],
            $dbConfig['name']
        );
        echo "<div class='info'><strong>Using TCP connection</strong></div>";
    }
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "<div class='info'>DSN: " . htmlspecialchars($dsn) . "</div>";
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    
    echo "<div class='success'>✅ PDO connection successful!</div>";
    
    // Try a query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo "<div class='success'>✅ PDO query successful</div>";
    echo "<div class='info'>Query returned: " . $result['count'] . " users</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ PDO connection failed!</div>";
    echo "<div class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>";
    echo "<h3>💡 This is the actual error your app is seeing!</h3>";
    echo "<p>The mysqli connection worked but PDO failed. This could be:</p>";
    echo "<ul>";
    echo "<li>PDO MySQL driver not installed/enabled</li>";
    echo "<li>Different authentication method required</li>";
    echo "<li>Socket vs TCP connection issue</li>";
    echo "</ul>";
    echo "</div>";
}
echo "</div>";

// Test 6: Check Application DB class
echo "<div class='test-item'>";
echo "<h2>Test 6: Application DB Class</h2>";

if (file_exists(__DIR__ . '/classes/DB.php')) {
    require_once __DIR__ . '/classes/DB.php';
    
    try {
        $db = \App\DB::getInstance();
        echo "<div class='success'>✅ DB class loaded successfully</div>";
        
        // Try a simple query
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "<div class='success'>✅ DB class query successful</div>";
        echo "<div class='info'>Query returned: " . $result['count'] . " users</div>";
    } catch (\Exception $e) {
        echo "<div class='error'>❌ DB class error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='info'><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></div>";
    }
} else {
    echo "<div class='error'>❌ DB class file not found</div>";
}
echo "</div>";

$mysqli->close();

echo "<hr>";
echo "<h2>🎉 Test Complete!</h2>";
echo "<div class='success'>";
echo "<strong>Next Steps:</strong><br>";
echo "<ul>";
echo "<li>If all tests passed, your database is working correctly</li>";
echo "<li>Try logging in at: <a href='" . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/login') . "'>Login Page</a></li>";
echo "<li>Delete this test file after verification: <code>db_test_connection.php</code></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
