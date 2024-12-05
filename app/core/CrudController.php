<?php
namespace App\Core;

abstract class CrudController extends Controller {
    protected $model;
    protected $modelClass;

    public function __construct() {
        if (!$this->modelClass) {
            throw new \Exception("Model class not specified in controller");
        }
        $this->model = new $this->modelClass();
    }

    public function index() {
        $records = $this->model->findAll();
        $this->render('crud/index', [
            'records' => $records,
            'model' => $this->model,
            'tableName' => $this->model::getTableName()
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->processFormData($_POST, $_FILES);
            $this->model->create($data);
            $this->redirect('index.php?controller=' . $this->getControllerName());
        }

        $this->render('crud/create', [
            'model' => $this->model,
            'tableName' => $this->model::getTableName()
        ]);
    }

    public function update($id) {
        $record = $this->model->findById($id);
        if (!$record) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->processFormData($_POST, $_FILES);
            $this->model->update($id, $data);
            $this->redirect('index.php?controller=' . $this->getControllerName());
        }

        $this->render('crud/update', [
            'record' => $record,
            'model' => $this->model,
            'tableName' => $this->model::getTableName()
        ]);
    }

    public function delete($id) {
        $this->model->delete($id);
        $this->redirect('index.php?controller=' . $this->getControllerName());
    }

    protected function processFormData($post, $files) {
        $data = [];
        foreach ($post as $key => $value) {
            if ($key !== 'id') {
                $data[$key] = $value;
            }
        }

        foreach ($files as $key => $file) {
            if ($file['size'] > 0) {
                $data[$key] = file_get_contents($file['tmp_name']);
            }
        }

        return $data;
    }

    protected function getControllerName() {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $name = end($parts);
        return str_replace('Controller', '', $name);
    }
}
