<?php
namespace App\Core;

use PDO;

abstract class Model {
    public $db;
    public static $table;
    public static $tableName;
    public static $primaryKey = 'id';
    public static $relations = [];
    public static $displayField = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getTableName() {
        // Support both $table and $tableName
        return static::$table ?? static::$tableName ?? '';
    }

    public static function getPrimaryKey() {
        return static::$primaryKey;
    }

    public static function getRelations() {
        return static::$relations;
    }

    public static function getDisplayField() {
        return static::$displayField;
    }

    public static function getTableColumns() {
        // Fetch table columns dynamically from the database
        $db = Database::getInstance();
        $tableName = static::getTableName();
        
        try {
            $stmt = $db->getConnection()->prepare("SHOW COLUMNS FROM `$tableName`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return array_combine($columns, array_fill(0, count($columns), null));
        } catch (\PDOException $e) {
            // Log the error and return an empty array
            error_log("Error fetching table columns for $tableName: " . $e->getMessage());
            return [];
        }
    }

    public function findAll() {
        return $this->db->fetchAll("SELECT * FROM " . static::getTableName());
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT * FROM " . static::getTableName() . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public function create($data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO " . static::getTableName() . 
               " (" . implode(", ", $fields) . ") VALUES (" . 
               implode(", ", $values) . ")";
        
        return $this->db->query($sql, array_values($data));
    }

    public function update($id, $data) {
        $fields = array_keys($data);
        $set = array_map(function($field) {
            return "$field = ?";
        }, $fields);
        
        $sql = "UPDATE " . static::getTableName() . 
               " SET " . implode(", ", $set) . 
               " WHERE " . static::$primaryKey . " = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        return $this->db->query($sql, $values);
    }

    public function delete($id) {
        return $this->db->query(
            "DELETE FROM " . static::getTableName() . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public function getRelatedData($relationName) {
        if (!isset(static::$relations[$relationName])) {
            throw new \Exception("Relation '$relationName' not found");
        }

        $relation = static::$relations[$relationName];
        $model = new $relation['model']();
        return $model->findAll();
    }
}
