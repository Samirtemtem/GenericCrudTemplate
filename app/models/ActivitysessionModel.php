<?php
namespace App\Models;

use App\Core\Model;

class ActivitysessionModel extends Model {
    public static $table = 'activitysession';
    public static $primaryKey = 'ID';
    public static $displayField = 'ID';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'ActivityID' => 
  array (
    'model' => '\\App\\Models\\ActivityModel',
    'table' => 'activity',
    'key' => 'id',
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
        if (!is_numeric($data['ID'])) {
            $errors['ID'] = 'ID must be a number';
        }
        if (!is_numeric($data['Weekday'])) {
            $errors['Weekday'] = 'Weekday must be a number';
        }
        
        
        if (empty($data['ActivityID'])) {
            $errors['ActivityID'] = 'ActivityID is required';
        }
        if (!is_numeric($data['ActivityID'])) {
            $errors['ActivityID'] = 'ActivityID must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}