<?php
namespace Controllers;

use Core\Controller;

class RecordController extends Controller {
	public function __construct($model) {
		parent::__construct($model);
		$this->viewPath = __DIR__ . '/../views/';
	}

	public function create() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$data = $_POST;
			
			// Handle file uploads
			foreach ($_FILES as $field => $file) {
				if ($file['size'] > 0) {
					$data[$field] = file_get_contents($file['tmp_name']);
				}
			}

			if ($this->model->create($data)) {
				$this->redirect("view_table.php?table=" . urlencode($this->model->getTableName()));
			}
		}

		$this->render('create', [
			'columns' => $this->model->getColumns(),
			'foreignKeys' => $this->model->getForeignKeys(),
			'table' => $this->model->getTableName()
		]);
	}
}