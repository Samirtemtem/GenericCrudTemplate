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
							<form action="create.php?table=<?php echo urlencode($table); ?>" 
								  method="post" 
								  enctype="multipart/form-data">
								<?php foreach ($columns as $column): ?>
									<?php if ($column['Field'] != 'id'): ?>
										<div class="form-group mb-3">
											<label class="form-label"><?php echo ucfirst($column['Field']); ?></label>
											<?php
											$isForeignKey = false;
											foreach ($foreignKeys as $fk) {
												if ($fk['COLUMN_NAME'] === $column['Field']) {
													$isForeignKey = true;
													// Foreign key handling here
													break;
												}
											}
											
											if (!$isForeignKey) {
												if (isImageColumn($column)) {
													?>
													<input type="file" 
														   name="<?php echo $column['Field']; ?>" 
														   class="form-control"
														   accept="image/*"
														   <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
												<?php
												} else {
													// Regular field handling
													$type = getInputType($column['Type']);
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
												}
											}
											?>
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