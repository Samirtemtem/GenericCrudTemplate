<?php
namespace Core;

class Controller {
	protected $model;
	protected $viewPath;

	public function __construct($model = null) {
		$this->model = $model;
	}

	protected function render($view, $data = []) {
		extract($data);
		require $this->viewPath . $view . '.php';
	}

	protected function redirect($url) {
		header("Location: $url");
		exit();
	}