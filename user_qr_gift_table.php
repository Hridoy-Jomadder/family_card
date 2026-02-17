<?php
include "config.php";

// Fetch users + gift info
$sql = "
SELECT 
    u.family_name,
    u.family_card_number,
    g.gift1,
    g.gift2,
    g.gift3
FROM users u
LEFT JOIN gift g ON u.family_card_number = g.card
ORDER BY u.family_name ASC
";

$result = mysqli_query($conn,$sql);
if(!$result){
    die("SQL Error: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Family Card QR & Gift Info</title>
<style>
body{font-family:Arial;background:#f2f2f2;}
table{width:95%;margin:auto;border-collapse:collapse;background:white;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
th{background:#006a4e;color:white;}
img{width:80px;}
button{
    padding:6px 12px;
    background:#ff9800;
    border:none;
    color:white;
    border-radius:4px;
    cursor:pointer;
}
button:hover{background:#e68a00;}
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
    <th>Download PDF</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= htmlspecialchars($row['family_name']) ?></td>
    <td><?= htmlspecialchars($row['family_card_number']) ?></td>
    <td>
        <img src="qrcodes/<?= $row['family_card_number'] ?>.png" alt="QR Code">
    </td>
    <td><?= htmlspecialchars($row['gift1'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['gift2'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['gift3'] ?? '-') ?></td>
    <td>
        <a href="id_card_pdf.php?card=<?= $row['family_card_number'] ?>">
            <button>Download PDF</button>
        </a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
