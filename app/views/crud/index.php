<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($tableName) ? ucfirst($tableName) : 'Records'; ?> Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .wrapper { width: 1200px; padding: 20px; margin: 0 auto; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.075); }
        .blob-preview { max-width: 100px; max-height: 100px; cursor: pointer; }
        .modal-blob-preview { max-width: 100%; max-height: 500px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3 d-flex justify-content-between align-items-center">
                        <h2><?php 
                            echo isset($tableName) 
                                ? ucfirst($tableName) . ' Records' 
                                : (isset($model) 
                                    ? ucfirst($model::getTableName()) . ' Records' 
                                    : 'Records'); 
                        ?></h2>
                        <div class="d-flex">
                            <form class="me-2" action="index.php" method="get">
                                <input type="hidden" name="controller" value="<?php 
                                    echo isset($model) 
                                        ? $model::getTableName() 
                                        : (isset($tableName) 
                                            ? $tableName 
                                            : ''); 
                                ?>">
                                <input type="hidden" name="action" value="index">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search..." 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                            <a href="index.php?controller=<?php 
                                echo isset($model) 
                                    ? $model::getTableName() 
                                    : (isset($tableName) 
                                        ? $tableName 
                                        : ''); 
                            ?>&action=create" class="btn btn-success">
                                <i class="bi bi-plus"></i> Add New Record
                            </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($records)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <?php 
                                                // Fallback to first record's keys if no model is provided
                                                $columns = isset($model) 
                                                    ? array_keys($model::getRelations() + $model::getTableColumns()) 
                                                    : (isset($records[0]) 
                                                        ? array_keys($records[0]) 
                                                        : []); 
                                                
                                                foreach ($columns as $field): ?>
                                                    <th>
                                                        <a href="index.php?controller=<?php 
                                                            echo isset($model) 
                                                                ? $model::getTableName() 
                                                                : (isset($tableName) 
                                                                    ? $tableName 
                                                                    : ''); 
                                                        ?>&action=index&sort=<?php echo $field; ?>&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
                                                            <?php echo ucfirst($field); ?>
                                                            <?php if (isset($_GET['sort']) && $_GET['sort'] === $field): ?>
                                                                <i class="bi bi-sort-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
                                                            <?php endif; ?>
                                                        </a>
                                                    </th>
                                                <?php endforeach; ?>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($records as $record): ?>
                                                <tr>
                                                    <?php 
                                                    // Fallback to first record's keys if no model is provided
                                                    $columns = isset($model) 
                                                        ? array_keys($model::getRelations() + $model::getTableColumns()) 
                                                        : (isset($records[0]) 
                                                            ? array_keys($records[0]) 
                                                            : []); 
                                                    
                                                    foreach ($columns as $field): ?>
                                                        <td>
                                                            <?php
                                                            $value = $record[$field] ?? null;
                                                            
                                                            if (is_resource($value)) {
                                                                $imageData = stream_get_contents($value);
                                                                echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" 
                                                                      class="blob-preview" 
                                                                      data-bs-toggle="modal" 
                                                                      data-bs-target="#imageModal" 
                                                                      data-image="data:image/jpeg;base64,' . base64_encode($imageData) . '">';
                                                            } else {
                                                                if (isset($model) && method_exists($model, 'getRelations')) {
                                                                    $relations = $model::getRelations();
                                                                    if (isset($relations[$field])) {
                                                                        $relatedModel = new $relations[$field]['model']();
                                                                        $relatedRecord = $relatedModel->findById($value);
                                                                        if ($relatedRecord) {
                                                                            $displayField = $relatedModel::getDisplayField();
                                                                            echo htmlspecialchars($relatedRecord[$displayField]);
                                                                        } else {
                                                                            echo htmlspecialchars($value);
                                                                        }
                                                                    } else {
                                                                        echo htmlspecialchars($value);
                                                                    }
                                                                } else {
                                                                    echo htmlspecialchars($value);
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="index.php?controller=<?php 
                                                                echo isset($model) 
                                                                    ? $model::getTableName() 
                                                                    : (isset($tableName) 
                                                                        ? $tableName 
                                                                        : ''); 
                                                            ?>&action=view&id=<?php 
                                                                echo isset($model) 
                                                                    ? $record[$model::getPrimaryKey()] 
                                                                    : $record['id']; 
                                                            ?>" 
                                                               class="btn btn-info btn-sm">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="index.php?controller=<?php 
                                                                echo isset($model) 
                                                                    ? $model::getTableName() 
                                                                    : (isset($tableName) 
                                                                        ? $tableName 
                                                                        : ''); 
                                                            ?>&action=update&id=<?php 
                                                                echo isset($model) 
                                                                    ? $record[$model::getPrimaryKey()] 
                                                                    : $record['id']; 
                                                            ?>" 
                                                               class="btn btn-primary btn-sm">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="index.php?controller=<?php 
                                                                echo isset($model) 
                                                                    ? $model::getTableName() 
                                                                    : (isset($tableName) 
                                                                        ? $tableName 
                                                                        : ''); 
                                                            ?>&action=delete&id=<?php 
                                                                echo isset($model) 
                                                                    ? $record[$model::getPrimaryKey()] 
                                                                    : $record['id']; 
                                                            ?>" 
                                                               class="btn btn-danger btn-sm"
                                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    
                                    <?php if (isset($pagination)): ?>
                                    <nav>
                                        <ul class="pagination justify-content-center">
                                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                                    <a class="page-link" href="index.php?controller=<?php 
                                                        echo isset($model) 
                                                            ? $model::getTableName() 
                                                            : (isset($tableName) 
                                                                ? $tableName 
                                                                : ''); 
                                                    ?>&action=index&page=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No records found. 
                                    <a href="index.php?controller=<?php 
                                        echo isset($model) 
                                            ? $model::getTableName() 
                                            : (isset($tableName) 
                                                ? $tableName 
                                                : ''); 
                                    ?>&action=create" class="alert-link">
                                        Create your first record
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Image Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" class="modal-blob-preview" src="" alt="Image Preview">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const blobPreviews = document.querySelectorAll('.blob-preview');
            const modalImage = document.getElementById('modalImage');

            blobPreviews.forEach(preview => {
                preview.addEventListener('click', function() {
                    modalImage.src = this.dataset.image;
                });
            });
        });
    </script>
</body>
</html>
