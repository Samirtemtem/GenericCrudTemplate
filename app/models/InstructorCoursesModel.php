<?php
namespace App\Models;

use App\Core\Model;

class InstructorCoursesModel extends Model {
    protected static $tableName = 'instructor_courses';
    protected static $primaryKey = 'instructor_num_instructor';
    protected static $displayField = 'instructor_num_instructor';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'instructor_num_instructor' => 
  array (
    'model' => '\\App\\Models\\InstructorModel',
    'table' => 'instructor',
    'key' => 'num_instructor',
  ),
  'courses_num_course' => 
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
        if (!is_numeric($data['instructor_num_instructor'])) {
            $errors['instructor_num_instructor'] = 'Instructor num instructor must be a number';
        }
        if (empty($data['courses_num_course'])) {
            $errors['courses_num_course'] = 'Courses num course is required';
        }
        if (!is_numeric($data['courses_num_course'])) {
            $errors['courses_num_course'] = 'Courses num course must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}