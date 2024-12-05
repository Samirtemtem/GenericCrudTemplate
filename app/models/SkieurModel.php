<?php
namespace App\Models;

use App\Core\Model;

class SkieurModel extends Model {
    protected static $tableName = 'skieur';
    protected static $primaryKey = 'idskier';
    protected static $displayField = 'name';

    /**
     * Define foreign key relationships
     * @return array
     */
    public static function getRelations(): array {
        return array (
  'subscription_num_sub' => 
  array (
    'model' => '\\App\\Models\\SubscriptionModel',
    'table' => 'subscription',
    'key' => 'num_sub',
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
        if (!is_numeric($data['idskier'])) {
            $errors['idskier'] = 'Idskier must be a number';
        }
        
        
        
        if (!is_numeric($data['subscription_num_sub'])) {
            $errors['subscription_num_sub'] = 'Subscription num sub must be a number';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}