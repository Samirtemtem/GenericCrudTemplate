<?php
namespace App\Controllers;

use App\Core\CrudController;
use App\Models\SkieurModel;

/**
 * Generated controller class for Skieur
 */
class SkieurController extends CrudController {
    protected $modelClass = SkieurModel::class;
    
    /**
     * Override this method to customize the validation rules
     */
    protected function validateData(array $data, bool $isUpdate = false): array {
        // Add custom validation rules here
        return $data;
    }
    
    /**
     * Override this method to customize how data is processed before saving
     */
    protected function beforeSave(array $data, bool $isUpdate = false): array {
        // Add custom data processing here
        return $data;
    }
    
    /**
     * Override this method to perform actions after successful save
     */
    protected function afterSave($id, array $data, bool $isUpdate = false): void {
        // Add post-save actions here
    }
}