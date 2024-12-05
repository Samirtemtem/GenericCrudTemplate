<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pro');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $class = str_replace('\\', '/', $class);
    
    // Possible base paths
    $basePaths = [
        __DIR__ . '/',
        __DIR__ . '/app/'
    ];
    
    // Try to find the file
    foreach ($basePaths as $basePath) {
        $possibleFiles = [
            $basePath . $class . '.php',
            $basePath . strtolower($class) . '.php'
        ];
        
        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }
    
    return false;
});

// Database Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to get foreign key map
function getForeignKeyMap(PDO $pdo, string $tableName): array {
    $query = "
        SELECT 
            column_name, 
            referenced_table_name, 
            referenced_column_name 
        FROM 
            information_schema.key_column_usage 
        WHERE 
            referenced_table_schema = :dbName 
            AND table_name = :tableName 
            AND referenced_table_name IS NOT NULL
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'dbName' => DB_NAME,
        'tableName' => $tableName
    ]);
    
    $foreignKeys = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $foreignKeys[$row['column_name']] = [
            'table' => $row['referenced_table_name'],
            'column' => $row['referenced_column_name']
        ];
    }
    
    return $foreignKeys;
}

// Helper function to get display fields for tables
function getTableDisplayFields(PDO $pdo, string $tableName): string {
    $columns = $pdo->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_COLUMN);
    
    $priorityFields = ['name', 'title', 'label', 'description', 'email', 'username'];
    
    foreach ($priorityFields as $field) {
        if (in_array($field, $columns)) {
            return $field;
        }
    }
    
    // Fallback to first text column or first column
    foreach ($columns as $column) {
        if ($column !== 'id') {
            return $column;
        }
    }
    
    return 'id';
}

// Helper function to check if a column is an image column
function isImageColumn(string $columnName, string $columnType): bool {
    return 
        strpos(strtolower($columnName), 'image') !== false || 
        strpos(strtolower($columnName), 'photo') !== false || 
        strpos(strtolower($columnType), 'blob') !== false;
}
?>
