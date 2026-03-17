<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$DB = new Database();
$conn = $DB->connect();

$min_amount = $_GET['min_amount'] ?? 0;
$max_amount = $_GET['max_amount'] ?? 999999999;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sort_by = $_GET['sort_by'] ?? 'balance';
$order_by = ($sort_by == 'total_amount') ? 'total_amount' : 'balance';

$query = "
SELECT id,family_name,full_name,family_image,family_members,
mobile_number,nid_number,family_card_number,
job,job_type,job_salary,total_amount,balance
FROM users
WHERE balance BETWEEN ? AND ?
ORDER BY $order_by DESC
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ddii", $min_amount,$max_amount,$limit,$offset);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$count_query = "SELECT COUNT(*) as total FROM users WHERE balance BETWEEN ? AND ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("dd",$min_amount,$max_amount);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];

$total_pages = ceil($total_rows/$limit);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Profile - Amount Search</title>
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
</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #fff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
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

<div class="container">
           <!-- Star Start -->
           <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                <?php if (!empty($message)): ?>
                        <p style="color: black;text-align: center; font-size: 22px;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <!-- <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-male fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Male Family</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-female fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Female Family</p>
                                <h6 class="mb-0">123 </h6>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-area fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Right Information</p>
                                <h6 class="mb-0">100%</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-city fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">City</p>
                                <h6 class="mb-0">2 crore 82 lakh 60 thousand</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-tree fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Village</p>
                                <h6 class="mb-0">1 crore 10 lakh 70 thousand</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-id-card fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Family Card</p>
                                <h6 class="mb-0">3 crore 93 lakh 30 thousand</h6>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
     <!-- Star End -->
    <h1 class="text-center">Family Balance Search</h1>
    <div class="container">
    <div style="width: 100%;">
<form method="GET" class="row g-3 mb-4">

<div class="col-md-3">
<label>Minimum Amount</label>
<input type="number" name="min_amount" class="form-control"
value="<?php echo $min_amount ?>">
</div>

<div class="col-md-3">
<label>Maximum Amount</label>
<input type="number" name="max_amount" class="form-control"
value="<?php echo $max_amount ?>">
</div>

<div class="col-md-3">
<label>Sort By</label>
<select name="sort_by" class="form-control">

<option value="balance"
<?php if($sort_by=='balance') echo "selected"; ?>>
Balance
</option>

<option value="total_amount"
<?php if($sort_by=='total_amount') echo "selected"; ?>>
Total Amount
</option>

</select>
</div>

<div class="col-md-3">
<button class="btn btn-primary mt-4 w-100">Search</button>
</div>

</form>
    </div>
</div>





<!-- TABLE -->

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>
<th>Family Name</th>
<th>Full Name</th>
<th>Image</th>
<th>Members</th>
<th>Mobile</th>
<th>NID</th>
<th>Family Card</th>
<th>Job</th>
<th>Designation</th>
<th>Salary</th>
<th>Balance</th>

</tr>

</thead>

<tbody>

<?php foreach($users as $user){ ?>

<tr>

<td><?php echo $user['id']; ?></td>

<td><?php echo htmlspecialchars($user['family_name']); ?></td>

<td><?php echo htmlspecialchars($user['full_name']); ?></td>

<td>

<?php if(!empty($user['family_image'])){ ?>

<img src="<?php echo $user['family_image']; ?>"
width="60"
class="img-thumbnail"
data-bs-toggle="modal"
data-bs-target="#imageModal"
onclick="showImage(this.src)">

<?php } else { ?>

No Image

<?php } ?>

</td>

<td><?php echo $user['family_members']; ?></td>

<td><?php echo $user['mobile_number']; ?></td>

<td><?php echo $user['nid_number']; ?></td>

<td><?php echo $user['family_card_number']; ?></td>

<td><?php echo $user['job']; ?></td>

<td><?php echo $user['job_type']; ?></td>

<td><?php echo ($user['job_salary']); ?> Tk</td>

<td><?php echo ($user['balance']); ?> Tk</td>

</tr>

<?php } ?>

</tbody>

</table>

<!-- PAGINATION -->

<nav>

<ul class="pagination justify-content-center">

<?php for($i=1;$i<=$total_pages;$i++){ ?>

<li class="page-item <?php if($i==$page) echo 'active'; ?>">

<a class="page-link"
href="?page=<?php echo $i ?>
&min_amount=<?php echo $min_amount ?>
&max_amount=<?php echo $max_amount ?>
&sort_by=<?php echo $sort_by ?>">

<?php echo $i ?>

</a>

</li>

<?php } ?>

</ul>

</nav>

</div>

<!-- IMAGE MODAL -->

<div class="modal fade" id="imageModal">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-body text-center">

<img id="modalImage" style="width:100%">

</div>

</div>

</div>

</div>





    
</div>
<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

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

    
<script>

function showImage(src){

document.getElementById("modalImage").src = src;

}

</script>
</body>
</html>
