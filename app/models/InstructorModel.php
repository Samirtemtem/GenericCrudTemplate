<?php
namespace App\Models;

use App\Core\Model;

class InstructorModel extends Model {
    public static $tableName = 'instructor';
    public static $primaryKey = 'num_instructor';
    public static $displayField = 'name';

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
    public function validate(array $data): array {
        $errors = [];
        $primaryKey = self::$primaryKey;

        // Add your custom validation logic here
        if (!is_numeric($data['num_instructor'])) {
            $errors['num_instructor'] = 'Num instructor must be a number';
        }
        
        

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}