<?php
namespace App\Core;

class Controller {
    protected function render($view, $data = []) {
        extract($data);
        
        $viewFile = dirname(__DIR__) . "/views/{$view}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View file not found: {$view}");
        }
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit();
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
