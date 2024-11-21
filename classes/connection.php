<?php
class Database {
    private $host = "localhost";    // Your database host
    private $user = "root";         // Your database username
    private $password = "";         // Your database password
    private $dbname = "family_data"; // Your database name

    public function connect() {
        $conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>
