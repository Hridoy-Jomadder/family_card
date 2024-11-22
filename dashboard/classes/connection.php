<?php
class Database {
    private $servername = "127.0.0.1"; // Change to your database host if needed
    private $username = "root";       // Replace with your DB username
    private $password = "";           // Replace with your DB password
    private $dbname = "family_data";  // Replace with your database name
    public $conn;

    public function connect() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}
?>
