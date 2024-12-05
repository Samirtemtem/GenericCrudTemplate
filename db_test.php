<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once 'config.php';

try {
    // Attempt to connect to the database
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s", 
        DB_HOST, 
        DB_NAME, 
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // Attempt connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Test a simple query
    $stmt = $pdo->query("SELECT 1");
    
    // If we get here, connection is successful
    echo "Database connection successful!";
} catch(PDOException $e) {
    // Detailed error logging
    echo "Connection failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Connection Details:\n";
    echo "Host: " . DB_HOST . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Charset: " . DB_CHARSET . "\n";
}
?>
