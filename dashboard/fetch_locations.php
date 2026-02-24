<?php
include "../classes/connection.php";
$conn = (new Database())->connect();

$type = $_POST['type'] ?? '';
$parent_id = $_POST['parent_id'] ?? '';

if(!$type) exit;

switch($type){
    case 'district':
        $stmt = $conn->prepare("SELECT id,name_en FROM districts WHERE division_id=? ORDER BY name_en ASC");
        $stmt->bind_param("i",$parent_id);
        break;
    case 'upazila':
        $stmt = $conn->prepare("SELECT id,name_en FROM upazilas WHERE district_id=? ORDER BY name_en ASC");
        $stmt->bind_param("i",$parent_id);
        break;
    case 'union':
        $stmt = $conn->prepare("SELECT name_en FROM unions WHERE upazila_id=? ORDER BY name_en ASC");
        $stmt->bind_param("i",$parent_id);
        break;
    case 'ward':
        $stmt = $conn->prepare("SELECT ward_number FROM wards WHERE union_name=? ORDER BY ward_number ASC");
        $stmt->bind_param("s",$parent_id);
        break;
    default:
        exit;
}

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