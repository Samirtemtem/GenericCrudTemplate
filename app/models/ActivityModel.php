<?php
namespace App\Models;

use App\Core\Model;

class ActivityModel extends Model {
    public static $tableName = 'activity';
    public static $primaryKey = 'id';
    public static $displayField = 'Title';

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
        if (!is_numeric($data['id'])) {
            $errors['id'] = 'Id must be a number';
        }
        if (empty($data['Date'])) {
            $errors['Date'] = 'Date is required';
        }
        if (empty($data['TypeActivity'])) {
            $errors['TypeActivity'] = 'TypeActivity is required';
        }
        if (empty($data['Title'])) {
            $errors['Title'] = 'Title is required';
        }
        if (empty($data['Description'])) {
            $errors['Description'] = 'Description is required';
        }
        if (empty($data['price'])) {
            $errors['price'] = 'Price is required';
        }
        if (!is_numeric($data['price'])) {
            $errors['price'] = 'Price must be a number';
        }
        if (empty($data['ImageData'])) {
            $errors['ImageData'] = 'ImageData is required';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return $data;
    }
}