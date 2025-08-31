<?php
/**
 * Database Configuration File
 * Handles connections to both SFGS and CBT databases
 */

// Database Configuration
$db_config = [
    'sfgs' => [
        'host' => 'sql100.byetcluster.com',
        'database' => 'if0_39795047_sfgs',
        'username' => 'if0_39795047',  // Updated username based on database name
        'password' => 'your_actual_password_here',  // Replace with your actual password
        'charset' => 'utf8mb4'
    ],
    'cbt' => [
        'host' => 'sql100.byetcluster.com',
        'database' => 'if0_39795047_cbt',
        'username' => 'if0_39795047',  // Updated username based on database name
        'password' => 'your_actual_password_here',  // Replace with your actual password
        'charset' => 'utf8mb4'
    ]
];

/**
 * Create database connection
 */
function createConnection($db_name) {
    global $db_config;

    if (!isset($db_config[$db_name])) {
        throw new Exception("Database configuration not found for: $db_name");
    }

    $config = $db_config[$db_name];

    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed for $db_name: " . $e->getMessage());
    }
}

/**
 * Test database connections
 */
function testConnections() {
    $results = [];

    foreach (['sfgs', 'cbt'] as $db_name) {
        try {
            $pdo = createConnection($db_name);
            $pdo->query("SELECT 1");
            $results[$db_name] = true;
        } catch (Exception $e) {
            $results[$db_name] = false;
            $results[$db_name . '_error'] = $e->getMessage();
        }
    }

    return $results;
}

/**
 * Get both database connections
 */
function getDatabaseConnections() {
    return [
        'sfgs' => createConnection('sfgs'),
        'cbt' => createConnection('cbt')
    ];
}
?>