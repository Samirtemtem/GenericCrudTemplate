<?php
require_once "config.php";
require_once "core/Model.php";
require_once "core/Controller.php";
require_once "controllers/RecordController.php";

// Get and validate table name from URL
$table = isset($_GET["table"]) ? $_GET["table"] : "";

if (empty($table)) {
    header("location: index.php");
    exit();
}

try {
    // Create dynamic model instance
    $modelClass = "\\Models\\" . ucfirst($table) . "Model";
    if (!class_exists($modelClass)) {
        // Fallback to generic model if specific model doesn't exist
        $modelClass = "\\Core\\Model";
    }
    
    $model = new $modelClass($pdo);
    $controller = new \Controllers\RecordController($model);
    $controller->create();
    
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}
                    $imageData = file_get_contents($_FILES[$column['Field']]['tmp_name']);
                    $fields[] = "`{$column['Field']}`";
                    $values[] = ":{$column['Field']}";
                    $params[":{$column['Field']}"] = $imageData;
                } else {
                    $fields[] = "`{$column['Field']}`";
                    $values[] = ":{$column['Field']}";
                    $params[":{$column['Field']}"] = $_POST[$column['Field']] ?? null;
                }
            }
        }

        $sql = "INSERT INTO `$table` (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        header("location: view_table.php?table=" . urlencode($table));
        exit();
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
    <title>Create Record - <?php echo ucfirst($table); ?></title>
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
                        <h2>Create Record in <?php echo ucfirst($table); ?></h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?table=" . urlencode($table); ?>" 
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
                                                        <option value="<?php echo $option['id']; ?>">
                                                            <?php echo htmlspecialchars($option[$displayField]); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php
                                            } elseif (isImageColumn($column)) {
                                                ?>
                                                <input type="file" 
                                                       name="<?php echo $column['Field']; ?>" 
                                                       class="form-control"
                                                       accept="image/*"
                                                       <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
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
                                                        <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>></textarea>
                                                <?php else: ?>
                                                    <input type="<?php echo $type; ?>" 
                                                        name="<?php echo $column['Field']; ?>" 
                                                        class="form-control"
                                                        <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                <?php endif;
                                            } ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <input type="submit" class="btn btn-primary" value="Create Record">
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
