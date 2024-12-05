<?php
require_once "config.php";

// Get and validate table name from URL
$table = isset($_GET["table"]) ? $_GET["table"] : "";
if (empty($table)) {
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

    // Handle Delete Operation
    if (isset($_POST["delete"]) && isset($_POST["id"])) {
        $sql = "DELETE FROM `$table` WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $_POST["id"]]);
        header("location: view_table.php?table=" . urlencode($table));
        exit();
    }

    // Fetch records with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;

    // Get total records
    $total_records_stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    $total_records = $total_records_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Build the SELECT query with JOIN clauses for foreign keys
    $select_query = "SELECT a.* ";
    $join_clauses = "";
    foreach ($fkMap as $column => $fk) {
        $alias = "fk_" . $column;
        $select_query .= ", $alias.* as {$alias}_data";
        $join_clauses .= " LEFT JOIN {$fk['table']} $alias ON a.$column = $alias.{$fk['column']}";
    }
    $select_query .= " FROM `$table` a" . $join_clauses . " LIMIT :offset, :records_per_page";

    // Fetch records for current page
    $stmt = $pdo->prepare($select_query);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

} catch(PDOException $e) {
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
    <title>View Table - <?php echo ucfirst($table); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .wrapper{ width: 1200px; padding: 20px; margin: 0 auto; }
        .table-img { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3 d-flex justify-content-between align-items-center">
                        <h2><?php echo ucfirst($table); ?></h2>
                        <a href="create.php?table=<?php echo urlencode($table); ?>" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Add New Record
                        </a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($records)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <?php foreach ($columns as $column): ?>
                                                    <th><?php echo ucfirst($column['Field']); ?></th>
                                                <?php endforeach; ?>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($records as $record): ?>
                                                <tr>
                                                    <?php foreach ($columns as $column): ?>
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
                                                                    echo '<img src="data:image/jpeg;base64,' . $imageData . '" class="table-img" alt="Image">';
                                                                } else {
                                                                    echo 'No image';
                                                                }
                                                            } else {
                                                                echo htmlspecialchars($record[$column['Field']]);
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="read.php?table=<?php echo urlencode($table); ?>&id=<?php echo $record['id']; ?>" 
                                                               class="btn btn-info btn-sm" title="View Record">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="update.php?table=<?php echo urlencode($table); ?>&id=<?php echo $record['id']; ?>" 
                                                               class="btn btn-primary btn-sm" title="Update Record">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?table=" . urlencode($table); ?>" 
                                                                  method="post" style="display: inline;">
                                                                <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                                                <button type="submit" name="delete" class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Are you sure you want to delete this record?')"
                                                                        title="Delete Record">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-3">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $page-1; ?>">Previous</a>
                                        </li>
                                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $page+1; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="lead"><em>No records found.</em></p>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary mt-3">Back to Tables List</a>
                        </div>
                    </div>
                </div>
            </div>        
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
