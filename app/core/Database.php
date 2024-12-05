<?php
namespace App\Core;

// Require configuration file to import database constants
require_once __DIR__ . '/../../config.php';

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Enable error reporting
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        try {
            // Detailed connection parameters
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s", 
                DB_HOST, 
                DB_NAME, 
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            // Log connection attempt
            error_log("Attempting to connect to database:");
            error_log("DSN: " . $dsn);
            error_log("User: " . DB_USER);

            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );

            // Log successful connection
            error_log("Database connection successful");
        } catch(PDOException $e) {
            // Comprehensive error logging
            error_log("Database Connection Error Details:");
            error_log("Error Code: " . $e->getCode());
            error_log("Error Message: " . $e->getMessage());
            error_log("Connection Parameters:");
            error_log("Host: " . DB_HOST);
            error_log("Database: " . DB_NAME);
            error_log("User: " . DB_USER);
            error_log("Charset: " . DB_CHARSET);

            // Throw the exception to allow higher-level error handling
            throw new \Exception("Database connection failed: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (\Exception $e) {
                // Handle or rethrow the exception as needed
                die("Failed to create database instance: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Additional method to test connection
    public function testConnection() {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return true;
        } catch(PDOException $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
}
