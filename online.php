<?php
include "classes/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$DB = new Database();
$conn = $DB->connect();

$message = "";
$existingData = [];

/* =========================
   LOAD EXISTING DATA
========================= */
$stmt = $conn->prepare("
    SELECT * 
    FROM family_full_info 
    WHERE user_id=? 
    ORDER BY id DESC 
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $existingData = $result->fetch_assoc();
}

$stmt->close();

/* =========================
   SECURE UPLOAD FUNCTION
========================= */
function uploadFile($field, $nid, $oldFile = "")
{
    if (
        !isset($_FILES[$field]) ||
        empty($_FILES[$field]['name'])
    ) {
        return $oldFile;
    }

    $safeNid = preg_replace('/[^0-9]/', '', $nid);

    $uploadDir = "uploads/" . $safeNid . "/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png'];
    $allowedExt   = ['jpg', 'jpeg', 'png'];

    $tmpName = $_FILES[$field]['tmp_name'];

    // MIME CHECK
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $ext = strtolower(
        pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION)
    );

    // TYPE CHECK
    if (!in_array($fileType, $allowedTypes)) {
        return $oldFile;
    }

    // EXTENSION CHECK
    if (!in_array($ext, $allowedExt)) {
        return $oldFile;
    }

    // SIZE LIMIT (2MB)
    if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
        return $oldFile;
    }

    $fileName = uniqid() . "." . $ext;

    if (
        move_uploaded_file(
            $tmpName,
            $uploadDir . $fileName
        )
    ) {
        return $fileName;
    }

    return $oldFile;
}

/* =========================
   SAVE / UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nidNumber = trim($_POST['nid_number']);

    // Upload Images
    $nid_image      = uploadFile("nid_image", $nidNumber, $existingData['nid_image'] ?? '');
    $passport_image = uploadFile("passport_image", $nidNumber, $existingData['passport_image'] ?? '');
    $birth_image    = uploadFile("birth_image", $nidNumber, $existingData['birth_image'] ?? '');
    $company_image  = uploadFile("company_image", $nidNumber, $existingData['company_image'] ?? '');
    $car_image      = uploadFile("car_image", $nidNumber, $existingData['car_image'] ?? '');
    $farm_image     = uploadFile("farm_image", $nidNumber, $existingData['farm_image'] ?? '');
    $pond_image     = uploadFile("pond_image", $nidNumber, $existingData['pond_image'] ?? '');
    $land_image     = uploadFile("land_image", $nidNumber, $existingData['land_image'] ?? '');
    $house_image    = uploadFile("house_image", $nidNumber, $existingData['house_image'] ?? '');
    $other_image    = uploadFile("other_image", $nidNumber, $existingData['other_image'] ?? '');

    /* =========================
       UPDATE
    ========================= */
    if (!empty($existingData)) {

        $stmt = $conn->prepare("
            UPDATE family_full_info SET

                nid_number=?,
                spouse_nid=?,
                father_nid=?,
                mother_nid=?,

                son1=?,
                son2=?,
                son3=?,

                daughter1=?,
                daughter2=?,
                daughter3=?,

                other_member=?,

                car_name=?,
                house_name=?,

                company_name=?,
                company_value=?,

                farm_name=?,
                farm_value=?,

                pond_area=?,
                pond_value=?,

                land_name=?,
                land_value=?,

                nid_image=?,
                passport_image=?,
                birth_image=?,
                company_image=?,
                car_image=?,
                farm_image=?,
                pond_image=?,
                land_image=?,
                house_image=?,
                other_image=?

            WHERE id=?
        ");

        $stmt->bind_param(

            str_repeat("s", 31) . "i",

            $_POST['nid_number'],
            $_POST['spouse_nid'],
            $_POST['father_nid'],
            $_POST['mother_nid'],

            $_POST['son1'],
            $_POST['son2'],
            $_POST['son3'],

            $_POST['daughter1'],
            $_POST['daughter2'],
            $_POST['daughter3'],

            $_POST['other_member'],

            $_POST['car_name'],
            $_POST['house_name'],

            $_POST['company_name'],
            $_POST['company_value'],

            $_POST['farm_name'],
            $_POST['farm_value'],

            $_POST['pond_area'],
            $_POST['pond_value'],

            $_POST['land_name'],
            $_POST['land_value'],

            $nid_image,
            $passport_image,
            $birth_image,
            $company_image,
            $car_image,
            $farm_image,
            $pond_image,
            $land_image,
            $house_image,
            $other_image,

            $existingData['id']
        );

        if ($stmt->execute()) {
            $message = "✅ Data updated successfully!";
        } else {
            $message = "❌ Update Error: " . $stmt->error;
        }

        $stmt->close();

    } else {

        /* =========================
           INSERT
        ========================= */

        $stmt = $conn->prepare("
            INSERT INTO family_full_info (

                user_id,

                nid_number,
                spouse_nid,
                father_nid,
                mother_nid,

                son1,
                son2,
                son3,

                daughter1,
                daughter2,
                daughter3,

                other_member,

                car_name,
                house_name,

                company_name,
                company_value,

                farm_name,
                farm_value,

                pond_area,
                pond_value,

                land_name,
                land_value,

                nid_image,
                passport_image,
                birth_image,
                company_image,
                car_image,
                farm_image,
                pond_image,
                land_image,
                house_image,
                other_image

            ) VALUES (

                ?,?,?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,?,?,
                ?,?

            )
        ");

        $stmt->bind_param(

            "i" . str_repeat("s", 31),

            $user_id,

            $_POST['nid_number'],
            $_POST['spouse_nid'],
            $_POST['father_nid'],
            $_POST['mother_nid'],

            $_POST['son1'],
            $_POST['son2'],
            $_POST['son3'],

            $_POST['daughter1'],
            $_POST['daughter2'],
            $_POST['daughter3'],

            $_POST['other_member'],

            $_POST['car_name'],
            $_POST['house_name'],

            $_POST['company_name'],
            $_POST['company_value'],

            $_POST['farm_name'],
            $_POST['farm_value'],

            $_POST['pond_area'],
            $_POST['pond_value'],

            $_POST['land_name'],
            $_POST['land_value'],

            $nid_image,
            $passport_image,
            $birth_image,
            $company_image,
            $car_image,
            $farm_image,
            $pond_image,
            $land_image,
            $house_image,
            $other_image
        );

        if ($stmt->execute()) {
            $message = "✅ Data saved successfully!";
        } else {
            $message = "❌ Insert Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Top Family Full Information</title>

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
    <link rel="stylesheet" href="css/stylel.css">

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

<style>
    .form{
    width: 50%;
    margin: 0 auto;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    };

</style>
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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm">
                <div class="card-body">

                    <?php if($message): ?>
                        <div class="alert alert-info"><?= $message ?></div>
                    <?php endif; ?>

                    <h3 class="text-center mb-4 fw-bold text-dark">
                        Family Information Management
                    </h3>

                    <div class="alert alert-light border shadow-sm">

                        <h5 class="fw-bold mb-3 text-primary">
                            <i class="fas fa-shield-alt me-2"></i>
                            System Overview
                        </h5>

                        <p class="mb-3 text-muted">
                            This platform is designed to securely manage family information,
                            personal records, assets, and supporting documents within a
                            centralized digital system.
                        </p>

                        <h6 class="fw-bold text-dark mb-2">
                            Guidelines & Compliance
                        </h6>

                        <ul class="mb-0 text-muted">
                            <li>All submitted information must be accurate and verifiable.</li>
                            <li>Uploading false, misleading, or unauthorized documents is prohibited.</li>
                            <li>National ID (NID) information must be authentic and valid.</li>
                            <li>Users are responsible for maintaining the confidentiality of their account.</li>
                            <li>Any misuse of the system may result in account suspension or administrative action.</li>
                        </ul>

                    </div>

                    <form method="POST" enctype="multipart/form-data">

                        <!-- ================= BASIC INFO ================= -->
                        <h5 class="mt-3">Basic Information</h5>
                        <div class="row">

                            <div class="col-md-6 mb-2">
                                <input type="text" name="nid_number" class="form-control"
                                    placeholder="NID Number" required
                                    value="<?= $existingData['nid_number'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="text" name="spouse_nid" class="form-control"
                                    placeholder="Spouse NID"
                                    value="<?= $existingData['spouse_nid'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="text" name="father_nid" class="form-control"
                                    placeholder="Father NID"
                                    value="<?= $existingData['father_nid'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="text" name="mother_nid" class="form-control"
                                    placeholder="Mother NID"
                                    value="<?= $existingData['mother_nid'] ?? '' ?>">
                            </div>

                        </div>

                        <!-- ================= FAMILY MEMBERS ================= -->
                        <h5 class="mt-4">Family Members</h5>
                        <div class="row">

                            <?php for($i=1;$i<=3;$i++): ?>
                            <div class="col-md-4 mb-2">
                                <input type="text" name="son<?= $i ?>" class="form-control"
                                    placeholder="<?= $i ?>. Son NID"
                                    value="<?= $existingData["son$i"] ?? '' ?>">
                            </div>
                            <?php endfor; ?>

                            <?php for($i=1;$i<=3;$i++): ?>
                            <div class="col-md-4 mb-2">
                                <input type="text" name="daughter<?= $i ?>" class="form-control"
                                    placeholder="<?= $i ?>. Daughter NID"
                                    value="<?= $existingData["daughter$i"] ?? '' ?>">
                            </div>
                            <?php endfor; ?>

                        </div>

                        <div class="mb-2">
                            <input type="text" name="other_member" class="form-control"
                                placeholder="Other Members NID "
                                value="<?= $existingData['other_member'] ?? '' ?>">
                        </div>

                        <!-- ================= ASSETS ================= -->
                        <h5 class="mt-4">Assets Information</h5>
                        <div class="row">

                            <?php
                            $fields = [
                                "car_name","house_name","company_name","company_value",
                                "farm_name","farm_value","pond_area","pond_value",
                                "land_name","land_value"
                            ];

                            foreach($fields as $f):
                            ?>
                            <div class="col-md-6 mb-2">
                                <input type="text" name="<?= $f ?>" class="form-control"
                                    placeholder="<?= ucwords(str_replace('_',' ',$f)) ?>"
                                    value="<?= $existingData[$f] ?? '' ?>">
                            </div>
                            <?php endforeach; ?>

                        </div>

                        <!-- ================= FILE UPLOAD ================= -->
                        <h5 class="mt-4">Document Upload</h5>

                        <?php
                        $uploads = [
                            "nid_image" => "National ID Image",
                            "passport_image" => "Passport Image",
                            "birth_image" => "Birth Certificate Image",
                            "company_image" => "Company Office Image",
                            "car_image" => "Car Image",
                            "farm_image" => "Farm Image",
                            "pond_image" => "Pond Image",
                            "land_image" => "Land Image",
                            "house_image" => "House Image",
                            "other_image" => "Other Image"
                        ];
                        ?>

                        <div class="row">

                        <?php foreach($uploads as $key=>$label): ?>
                            <div class="col-md-6 mb-3">

                                <label class="form-label"><?= $label ?></label>

                                <input type="file" name="<?= $key ?>" class="form-control">

                                <?php if(!empty($existingData[$key])): ?>
                                    <img src="uploads/<?= $existingData['nid_number'] ?>/<?= $existingData[$key] ?>"
                                         class="img-thumbnail mt-2" width="120">
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>

                        </div>

                        <!-- ================= SUBMIT ================= -->
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                Save Information
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
<!-- Back to Top Button (Right-Aligned) -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top position-fixed bottom-0 end-0 m-4">
    <i class="bi bi-arrow-up"></i>
</a>

</body>
</html>