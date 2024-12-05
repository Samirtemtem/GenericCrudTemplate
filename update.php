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

    // Get foreign key relationships including inferred ones
    $fkMap = getForeignKeyMap($pdo, $table);

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $updates = [];
        $params = [];

        foreach ($columns as $column) {
            if ($column['Field'] != 'id') {
                if (isset($_FILES[$column['Field']]) && $_FILES[$column['Field']]['size'] > 0) {
                    // Handle file upload
                    $imageData = file_get_contents($_FILES[$column['Field']]['tmp_name']);
                    $updates[] = "`{$column['Field']}` = :{$column['Field']}";
                    $params[":{$column['Field']}"] = $imageData;
                } else {
                    $updates[] = "`{$column['Field']}` = :{$column['Field']}";
                    $params[":{$column['Field']}"] = $_POST[$column['Field']] ?? null;
                }
            }
        }

        $sql = "UPDATE `$table` SET " . implode(", ", $updates) . " WHERE id = :id";
        $params[":id"] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        header("location: view_table.php?table=" . urlencode($table));
        exit();
    }

    // Get existing record
    $sql = "SELECT * FROM `$table` WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        header("location: index.php");
        exit();
    }
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record - <?php echo ucfirst($table); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .wrapper{ width: 800px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3">
                        <h2>Update Record in <?php echo ucfirst($table); ?></h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?table=" . urlencode($table) . "&id=" . urlencode($id); ?>" 
                                  method="post"
                                  enctype="multipart/form-data">
                                <?php foreach ($columns as $column): ?>
                                    <?php if ($column['Field'] != 'id'): ?>
                                        <div class="form-group mb-3">
                                            <label class="form-label"><?php echo ucfirst($column['Field']); ?></label>
                                            <?php
                                            if (isset($fkMap[$column['Field']])) {
                                                // Create select box for foreign keys
                                                $fk = $fkMap[$column['Field']];
                                                $displayField = getTableDisplayFields($pdo, $fk['table']);
                                                $optionsStmt = $pdo->query("SELECT id, $displayField FROM {$fk['table']}");
                                                $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
                                                ?>
                                                <select name="<?php echo $column['Field']; ?>" 
                                                        class="form-select"
                                                        <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                    <option value="">Select <?php echo ucfirst($fk['table']); ?></option>
                                                    <?php foreach ($options as $option): ?>
                                                        <option value="<?php echo $option['id']; ?>"
                                                                <?php echo $record[$column['Field']] == $option['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($option[$displayField]); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php
                                            } elseif (isImageColumn($column)) {
                                                if (!empty($record[$column['Field']])) {
                                                    echo '<div class="mb-2">';
                                                    echo '<img src="data:image/jpeg;base64,' . base64_encode($record[$column['Field']]) . '" 
                                                              class="img-thumbnail" style="max-width: 200px;">';
                                                    echo '</div>';
                                                }
                                                ?>
                                                <input type="file" 
                                                       name="<?php echo $column['Field']; ?>" 
                                                       class="form-control"
                                                       accept="image/*">
                                            <?php
                                            } else {
                                                // Handle regular fields
                                                $type = "text"; // default
                                                if (strpos($column['Type'], 'int') !== false) {
                                                    $type = "number";
                                                } elseif (strpos($column['Type'], 'date') !== false) {
                                                    $type = "date";
                                                } elseif (strpos($column['Type'], 'text') !== false) {
                                                    $type = "textarea";
                                                }
                                                
                                                if ($type === "textarea"): ?>
                                                    <textarea name="<?php echo $column['Field']; ?>" 
                                                        class="form-control"
                                                        <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>><?php echo htmlspecialchars($record[$column['Field']]); ?></textarea>
                                                <?php else: ?>
                                                    <input type="<?php echo $type; ?>" 
                                                        name="<?php echo $column['Field']; ?>" 
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($record[$column['Field']]); ?>"
                                                        <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                <?php endif;
                                            } ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <input type="submit" class="btn btn-primary" value="Update Record">
                                    <a href="view_table.php?table=<?php echo urlencode($table); ?>" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
