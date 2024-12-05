<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update <?php echo ucfirst($tableName); ?> Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .wrapper { width: 800px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3">
                        <h2>Update <?php echo ucfirst($tableName); ?> Record</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="index.php?controller=<?php echo $model::getTableName(); ?>&action=update&id=<?php echo $record[$model::getPrimaryKey()]; ?>" 
                                  method="post" 
                                  enctype="multipart/form-data">
                                <?php
                                $columns = $model->db->fetchAll("SHOW COLUMNS FROM " . $model::getTableName());
                                foreach ($columns as $column):
                                    if ($column['Field'] !== $model::getPrimaryKey()):
                                ?>
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo ucfirst($column['Field']); ?></label>
                                        <?php
                                        $relations = $model::getRelations();
                                        if (isset($relations[$column['Field']])) {
                                            // Create select box for foreign keys
                                            $relatedModel = new $relations[$column['Field']]['model']();
                                            $options = $relatedModel->findAll();
                                            $displayField = $relatedModel::getDisplayField();
                                            ?>
                                            <select name="<?php echo $column['Field']; ?>" 
                                                    class="form-select"
                                                    <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                <option value="">Select <?php echo ucfirst($relatedModel::getTableName()); ?></option>
                                                <?php foreach ($options as $option): ?>
                                                    <option value="<?php echo $option[$relatedModel::getPrimaryKey()]; ?>"
                                                            <?php echo $record[$column['Field']] == $option[$relatedModel::getPrimaryKey()] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($option[$displayField]); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
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
                                            } elseif (strpos(strtolower($column['Type']), 'blob') !== false || 
                                                     strpos(strtolower($column['Field']), 'image') !== false || 
                                                     strpos(strtolower($column['Field']), 'photo') !== false) {
                                                $type = "file";
                                            }
                                            
                                            if ($type === "textarea"): ?>
                                                <textarea name="<?php echo $column['Field']; ?>" 
                                                    class="form-control"
                                                    <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>><?php echo htmlspecialchars($record[$column['Field']]); ?></textarea>
                                            <?php elseif ($type === "file"): 
                                                if (!empty($record[$column['Field']])): ?>
                                                    <div class="mb-2">
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($record[$column['Field']]); ?>" 
                                                             class="img-thumbnail" 
                                                             style="max-width: 200px;">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" 
                                                       name="<?php echo $column['Field']; ?>" 
                                                       class="form-control"
                                                       accept="image/*">
                                            <?php else: ?>
                                                <input type="<?php echo $type; ?>" 
                                                       name="<?php echo $column['Field']; ?>" 
                                                       class="form-control"
                                                       value="<?php echo htmlspecialchars($record[$column['Field']]); ?>"
                                                       <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                            <?php endif;
                                        }
                                        ?>
                                    </div>
                                <?php endif; endforeach; ?>
                                <div class="mt-3">
                                    <input type="submit" class="btn btn-primary" value="Update Record">
                                    <a href="index.php?controller=<?php echo $model::getTableName(); ?>" class="btn btn-secondary">Cancel</a>
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
