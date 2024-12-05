<?php
require_once "config.php";

function detectDisplayField($columns) {
    $priorityFields = [
        'name', 'title', 'label', 'description', 'email', 'username', 
        'first_name', 'last_name', 'full_name', 'display_name'
    ];

    foreach ($priorityFields as $field) {
        foreach ($columns as $column) {
            if (strtolower($column['Field']) === $field) {
                return $column['Field'];
            }
        }
    }

    // If no priority field found, return the first non-id text field
    foreach ($columns as $column) {
        if (
            $column['Field'] !== 'id' && 
            (strpos(strtolower($column['Type']), 'varchar') !== false || 
             strpos(strtolower($column['Type']), 'text') !== false)
        ) {
            return $column['Field'];
        }
    }

    // Fallback to first column
    return $columns[0]['Field'];
}

function generateModelClass($tableName, $columns, $pdo) {
    // Determine primary key
    $primaryKey = 'id';
    foreach ($columns as $column) {
        if ($column['Key'] === 'PRI') {
            $primaryKey = $column['Field'];
            break;
        }
    }

    // Detect display field
    $displayField = detectDisplayField($columns);

    // Detect foreign key relationships
    $foreignKeys = [];
    $foreignKeyQuery = "
        SELECT 
            column_name, 
            referenced_table_name, 
            referenced_column_name 
        FROM 
            information_schema.key_column_usage 
        WHERE 
            referenced_table_schema = DATABASE() 
            AND table_name = :tableName 
            AND referenced_table_name IS NOT NULL
    ";
    $stmt = $pdo->prepare($foreignKeyQuery);
    $stmt->execute(['tableName' => $tableName]);
    $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($relations as $relation) {
        $foreignKeys[$relation['column_name']] = [
            'model' => '\\App\\Models\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $relation['referenced_table_name']))) . 'Model',
            'table' => $relation['referenced_table_name'],
            'key' => $relation['referenced_column_name']
        ];
    }

    // Generate model code
    $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Model';
    
    $code = "<?php
namespace App\\Models;

use App\\Core\\Model;

class {$modelName} extends Model {
    protected static \$tableName = '{$tableName}';
    protected static \$primaryKey = '{$primaryKey}';
    protected static \$displayField = '{$displayField}';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return " . var_export($foreignKeys, true) . ";
    }

    /**
     * Validation rules for this model
     * Override this method to add custom validation
     * @param array \$data Data to validate
     * @return array Validated and potentially modified data
     */
    protected function validate(array \$data): array {
        \$errors = [];
        \$primaryKey = self::\$primaryKey;

        // Add your custom validation logic here
        " . implode("\n        ", array_map(function($column) use ($primaryKey) {
            $rules = [];
            
            // Required fields (excluding primary key)
            if ($column['Null'] === 'NO' && $column['Field'] !== $primaryKey) {
                $rules[] = "if (empty(\$data['{$column['Field']}'])) {
            \$errors['{$column['Field']}'] = '" . ucfirst(str_replace('_', ' ', $column['Field'])) . " is required';
        }";
            }
            
            // Type-specific validations
            if (strpos(strtolower($column['Type']), 'int') !== false) {
                $rules[] = "if (!is_numeric(\$data['{$column['Field']}'])) {
            \$errors['{$column['Field']}'] = '" . ucfirst(str_replace('_', ' ', $column['Field'])) . " must be a number';
        }";
            }
            
            // Email validation
            if (strpos(strtolower($column['Field']), 'email') !== false) {
                $rules[] = "if (!filter_var(\$data['{$column['Field']}'], FILTER_VALIDATE_EMAIL)) {
            \$errors['{$column['Field']}'] = 'Invalid email format';
        }";
            }
            
            return implode("\n        ", $rules);
        }, $columns)) . "

        if (!empty(\$errors)) {
            throw new \Exception(json_encode(\$errors));
        }

        return \$data;
    }
}";

    // Ensure models directory exists
    $modelsDir = __DIR__ . '/app/models';
    if (!is_dir($modelsDir)) {
        mkdir($modelsDir, 0777, true);
    }

    // Write model file
    $filename = $modelsDir . "/{$modelName}.php";
    file_put_contents($filename, $code);

    return $modelName;
}

try {
    // Get all tables in the database
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $generatedModels = [];
    foreach ($tables as $table) {
        // Skip system tables or views if needed
        if (in_array($table, ['migrations', 'schema_migrations'])) continue;

        // Get table columns
        $columnsStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate model
        $modelName = generateModelClass($table, $columns, $pdo);
        $generatedModels[] = $modelName;
    }

    echo "Successfully generated the following models:\n";
    foreach ($generatedModels as $model) {
        echo "- {$model}\n";
    }

} catch (Exception $e) {
    die("Error generating models: " . $e->getMessage());
}
