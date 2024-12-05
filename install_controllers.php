<?php
require_once "config.php";

function generateController($modelClass) {
    // Remove 'Model' suffix if present
    $baseClassName = str_replace('Model', '', $modelClass);
    $controllerName = $baseClassName . 'Controller';
    
    $code = "<?php
namespace App\\Controllers;

use App\\Core\\CrudController;
use App\\Models\\{$modelClass};

/**
 * Generated controller class for {$baseClassName}
 */
class {$controllerName} extends CrudController {
    protected \$modelClass = {$modelClass}::class;
    
    /**
     * Override this method to customize the validation rules
     */
    protected function validateData(array \$data, bool \$isUpdate = false): array {
        // Add custom validation rules here
        return \$data;
    }
    
    /**
     * Override this method to customize how data is processed before saving
     */
    protected function beforeSave(array \$data, bool \$isUpdate = false): array {
        // Add custom data processing here
        return \$data;
    }
    
    /**
     * Override this method to perform actions after successful save
     */
    protected function afterSave(\$id, array \$data, bool \$isUpdate = false): void {
        // Add post-save actions here
    }
}";
    
    $controllerDir = __DIR__ . '/app/controllers';
    if (!is_dir($controllerDir)) {
        mkdir($controllerDir, 0777, true);
    }
    
    $filename = $controllerDir . "/{$controllerName}.php";
    
    // Backup existing file if it exists
    if (file_exists($filename)) {
        $backupDir = $controllerDir . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $timestamp = date('Y-m-d_H-i-s');
        copy($filename, "{$backupDir}/{$controllerName}_{$timestamp}.php");
    }
    
    file_put_contents($filename, $code);
    
    return $controllerName;
}

try {
    $modelsDir = __DIR__ . '/app/models';
    
    if (!is_dir($modelsDir)) {
        throw new Exception("Models directory not found. Please run install.php first.");
    }
    
    $files = scandir($modelsDir);
    
    if (count($files) <= 2) { // . and ..
        throw new Exception("No model files found. Please run install.php first.");
    }
    
    $generatedControllers = [];
    foreach ($files as $file) {
        if (substr($file, -4) === '.php') {
            $modelClass = substr($file, 0, -4); // Remove .php
            try {
                $controllerName = generateController($modelClass);
                $generatedControllers[] = $controllerName;
            } catch (Exception $e) {
                echo "Warning: Failed to generate controller for {$modelClass}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    if (empty($generatedControllers)) {
        throw new Exception("No controllers were generated. Check if model files exist and are properly formatted.");
    }
    
    echo "Successfully generated the following controllers:\n";
    foreach ($generatedControllers as $controller) {
        echo "- {$controller}\n";
    }
    echo "\nControllers are ready to use. You can customize them by overriding the validation and data processing methods.\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
