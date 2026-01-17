<?php
include "../config.php";
header("Content-Type: application/json");

$card = $_GET['card'] ?? '';

$q = mysqli_query($conn, "SELECT family_name, full_name, family_members, family_card_number 
FROM users WHERE family_card_number='$card'");

if(mysqli_num_rows($q)){
  $data = mysqli_fetch_assoc($q);

  // Scan log
  mysqli_query($conn,"INSERT INTO scan_logs(family_card_number,ip)
   VALUES('$card','".$_SERVER['REMOTE_ADDR']."')");

  echo json_encode([
    "status" => "valid",
    "family_name" => $data['family_name'],
    "family_head" => $data['full_name'],
    "members" => $data['family_members'],
    "card" => $data['family_card_number']
  ]);
}else{
  echo json_encode(["status"=>"invalid"]);
}
?>
