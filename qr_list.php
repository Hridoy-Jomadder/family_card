<?php
include "config.php";

// Fetch users
$sql = "SELECT id, family_name, family_card_number 
        FROM users 
        ORDER BY id ASC";
$res = mysqli_query($conn, $sql);
if(!$res){
    die("SQL Error: ".mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Family QR List</title>
<style>
body{font-family:'Times New Roman', Times, serif;background:#f2f6ff;}
.container{max-width:1100px;margin:30px auto;background:#fff;padding:20px;border-radius:12px;}
h2{text-align:center;color:#006400;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #ccc;padding:10px;text-align:center;}
th{background:#006400;color:#fff;}
img{width:80px;}
.btn{
    padding:6px 10px;
    text-decoration:none;
    border-radius:6px;
    color:#fff;
    font-size:14px;
}
.view{background:#007bff;}
.pdf{background:#dc3545;}
</style>
</head>
<body>

<div class="container">
<h2>📋 Family QR Code List</h2>

<table>
<tr>
<th>#</th>
<th>QR Code</th>
<th>Family Name</th>
<th>Card Number</th>
<th>Total Gifts</th>
<th>Action</th>
</tr>

<?php $i=1; while($row=mysqli_fetch_assoc($res)): 

$card = $row['family_card_number'];

// Count gifts
$qGift = mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM gift WHERE family_card_number='$card'"
);
$giftCount = mysqli_fetch_assoc($qGift)['total'];
?>

<tr>
<td><?= $i++ ?></td>
<td>
<img src="qrcodes/<?= $card ?>.png" alt="QR">
</td>
<td><?= htmlspecialchars($row['family_name']) ?></td>
<td><?= $card ?></td>
<td><?= $giftCount ?></td>
<td>
<a class="btn view" target="_blank"
   href="verify.php?card=<?= $card ?>">View</a>
<!-- <a class="btn pdf" href="#">PDF</a> -->
</td>
</tr>

<?php endwhile; ?>
</table>
</div>

</body>
</html>
