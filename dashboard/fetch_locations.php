<?php
include "classes/connection.php";
$conn = (new Database())->connect();

$type = $_POST['type'] ?? '';
$parent_id = (int)($_POST['parent_id'] ?? 0);

if(!$type || !$parent_id){
    exit;
}

switch($type){

    case 'district':
        $stmt = $conn->prepare("SELECT id, name_en FROM districts WHERE division_id=? ORDER BY name_en ASC");
        break;

    case 'upazila':
        $stmt = $conn->prepare("SELECT id, name_en FROM upazilas WHERE district_id=? ORDER BY name_en ASC");
        break;

    case 'union':
        $stmt = $conn->prepare("SELECT id, name_en FROM unions WHERE upazila_id=? ORDER BY name_en ASC");
        break;

    case 'ward':
        $stmt = $conn->prepare("SELECT id, ward_number FROM wards WHERE union_id=? ORDER BY ward_number ASC");
        break;

    default:
        exit;
}

$stmt->bind_param("i",$parent_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
