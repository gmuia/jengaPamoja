<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'u144866785_jengaPamojaKba';
    // private $username = 'root';
    private $username = 'root1';
    // private $username = 'u144866785_jengaKBA';
    private $password = 'Root_Admin@1000%';
    // private $password = 'OmohJengaKBA2024@';
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }
}

?>




