<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$DB = new Database();
$conn = $DB->connect();

$message = "";
$existingData = [];

// Load existing data if user already has a record
$result = $conn->query("SELECT * FROM family_full_info WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
if($result && $result->num_rows > 0){
    $existingData = $result->fetch_assoc();
}

// Upload function
function uploadFile($name, $nidNumber, $oldFile = ''){
    $uploadDir = "uploads/".$nidNumber."/";
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if(!empty($_FILES[$name]['name'])){
        $fileName = time().'_'.basename($_FILES[$name]['name']);
        move_uploaded_file($_FILES[$name]['tmp_name'], $uploadDir.$fileName);
        return $fileName;
    }
    return $oldFile; // keep old file if no new upload
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nid_number'])) {

    $nidNumber = trim($_POST['nid_number']);

    $nid_image       = uploadFile("nid_image", $nidNumber, $existingData['nid_image'] ?? '');
    $passport_image  = uploadFile("passport_image", $nidNumber, $existingData['passport_image'] ?? '');
    $birth_image     = uploadFile("birth_image", $nidNumber, $existingData['birth_image'] ?? '');
    $company_image   = uploadFile("company_image", $nidNumber, $existingData['company_image'] ?? '');
    $car_image       = uploadFile("car_image", $nidNumber, $existingData['car_image'] ?? '');
    $farm_image      = uploadFile("farm_image", $nidNumber, $existingData['farm_image'] ?? '');

    if(!empty($existingData)){ 
        // UPDATE existing record
        $stmt = $conn->prepare("
            UPDATE family_full_info SET
                nid_number=?, spouse_nid=?, father_nid=?, mother_nid=?,
                son1=?, son2=?, son3=?, daughter1=?, daughter2=?, daughter3=?,
                other_member=?, car_name=?, house_name=?, company_name=?, company_value=?,
                farm_name=?, farm_value=?, pond_area=?, pond_value=?, land_name=?, land_value=?,
                nid_image=?, passport_image=?, birth_image=?, company_image=?, car_image=?, farm_image=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "sssssssssssssssssssssssssssi",
            $_POST['nid_number'], $_POST['spouse_nid'], $_POST['father_nid'], $_POST['mother_nid'],
            $_POST['son1'], $_POST['son2'], $_POST['son3'], $_POST['daughter1'], $_POST['daughter2'], $_POST['daughter3'],
            $_POST['other_member'], $_POST['car_name'], $_POST['house_name'], $_POST['company_name'], $_POST['company_value'],
            $_POST['farm_name'], $_POST['farm_value'], $_POST['pond_area'], $_POST['pond_value'], $_POST['land_name'], $_POST['land_value'],
            $nid_image, $passport_image, $birth_image, $company_image, $car_image, $farm_image,
            $existingData['id']
        );

        if($stmt->execute()){
            $message = "✅ Data updated successfully!";
        } else {
            $message = "❌ Update Error: ".$stmt->error;
        }

    } else {
        // INSERT new record
        $stmt = $conn->prepare("
            INSERT INTO family_full_info(
                user_id, nid_number, spouse_nid, father_nid, mother_nid,
                son1, son2, son3, daughter1, daughter2, daughter3,
                other_member, car_name, house_name, company_name, company_value,
                farm_name, farm_value, pond_area, pond_value, land_name, land_value,
                nid_image, passport_image, birth_image, company_image, car_image, farm_image
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "isssssssssssssssssssssssssss",
            $user_id,
            $_POST['nid_number'], $_POST['spouse_nid'], $_POST['father_nid'], $_POST['mother_nid'],
            $_POST['son1'], $_POST['son2'], $_POST['son3'], $_POST['daughter1'], $_POST['daughter2'], $_POST['daughter3'],
            $_POST['other_member'], $_POST['car_name'], $_POST['house_name'], $_POST['company_name'], $_POST['company_value'],
            $_POST['farm_name'], $_POST['farm_value'], $_POST['pond_area'], $_POST['pond_value'], $_POST['land_name'], $_POST['land_value'],
            $nid_image, $passport_image, $birth_image, $company_image, $car_image, $farm_image
        );

        if($stmt->execute()){
            $message = "✅ Data saved successfully!";
        } else {
            $message = "❌ Insert Error: ".$stmt->error;
        }
    }

    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Top Family Full Information</title>
<link href="css/bootstrap.min.css" rel="stylesheet" />
<link href="css/style.css" rel="stylesheet" />
</head>
<body>
<div class="header">
    <img src="img/Government_Seal_of_Bangladesh.png" style="width: 60px;">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color: #fff;">Hand in hand, the country of pride is Shahid Ziaur Rahman Bangladesh.</h4>
</div>

<div class="navbar">
    <a href="index.php" class="active">Home</a>
    <a href="profile.php">Profile</a>
    <a href="asset.php">Asset</a>
    <a href="job.php">Govt./Company Job</a>
    <a href="gift.php">Gift</a>
    <a href="information.php">Information</a>
    <a href="months.php">Months</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container mt-4" d>

    <form method="POST" enctype="multipart/form-data">
        <?php if($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>
            <h2>Top Family Full Information</h2>

        <input type="text" name="nid_number" class="form-control mb-2" placeholder="NID Number" required value="<?= $existingData['nid_number'] ?? '' ?>">
        <input type="text" name="spouse_nid" class="form-control mb-2" placeholder="Spouse NID" value="<?= $existingData['spouse_nid'] ?? '' ?>">
        <input type="text" name="father_nid" class="form-control mb-2" placeholder="Father NID" value="<?= $existingData['father_nid'] ?? '' ?>">
        <input type="text" name="mother_nid" class="form-control mb-2" placeholder="Mother NID" value="<?= $existingData['mother_nid'] ?? '' ?>">
        <br>
        <h5>Sons NID</h5>
        <input type="text" name="son1" placeholder="Son NID" class="form-control mb-2" value="<?= $existingData['son1'] ?? '' ?>">
        <input type="text" name="son2" placeholder="Son NID" class="form-control mb-2" value="<?= $existingData['son2'] ?? '' ?>">
        <input type="text" name="son3" placeholder="Son NID" class="form-control mb-2" value="<?= $existingData['son3'] ?? '' ?>">
        <br>
        <h5>Daughters NID</h5>
        <input type="text" name="daughter1" placeholder="Daughter NID" class="form-control mb-2" value="<?= $existingData['daughter1'] ?? '' ?>">
        <input type="text" name="daughter2" placeholder="Daughter NID" class="form-control mb-2" value="<?= $existingData['daughter2'] ?? '' ?>">
        <input type="text" name="daughter3" placeholder="Daughter NID" class="form-control mb-2" value="<?= $existingData['daughter3'] ?? '' ?>">
        <br>
        <h4>Other Members NID</h4>
        <input type="text" name="other_member" class="form-control mb-2" placeholder="Other Member" value="<?= $existingData['other_member'] ?? '' ?>">
        <br>
        <h5>Assets</h5>
        <input type="text" name="car_name" placeholder="Car Name" class="form-control mb-2" value="<?= $existingData['car_name'] ?? '' ?>">
        <input type="text" name="house_name" placeholder="House Name" class="form-control mb-2" value="<?= $existingData['house_name'] ?? '' ?>">
        <input type="text" name="company_name" placeholder="Company Name" class="form-control mb-2" value="<?= $existingData['company_name'] ?? '' ?>">
        <input type="text" name="company_value" placeholder="Company Value" class="form-control mb-2" value="<?= $existingData['company_value'] ?? '' ?>">
        <input type="text" name="farm_name" placeholder="Farm Name" class="form-control mb-2" value="<?= $existingData['farm_name'] ?? '' ?>">
        <input type="text" name="farm_value" placeholder="Farm Value" class="form-control mb-2" value="<?= $existingData['farm_value'] ?? '' ?>">
        <input type="text" name="pond_area" placeholder="Pond Area" class="form-control mb-2" value="<?= $existingData['pond_area'] ?? '' ?>">
        <input type="text" name="pond_value" placeholder="Pond Value" class="form-control mb-2" value="<?= $existingData['pond_value'] ?? '' ?>">
        <input type="text" name="land_name" placeholder="Land Name" class="form-control mb-2" value="<?= $existingData['land_name'] ?? '' ?>">
        <input type="text" name="land_value" placeholder="Land Value" class="form-control mb-2" value="<?= $existingData['land_value'] ?? '' ?>">
<br>
<h5>Documents</h5>
<h6>NID Image</h6>
<input type="file" name="nid_image" class="mb-2">
<?php if(!empty($existingData['nid_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['nid_image'] ?>" width="400">
<?php endif; ?>

<h6>Passport Image</h6>
<input type="file" name="passport_image" class="mb-2">
<?php if(!empty($existingData['passport_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['passport_image'] ?>" width="400">
<?php endif; ?>

<h6>Birth Image</h6>
<input type="file" name="birth_image" class="mb-2">
<?php if(!empty($existingData['birth_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['birth_image'] ?>" width="400">
<?php endif; ?>

<h6>Company Head Office Image</h6>
<input type="file" name="company_image" class="mb-2">
<?php if(!empty($existingData['company_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['company_image'] ?>" width="400">
<?php endif; ?>

<h6>Car Image</h6>
<input type="file" name="car_image" class="mb-2">
<?php if(!empty($existingData['car_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['car_image'] ?>" width="400">
<?php endif; ?>

<h6>Farm Image</h6>
<input type="file" name="farm_image" class="mb-2">
<?php if(!empty($existingData['farm_image'])): ?>
    <br><img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData['farm_image'] ?>" width="400">
<?php endif; ?>

        <button type="submit" class="btn btn-primary mt-3">Save</button>
    </form>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>