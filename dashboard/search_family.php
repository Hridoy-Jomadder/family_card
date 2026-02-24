<?php
include "../classes/connection.php";
$conn = (new Database())->connect();

// Get filter values
$division_id = $_GET['division_id'] ?? '';
$district_id = $_GET['district_id'] ?? '';
$upazila_id  = $_GET['upazila_id'] ?? '';
$union_name  = $_GET['union_name'] ?? '';
$ward_number = $_GET['ward_number'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

// Division filter
if($division_id){
    $query .= " AND division_id=?";
    $params[] = $division_id;
    $types .= "i";
}

// District filter
if($district_id){
    $query .= " AND district_id=?";
    $params[] = $district_id;
    $types .= "i";
}

// Upazila filter
if($upazila_id){
    $query .= " AND upazila_id=?";
    $params[] = $upazila_id;
    $types .= "i";
}

// Union filter (use name)
if($union_name){
    $query .= " AND union_name=?";
    $params[] = $union_name;
    $types .= "s";
}

// Ward filter (use number)
if($ward_number){
    $query .= " AND ward_number=?";
    $params[] = $ward_number;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    echo '<table class="table table-bordered">';
    echo '<tr><th>Family Name</th><th>House No</th><th>Division</th><th>District</th><th>Upazila</th><th>Union</th><th>Ward</th></tr>';
    while($row = $result->fetch_assoc()){
        echo '<tr>
                <td>'.htmlspecialchars($row['family_name']).'</td>
                <td>'.htmlspecialchars($row['house_no']).'</td>
                <td>'.htmlspecialchars($row['division_name']).'</td>
                <td>'.htmlspecialchars($row['district_name']).'</td>
                <td>'.htmlspecialchars($row['upazila_name']).'</td>
                <td>'.htmlspecialchars($row['union_name']).'</td>
                <td>'.htmlspecialchars($row['ward_number']).'</td>
              </tr>';
    }
    echo '</table>';
}else{
    echo '<p>No families found.</p>';
}
?>