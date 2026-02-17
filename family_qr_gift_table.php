<?php
include "config.php";

$sql = "
SELECT 
    u.family_name,
    u.card,
    g.gift1,
    g.gift2,
    g.gift3
FROM users u
LEFT JOIN gift g ON u.card = g.card
";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Family Card Gift Table</title>
<style>
body{
    font-family:Arial;
    background:#f2f2f2;
}
table{
    width:95%;
    margin:auto;
    border-collapse:collapse;
    background:white;
}
th,td{
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}
th{
    background:#006a4e;
    color:white;
}
img{
    width:90px;
}
</style>
</head>

<body>

<h2 align="center">Family Card QR & Gift Information</h2>

<table>
<tr>
    <th>Family Name</th>
    <th>Card No</th>
    <th>QR Code</th>
    <th>Gift 1</th>
    <th>Gift 2</th>
    <th>Gift 3</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?= $row['family_name'] ?></td>
    <td><?= $row['card'] ?></td>
    <td>
        <img src="qrcodes/<?= $row['card'] ?>.png">
    </td>
    <td><?= $row['gift1'] ?? '—' ?></td>
    <td><?= $row['gift2'] ?? '—' ?></td>
    <td><?= $row['gift3'] ?? '—' ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>
