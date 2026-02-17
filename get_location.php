<?php
include "classes/connection.php";
session_start();

// Check user login
if (!isset($_SESSION['user_id'])) {
    exit("Not logged in.");
}

$DB = new Database();
$conn = $DB->connect();

$type = $_POST['type'] ?? '';
$response = '';

// Helper function to generate options
function generateOptions($data, $selected_id) {
    $options = "<option value=''>--Select--</option>";
    foreach ($data as $row) {
        $isSelected = ($row['id'] == $selected_id) ? 'selected' : '';
        $options .= "<option value='{$row['id']}' $isSelected>{$row['name_en']}</option>";
    }
    return $options;
}

$user_id = $_SESSION['user_id'];

// Fetch current user location for preselection
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

switch($type) {
    case 'district':
        $division_id = intval($_POST['division_id'] ?? 0);
        $districts = $conn->query("SELECT id, name_en FROM districts WHERE division_id=$division_id ORDER BY name_en ASC")->fetch_all(MYSQLI_ASSOC);
        $selected_id = $user['district_id'] ?? null;
        $response = generateOptions($districts, $selected_id);
        break;

    case 'upazila':
        $district_id = intval($_POST['district_id'] ?? 0);
        $upazilas = $conn->query("SELECT id, name_en FROM upazilas WHERE district_id=$district_id ORDER BY name_en ASC")->fetch_all(MYSQLI_ASSOC);
        $selected_id = $user['upazila_id'] ?? null;
        $response = generateOptions($upazilas, $selected_id);
        break;

    case 'union':
        $upazila_id = intval($_POST['upazila_id'] ?? 0);
        $unions = $conn->query("SELECT id, name_en FROM unions WHERE upazila_id=$upazila_id ORDER BY name_en ASC")->fetch_all(MYSQLI_ASSOC);
        $selected_id = $user['union_id'] ?? null;
        $response = generateOptions($unions, $selected_id);
        break;

    case 'ward':
        $union_id = intval($_POST['union_id'] ?? 0);
        $wards = $conn->query("SELECT id, ward_number AS name_en FROM wards WHERE union_id=$union_id ORDER BY ward_number ASC")->fetch_all(MYSQLI_ASSOC);
        $selected_id = $user['ward_id'] ?? null;
        $response = generateOptions($wards, $selected_id);
        break;

    default:
        $response = "<option value=''>Invalid type</option>";
}

echo $response;
$conn->close();
