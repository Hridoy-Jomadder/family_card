<?php
include "classes/connection.php";
$conn = (new Database())->connect();
$division_id = $_POST['id'] ?? 0;
$districts = $conn->query("SELECT * FROM districts WHERE division_id=$division_id ORDER BY name_en ASC");
echo '<option value="">--Select District--</option>';
while($d = $districts->fetch_assoc()){
    echo '<option value="'.$d['id'].'">'.htmlspecialchars($d['name_en']).'</option>';
}
