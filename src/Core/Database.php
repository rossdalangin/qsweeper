<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Singleton Database Class
 * Handles the database connection using PDO.
 */
class Database {
    /** @var Database|null The single instance of the Database class. */
    private static $instance = null;

    /** @var PDO The PDO connection object. */
    private $conn;

    /**
     * Private constructor to prevent direct instantiation.
     * Establishes the database connection.
     */
    private function __construct() {
        // Get database credentials from config
        require_once __DIR__ . '/../../config.php';

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In a real app, you'd log this error and show a generic message
            die('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Gets the single instance of the Database class.
     *
     * @return Database The singleton instance.
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Gets the PDO connection object.
     *
     * @return PDO The active PDO connection.
     */
    public function getConnection() {
        return $this->conn;
    }
}
