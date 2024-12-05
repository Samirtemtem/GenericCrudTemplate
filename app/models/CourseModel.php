<?php
namespace App\Models;

use App\Core\Model;

class CourseModel extends Model {
    public static $tableName = 'course';
    public static $primaryKey = 'num_course';
    public static $displayField = 'num_course';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'instructor_id' => 
  array (
    'model' => '\\App\\Models\\InstructorModel',
    'table' => 'instructor',
    'key' => 'num_instructor',
  ),
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
        if (!is_numeric($data['num_course'])) {
            $errors['num_course'] = 'Num course must be a number';
        }
        if (empty($data['level'])) {
            $errors['level'] = 'Level is required';
        }
        if (!is_numeric($data['level'])) {
            $errors['level'] = 'Level must be a number';
        }
        
        
        if (empty($data['time_slot'])) {
            $errors['time_slot'] = 'Time slot is required';
        }
        if (!is_numeric($data['time_slot'])) {
            $errors['time_slot'] = 'Time slot must be a number';
        }
        
        if (!is_numeric($data['instructor_id'])) {
            $errors['instructor_id'] = 'Instructor id must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}