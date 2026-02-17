<?php
include "classes/connection.php";
session_start();

if(!isset($_SESSION['user_id'])){
    exit("Unauthorized Access");
}

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];

$allowed = ['division_id','district_id','upazila_id','union_id','ward_id'];

$field = $_POST['field'] ?? '';
$value = (int)($_POST['value'] ?? 0);

if(!in_array($field,$allowed)){
    exit("Invalid Field");
}

$stmt = $conn->prepare("UPDATE users SET $field=? WHERE id=?");
$stmt->bind_param("ii",$value,$user_id);

if($stmt->execute()){
    echo "Family Information Updated Successfully";
}else{
    echo "Update Failed";
}

