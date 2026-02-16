<?php
include "config.php";  // ensure config.php is in the same folder

$card = $_GET['card'] ?? '';
if(!$card){
    die("<h2 style='color:red;text-align:center'>Card number missing!</h2>");
}

// Show the received card (debug)
echo "<div class='card'><p style='text-align:center; color:blue;'>Card received: <b>$card</b></p></div>";

// Fetch family info
$sql_family = "SELECT family_name, full_name AS family_head, family_members, family_card_number
               FROM users
               WHERE family_card_number='$card' LIMIT 1";

$res_family = mysqli_query($conn,$sql_family);
if(!$res_family){
    die("SQL Error: ".mysqli_error($conn));
}

if(mysqli_num_rows($res_family)==0){
    die("<h2 style='color:red;text-align:center'>❌ Invalid Family Card</h2>");
}

$family = mysqli_fetch_assoc($res_family);

// Fetch gifts
$sql_gifts = "SELECT full_name,gift_name,agricultural_product,product_name,vehicle,value,description,gift_image,issued_date
              FROM gift
              WHERE family_card_number='$card'";

$res_gifts = mysqli_query($conn,$sql_gifts);

$gifts = [];
if($res_gifts){
    while($row = mysqli_fetch_assoc($res_gifts)){
        $gifts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Family Card Details</title>
<style>
body{font-family:'Times New Roman', Times, serif;background:#eef;margin:0;padding:0;}
.container{max-width:960px;margin:20px auto;padding:15px;}
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 0 15px #aaa;}
h2{text-align:center;color:#006400;}
.family-info p b{display:inline-block;width:150px;}
.gift-table-container{overflow-x:auto;}
table{width:100%;border-collapse:collapse;min-width:800px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#006400;color:white;}
img{max-width:80px;height:auto;border-radius:5px;}
@media (max-width:600px){.family-info p b{width:100px;}}
</style>
</head>
<body>

<div class="container">
<div class="card">
<h2>✔ Family Verified</h2>

<div class="family-info">
<p><b>Family Name:</b> <?= htmlspecialchars($family['family_name']) ?></p>
<p><b>Family Head:</b> <?= htmlspecialchars($family['family_head'] ?? '-') ?></p>
<p><b>Total Members:</b> <?= htmlspecialchars($family['family_members'] ?? '-') ?></p>
<p><b>Card Number:</b> <?= htmlspecialchars($family['family_card_number']) ?></p>
</div>

<h3>Gifts</h3>
<div class="gift-table-container">
<table>
<tr>
<th>Full Name</th>
<th>Gift Name</th>
<th>Agricultural Product</th>
<th>Product Name</th>
<th>Vehicle</th>
<th>Value</th>
<th>Description</th>
<th>Gift Image</th>
<th>Issued Date</th>
</tr>

<?php if(!empty($gifts)): ?>
<?php foreach($gifts as $gift): ?>
<tr>
<td><?= htmlspecialchars($gift['full_name']) ?></td>
<td><?= htmlspecialchars($gift['gift_name']) ?></td>
<td><?= htmlspecialchars($gift['agricultural_product']) ?></td>
<td><?= htmlspecialchars($gift['product_name']) ?></td>
<td><?= htmlspecialchars($gift['vehicle']) ?></td>
<td><?= htmlspecialchars($gift['value']) ?></td>
<td><?= htmlspecialchars($gift['description']) ?></td>
<td>
<?php if($gift['gift_image']): ?>
<img src="<?= '../'.$gift['gift_image'] ?>" alt="Gift Image">
<?php else: echo '-'; endif; ?>
</td>
<td><?= htmlspecialchars($gift['issued_date'] ?? '-') ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="9" style="text-align:center;">No gifts found</td></tr>
<?php endif; ?>

</table>
</div>
</div>
</div>

</body>
</html>
