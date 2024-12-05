<?php
namespace App\Models;

use App\Core\Model;

class SubscriptionModel extends Model {
    protected static $tableName = 'subscription';
    protected static $primaryKey = 'num_sub';
    protected static $displayField = 'num_sub';

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
        if (!is_numeric($data['num_sub'])) {
            $errors['num_sub'] = 'Num sub must be a number';
        }
        
        
        
        

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}