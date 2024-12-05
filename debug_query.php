<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/models/ActivitysessionModel.php';

use App\Core\Database;
use App\Models\ActivitysessionModel;

try {
    // Create a new database instance
    $db = Database::getInstance();
    
    // Create a new model instance
    $model = new ActivitysessionModel();
    
    // Attempt to fetch all records
    echo "Attempting to fetch all records:\n";
    $records = $model->findAll();
    
    echo "Records found: " . count($records) . "\n";
    print_r($records);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
