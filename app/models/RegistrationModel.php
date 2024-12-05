<?php
namespace App\Models;

use App\Core\Model;

class RegistrationModel extends Model {
    protected static $tableName = 'registration';
    protected static $primaryKey = 'num_registraion';
    protected static $displayField = 'num_registraion';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'skieur_idskier' => 
  array (
    'model' => '\\App\\Models\\SkieurModel',
    'table' => 'skieur',
    'key' => 'idskier',
  ),
  'course_num_course' => 
  array (
    'model' => '\\App\\Models\\CourseModel',
    'table' => 'course',
    'key' => 'num_course',
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
        if (!is_numeric($data['num_registraion'])) {
            $errors['num_registraion'] = 'Num registraion must be a number';
        }
        if (empty($data['num_week'])) {
            $errors['num_week'] = 'Num week is required';
        }
        if (!is_numeric($data['num_week'])) {
            $errors['num_week'] = 'Num week must be a number';
        }
        if (!is_numeric($data['course_num_course'])) {
            $errors['course_num_course'] = 'Course num course must be a number';
        }
        if (!is_numeric($data['skieur_idskier'])) {
            $errors['skieur_idskier'] = 'Skieur idskier must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}