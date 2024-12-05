<?php
namespace App\Models;

use App\Core\Model;

class UseractivityModel extends Model {
    protected static $tableName = 'useractivity';
    protected static $primaryKey = 'id';
    protected static $displayField = 'Feedback';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'ActivityId' => 
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
    protected function validate(array $data): array {
        $errors = [];
        $primaryKey = self::$primaryKey;

        // Add your custom validation logic here
        if (!is_numeric($data['id'])) {
            $errors['id'] = 'Id must be a number';
        }
        
        if (!is_numeric($data['Stars'])) {
            $errors['Stars'] = 'Stars must be a number';
        }
        if (empty($data['UserId'])) {
            $errors['UserId'] = 'UserId is required';
        }
        if (!is_numeric($data['UserId'])) {
            $errors['UserId'] = 'UserId must be a number';
        }
        if (empty($data['ActivityId'])) {
            $errors['ActivityId'] = 'ActivityId is required';
        }
        if (!is_numeric($data['ActivityId'])) {
            $errors['ActivityId'] = 'ActivityId must be a number';
        }
        

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}