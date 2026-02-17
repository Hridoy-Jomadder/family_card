<?php
include "classes/connection.php";
session_start();

$conn = (new Database())->connect();

$where = [];
$params = [];
$types = '';

$fields = ['division_id','district_id','upazila_id','union_id','ward_id'];

foreach($fields as $field){
    if(isset($_GET[$field]) && $_GET[$field] !== ''){
        $where[] = "$field = ?";
        $params[] = (int)$_GET[$field];
        $types .= "i";
    }
}

$query = "SELECT id, family_name, full_name FROM users";

if(!empty($where)){
    $query .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($query);

if(!$stmt){
    die("SQL Error: " . $conn->error);
}

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "<div class='alert alert-warning'>No Family Found</div>";
    exit;
}

echo "<table class='table table-bordered table-striped'>";
echo "<tr><th>ID</th><th>Family Name</th><th>Family Head</th></tr>";

while($row = $result->fetch_assoc()){
    echo "<tr>
            <td>{$row['id']}</td>
            <td>".htmlspecialchars($row['family_name'])."</td>
            <td>".htmlspecialchars($row['full_name'])."</td>
          </tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?>
