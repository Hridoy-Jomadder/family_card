<?php
include "config.php";

$card = $_GET['card'] ?? '';

if(!$card){
    die("<h2 style='color:red;text-align:center'>Card number missing!</h2>");
}

/* Card format validation (Only numbers 5-15 digit) */
if(!preg_match('/^[0-9]{5,15}$/', $card)){
    die("<h2 style='color:red;text-align:center'>Invalid Card Format</h2>");
}

/* =============================
   FETCH FAMILY INFO (SECURE)
============================= */
$stmt = $conn->prepare("SELECT family_name,
                               full_name AS family_head,
                               family_members,
                               family_card_number,
                               family_image
                        FROM users
                        WHERE family_card_number = ?
                        LIMIT 1");

$stmt->bind_param("s", $card);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("<h2 style='color:red;text-align:center'>❌ Invalid Family Card</h2>");
}

$family = $result->fetch_assoc();

/* =============================
   FETCH GIFTS (SECURE)
============================= */
$stmt2 = $conn->prepare("SELECT full_name,
                                gift_name,
                                agricultural_product,
                                product_name,
                                vehicle,
                                gift_image,
                                created_at
                         FROM gift
                         WHERE family_card_number = ?");

$stmt2->bind_param("s", $card);
$stmt2->execute();
$res_gifts = $stmt2->get_result();

$gifts = [];
while($row = $res_gifts->fetch_assoc()){
    $gifts[] = $row;
}
echo "<div class='card' style='text-align:center; color:blue;'>Card received: $card</div>";
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
    <div style="text-align:center;margin-bottom:15px;">
        
        <?php
        
$profilePath = $family['family_image']; // already full path in DB
if(!empty($family['family_image']) && file_exists($profilePath)){
    $imgSrc = $profilePath;
}else{
    $imgSrc = "uploads/default-image.jpg";
}
?>
<div style="text-align:center;margin-bottom:15px;">
    <img src="<?= htmlspecialchars($imgSrc) ?>" 
         style="width:120px;height:120px;
                border-radius:50%;
                border:3px solid #006400;
                object-fit:cover;">
</div>
</div>
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
<!-- <th>Value</th>
<th>Description</th> -->
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
<!-- <td><?= htmlspecialchars($gift['value']) ?></td>
<td><?= htmlspecialchars($gift['description']) ?></td> -->
<td>
<?php 
$giftPath = $gift['gift_image']; // DB থেকে full path
if(!empty($gift['gift_image']) && file_exists($giftPath)){
    echo '<img src="'.htmlspecialchars($giftPath).'" width="80">';
}else{
    echo '-';
}
?>
</td>

<td><?= htmlspecialchars($gift['created_at'] ?? '-') ?></td>
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
