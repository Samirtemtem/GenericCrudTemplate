<?php
namespace App\Models;

use App\Core\Model;

class PisteModel extends Model {
    protected static $tableName = 'piste';
    protected static $primaryKey = 'num_piste';
    protected static $displayField = 'name_piste';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
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
        if (!is_numeric($data['num_piste'])) {
            $errors['num_piste'] = 'Num piste must be a number';
        }
        
        
        if (empty($data['length'])) {
            $errors['length'] = 'Length is required';
        }
        if (!is_numeric($data['length'])) {
            $errors['length'] = 'Length must be a number';
        }
        
        if (empty($data['slope'])) {
            $errors['slope'] = 'Slope is required';
        }
        if (!is_numeric($data['slope'])) {
            $errors['slope'] = 'Slope must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}