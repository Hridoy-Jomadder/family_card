<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$DB = new Database();
$conn = $DB->connect();

// Fetch bottom 10 poor families by balance
$query = "
SELECT id, family_name, full_name, family_image, family_members,
mobile_number, balance
FROM users
ORDER BY balance ASC
LIMIT 10
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Top 10 Poor Families | Family Card System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background:#f5f7fb;
    font-family: 'Times New Roman', Times, serif;
}
.header {
    background: linear-gradient(135deg,#dc3545,#ffc107);
    color:white;
    padding:40px;
    text-align:center;
    margin-bottom:30px;
}
.header h1{
    font-weight:700;
}
.subtitle{
    font-size:18px;
    opacity:0.9;
}
.card{
    border:none;
    border-radius:10px;
    box-shadow:0px 5px 15px rgba(0,0,0,0.1);
}
.family-img{
    width:60px;
    height:60px;
    border-radius:50%;
    object-fit:cover;
    cursor:pointer;
}
.table thead{
    background:#212529;
    color:white;
}
.rank-badge{
    background:#fd7e14;
    color:white;
    padding:6px 10px;
    border-radius:20px;
    font-weight:bold;
}
.balance{
    font-weight:700;
    color:#dc3545;
}
.footer{
    text-align:center;
    margin-top:40px;
    color:#777;
    font-size:14px;
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
<h1><i class="fas fa-heart-broken"></i> Top 10 Poor Families</h1>
<p class="subtitle">Family Card System – Economic Status Ranking Dashboard</p>
</div>

<div class="container">
<div class="card">
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover table-bordered align-middle">
<thead>
<tr>
<th>Rank</th>
<th>Family Name</th>
<th>Full Name</th>
<th>Profile</th>
<th>Members</th>
<th>Mobile</th>
<th>Balance</th>
</tr>
</thead>
<tbody>
<?php
$rank = 1;
while($row = $result->fetch_assoc()){
    $img = !empty($row['family_image']) ? $row['family_image'] : 'uploads/default-image.jpg';
?>
<tr>
<td><span class="rank-badge"><?= $rank++; ?></span></td>
<td><?= htmlspecialchars($row['family_name']); ?></td>
<td><?= htmlspecialchars($row['full_name']); ?></td>
<td>
<img src="<?= $img ?>" class="family-img" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImage('<?= $img ?>')">
</td>
<td><?= $row['family_members']; ?></td>
<td><?= $row['mobile_number']; ?></td>
<td class="balance"><i class="fas fa-coins"></i> <?= number_format((float)$row['balance']); ?> Tk</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<!-- IMAGE MODAL -->
<div class="modal fade" id="imageModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-body text-center">
<img id="modalImage" style="width:100%; max-height:80vh; object-fit:contain;">
</div>
</div>
</div>
</div>

<!-- FOOTER -->
<div class="footer">
© <?= date("Y"); ?> Family Card System – Economic Monitoring Dashboard
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showImage(src){
    document.getElementById("modalImage").src = src;
}
</script>
</body>
</html>