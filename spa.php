<?php
require_once 'config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';

// Database connection
$db = \App\Core\Database::getInstance();

// Page routing
$page = $_GET['page'] ?? 'dashboard';

function renderDashboard() {
    global $db;
    
    // Fetch counts for different entities
    $entities = ['activity', 'course', 'instructor', 'activitysession'];
    $stats = [];
    
    foreach ($entities as $entity) {
        $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM $entity");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats[$entity] = $result['count'];
    }
    
    ob_start();
    ?>
    <div class="container-fluid">
        <h1 class="mt-4">Dashboard</h1>
        <div class="row">
            <?php foreach ($stats as $entity => $count): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= ucfirst($entity) ?></h5>
                            <p class="card-text display-4"><?= $count ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderEntityList($entityName) {
    global $db;
    
    // Map entity names to table names
    $tableMap = [
        'activities' => 'activity',
        'courses' => 'course',
        'instructors' => 'instructor',
        'activity-sessions' => 'activitysession'
    ];
    
    $tableName = $tableMap[$entityName] ?? $entityName;
    
    // Fetch all records
    $stmt = $db->getConnection()->prepare("SELECT * FROM $tableName");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start();
    ?>
    <div class="container-fluid">
        <h1 class="mt-4"><?= ucwords(str_replace('-', ' ', $entityName)) ?></h1>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <?php 
                                // Display column headers if records exist
                                if (!empty($records)) {
                                    foreach (array_keys($records[0]) as $column) {
                                        echo "<th>" . ucwords(str_replace('_', ' ', $column)) . "</th>";
                                    }
                                }
                                ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <?php 
                                    foreach ($record as $value) {
                                        echo "<td>" . htmlspecialchars($value) . "</td>";
                                    }
                                    ?>
                                    <td>
                                        <button class="btn btn-sm btn-primary me-2">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Route content based on page parameter
switch ($page) {
    case 'dashboard':
        echo renderDashboard();
        break;
    
    case 'activities':
    case 'courses':
    case 'instructors':
    case 'activity-sessions':
        echo renderEntityList($page);
        break;
    
    default:
        echo "<div class='alert alert-danger'>Page not found</div>";
        break;
}
?>
