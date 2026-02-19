<?php
include "classes/connection.php";
session_start();
error_log("Type: $type, ParentId: " . ($_POST['division_id'] ?? $_POST['district_id'] ?? $_POST['upazila_id']));

$DB = new Database();
$conn = $DB->connect();

$type = $_POST['type'] ?? '';

function generateOptions($result) {
    $options = "<option value=''>--Select--</option>";
    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $options .= "<option value='{$row['id']}'>".htmlspecialchars($row['name_en'])."</option>";
        }
    }
    return $options;
}

switch($type){

    case 'district':
        $division_id = intval($_POST['division_id'] ?? 0);
        $result = $conn->query("SELECT id, name_en FROM districts WHERE division_id=$division_id ORDER BY name_en ASC");
        echo generateOptions($result);
        break;

    case 'upazila':
        $district_id = intval($_POST['district_id'] ?? 0);
        $result = $conn->query("SELECT id, name_en FROM upazilas WHERE district_id=$district_id ORDER BY name_en ASC");
        echo generateOptions($result);
        break;

    case 'union':
        $upazila_id = intval($_POST['upazila_id'] ?? 0);
        $result = $conn->query("SELECT id, name_en FROM unions WHERE upazila_id=$upazila_id ORDER BY name_en ASC");
        echo generateOptions($result);
        break;

    default:
        echo "<option value=''>Invalid type</option>";
}

$conn->close();
?>
