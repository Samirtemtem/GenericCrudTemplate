<?php
namespace App\Models;

use App\Core\Model;

class SkieurPistesModel extends Model {
    protected static $tableName = 'skieur_pistes';
    protected static $primaryKey = 'skieurs_idskier';
    protected static $displayField = 'skieurs_idskier';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'skieurs_idskier' => 
  array (
    'model' => '\\App\\Models\\SkieurModel',
    'table' => 'skieur',
    'key' => 'idskier',
  ),
  'pistes_num_piste' => 
  array (
    'model' => '\\App\\Models\\PisteModel',
    'table' => 'piste',
    'key' => 'num_piste',
  ),
);
    }

    /**
     * Validation rules for this model
     * Override this method to add custom validation
     * @param array $data Data to validate
     * @return array Validated and potentially modified data
     */
    protected function validate(array $data): array {
        $errors = [];
        $primaryKey = self::$primaryKey;

        // Add your custom validation logic here
        if (!is_numeric($data['skieurs_idskier'])) {
            $errors['skieurs_idskier'] = 'Skieurs idskier must be a number';
        }
        if (empty($data['pistes_num_piste'])) {
            $errors['pistes_num_piste'] = 'Pistes num piste is required';
        }
        if (!is_numeric($data['pistes_num_piste'])) {
            $errors['pistes_num_piste'] = 'Pistes num piste must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}