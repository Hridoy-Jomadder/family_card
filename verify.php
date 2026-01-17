<?php
<?php
$card = $_GET['card'];
$json = file_get_contents("https://yourdomain.com/api/verify.php?card=$card");
$data = json_decode($json,true);
?>

<!DOCTYPE html>
<html>
<head>
<title>Family Card Profile</title>
<style>
body{font-family:Arial;background:#eef}
.card{width:350px;margin:40px auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 0 10px #aaa;}
h3{color:#006400}
</style>
</head>

<body>

<div class="card">
<?php
if($data['status']=="valid"){
 echo "<h3>✔ Family Verified</h3>";
 echo "<b>Family Name:</b> ".$data['family_name']."<br>";
 echo "<b>Family Head:</b> ".$data['family_head']."<br>";
 echo "<b>Total Members:</b> ".$data['members']."<br>";
 echo "<b>Card Number:</b> ".$data['card']."<br>";
}else{
 echo "<h3 style='color:red'>❌ Invalid Family Card</h3>";
}
?>
</div>

</body>
</html>
