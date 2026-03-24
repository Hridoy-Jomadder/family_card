<?php
include "classes/connection.php";
$conn = (new Database())->connect();

$query="
SELECT d.name_en, COUNT(u.id) total
FROM divisions d
LEFT JOIN users u ON u.division_id=d.id
GROUP BY d.id
ORDER BY d.name_en ASC
";

$data=$conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Division Wise Family Count</title>
<meta content="" name="keywords">
    <meta content="" name="description">
    <meta content="Hridoy Jomadder" name="author">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/stylel.css" rel="stylesheet">


    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

        <style>
        .profile-container {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            margin: 20px auto;
            border-radius: 10px;
            width: 80%;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-image {
            /* border-radius: 100%; */
            width: 600px;
            height: 450px;
            border: 3px solid #e9da09;
        }
        .message {
            margin: 10px 0;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #ffffff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
</div>

<div class="navbar">
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="division_wise_family_count.php">Division Wise Family</a>
    <a href="family_information.php">Family Information</a>
    <a href="family_assets_information.php">Family Assets Information</a>
    <a href="gift_send.php">Send Gift</a>
    <a href="gift.php">Gifts</a>
    <a href="search.php">Search</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
</div>
<div class="container mt-5" style="display: block;">
<h4>📊 Division Wise Family Count</h4>

<canvas id="chart"></canvas>

<table class="table table-bordered mt-4">
<tr><th>Division</th><th>Total Families</th></tr>

<?php foreach($data as $row): ?>
<tr>
<td><?= htmlspecialchars($row['name_en']) ?></td>
<td><?= $row['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>

<script>
new Chart(document.getElementById('chart'),{
    type:'bar',
    data:{
        labels:<?= json_encode(array_column($data,'name_en')) ?>,
        datasets:[{
            label:'Total Families',
            data:<?= json_encode(array_column($data,'total')) ?>
        }]
    }
});
</script>


<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
<!-- silder & Arabic -->
    <div id="quranSlider" class="carousel slide mt-4" data-bs-ride="carousel" data-bs-interval="300"></div>
    <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet">

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

</body>
</html>