<?php
require_once "config.php";

// Get and validate table name and ID from URL
$table = isset($_GET["table"]) ? $_GET["table"] : "";
$id = isset($_GET["id"]) ? $_GET["id"] : "";

if (empty($table) || empty($id)) {
    header("location: index.php");
    exit();
}

try {
    // Get table structure
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columns = $columnsStmt->fetchAll();

    // Get foreign key relationships
    $fkStmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = '" . DB_NAME . "' AND
            TABLE_NAME = '$table' AND
            REFERENCED_TABLE_NAME IS NOT NULL"
    );
    $foreignKeys = $fkStmt->fetchAll(PDO::FETCH_ASSOC);
    $fkMap = [];
    foreach ($foreignKeys as $fk) {
        $fkMap[$fk['COLUMN_NAME']] = [
            'table' => $fk['REFERENCED_TABLE_NAME'],
            'column' => $fk['REFERENCED_COLUMN_NAME']
        ];
    }

    // Build the SELECT query with JOIN clauses for foreign keys
    $select_query = "SELECT a.* ";
    $join_clauses = "";
    foreach ($fkMap as $column => $fk) {
        $alias = "fk_" . $column;
        $select_query .= ", $alias.* as {$alias}_data";
        $join_clauses .= " LEFT JOIN {$fk['table']} $alias ON a.$column = $alias.{$fk['column']}";
    }
    $select_query .= " FROM `$table` a" . $join_clauses . " WHERE a.id = :id";

    // Get record with related data
    $stmt = $pdo->prepare($select_query);
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        throw new Exception("Record not found.");
    }
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}

// Function to determine if a column is an image type
function isImageColumn($column) {
    return strpos(strtolower($column['Type']), 'blob') !== false || 
           strpos(strtolower($column['Field']), 'image') !== false || 
           strpos(strtolower($column['Field']), 'photo') !== false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Record - <?php echo ucfirst($table); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .wrapper{ width: 800px; padding: 20px; margin: 0 auto; }
        .record-image { max-width: 300px; max-height: 300px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3">
                        <h2>View Record in <?php echo ucfirst($table); ?></h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <?php foreach ($columns as $column): ?>
                                    <tr>
                                        <th class="table-light" style="width: 30%"><?php echo ucfirst($column['Field']); ?></th>
                                        <td>
                                            <?php 
                                            if (isset($fkMap[$column['Field']])) {
                                                // Display related data for foreign keys
                                                $fk = $fkMap[$column['Field']];
                                                $alias = "fk_" . $column['Field'];
                                                $related_data = [];
                                                foreach (['name', 'title', 'label', 'description'] as $field) {
                                                    if (isset($record[$alias . '_data_' . $field])) {
                                                        $related_data[] = $record[$alias . '_data_' . $field];
                                                        break;
                                                    }
                                                }
                                                echo !empty($related_data) ? htmlspecialchars(implode(', ', $related_data)) : $record[$column['Field']];
                                            } elseif (isImageColumn($column)) {
                                                // Display image for BLOB/image columns
                                                if (!empty($record[$column['Field']])) {
                                                    $imageData = base64_encode($record[$column['Field']]);
                                                    echo '<img src="data:image/jpeg;base64,' . $imageData . '" class="record-image" alt="Record Image">';
                                                } else {
                                                    echo 'No image';
                                                }
                                            } else {
                                                echo nl2br(htmlspecialchars($record[$column['Field']]));
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            <div class="mt-3">
                                <a href="update.php?table=<?php echo urlencode($table); ?>&id=<?php echo urlencode($record['id']); ?>" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="view_table.php?table=<?php echo urlencode($table); ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
