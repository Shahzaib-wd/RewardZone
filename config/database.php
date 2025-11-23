<?php
/**
 * Database Configuration File
 * Contains database connection settings and PDO instance
 * 
 * @package RewardZone
 * @version 1.0
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "rewardzone_db";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Get database connection
     * @return PDO|null Database connection object or null on failure
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return null;
        }

        return $this->conn;
    }
}
?>
