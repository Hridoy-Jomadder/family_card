<?php
include "classes/connection.php";

$DB = new Database();
$conn = $DB->connect();

$type = $_POST['type'] ?? '';


function generateOptions($result) {
    $options = '<option value="">--Select--</option>';

    while($row = $result->fetch_assoc()){
        $options .= '<option value="'.$row['id'].'">'
                    .htmlspecialchars($row['name_en']).'</option>';
    }

    return $options;
}

if($type == 'district' && isset($_POST['division_id'])){

    $stmt = $conn->prepare("SELECT id,name_en FROM districts WHERE division_id=? ORDER BY name_en ASC");
    $stmt->bind_param("i", $_POST['division_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    echo generateOptions($result);
}

elseif($type == 'upazila' && isset($_POST['district_id'])){

    $stmt = $conn->prepare("SELECT id,name_en FROM upazilas WHERE district_id=? ORDER BY name_en ASC");
    $stmt->bind_param("i", $_POST['district_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    echo generateOptions($result);
}

elseif($type == 'union' && isset($_POST['upazila_id'])){

    $stmt = $conn->prepare("SELECT id,name_en FROM unions WHERE upazila_id=? ORDER BY name_en ASC");
    $stmt->bind_param("i", $_POST['upazila_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    echo generateOptions($result);
}

?>