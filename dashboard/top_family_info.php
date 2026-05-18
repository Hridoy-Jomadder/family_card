<?php

// ===============================
// DATABASE CONNECTION
// ===============================

include "classes/connection.php";

$DB = new Database();
$conn = $DB->connect();

// CHECK CONNECTION
if (!$conn) {
    die("Database Connection Failed");
}

// ===============================
// IMAGE FUNCTION
// ===============================

function showImage($img, $uploadPath){

    if(!empty($img)){

        return "<img src='".$uploadPath.$img."' width='70' height='70' class='img-thumbnail'>";
    }

    return "<span class='text-danger'>No Image</span>";
}

// ===============================
// PAGINATION
// ===============================

$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// ===============================
// FAMILY QUERY
// ===============================

$query = "
SELECT 
    u.id,
    u.family_name,
    f.*
FROM users u
LEFT JOIN family_full_info f 
ON u.id = f.user_id
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare Failed: " . $conn->error);
}

$stmt->bind_param("ii", $limit, $offset);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Top Family Information</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.table img{
    object-fit:cover;
    border-radius:6px;
}

.card-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

</style>

</head>
<body>

<div class="container-fluid mt-4">

<div class="card-box">

<h2 class="mb-4 text-center">
    Family Full Information Dashboard
</h2>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Family</th>
<th>NID</th>
<th>Spouse</th>
<th>Father</th>
<th>Mother</th>

<th>Son1</th>
<th>Son2</th>
<th>Son3</th>

<th>Daughter1</th>
<th>Daughter2</th>
<th>Daughter3</th>

<th>Other</th>

<th>Car</th>
<th>House</th>

<th>Company</th>
<th>Company Value</th>

<th>Farm</th>
<th>Farm Value</th>

<th>Pond</th>
<th>Pond Value</th>

<th>Land</th>
<th>Land Value</th>

<th>NID Image</th>
<th>Passport</th>
<th>Birth</th>
<th>House</th>
<th>Car</th>
<th>Farm</th>
<th>Land</th>
<th>Pond</th>
<th>Other</th>

</tr>

</thead>

<tbody>

<?php

if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {

        $uploadPath = "uploads/";

        echo "<tr>";

        echo "<td>".htmlspecialchars($row['id'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['family_name'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['nid_number'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['spouse_nid'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['father_nid'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['mother_nid'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['son1'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['son2'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['son3'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['daughter1'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['daughter2'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['daughter3'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['other_member'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['car_name'] ?? '')."</td>";
        echo "<td>".htmlspecialchars($row['house_name'] ?? '')."</td>";

        echo "<td>".htmlspecialchars($row['company_name'] ?? '')."</td>";
        echo "<td>".number_format((float)($row['company_value'] ?? 0))."</td>";

        echo "<td>".htmlspecialchars($row['farm_name'] ?? '')."</td>";
        echo "<td>".number_format((float)($row['farm_value'] ?? 0))."</td>";

        echo "<td>".htmlspecialchars($row['pond_area'] ?? '')."</td>";
        echo "<td>".number_format((float)($row['pond_value'] ?? 0))."</td>";

        echo "<td>".htmlspecialchars($row['land_name'] ?? '')."</td>";
        echo "<td>".number_format((float)($row['land_value'] ?? 0))."</td>";

        echo "<td>".showImage($row['nid_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['passport_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['birth_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['house_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['car_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['farm_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['land_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['pond_image'] ?? '', $uploadPath)."</td>";
        echo "<td>".showImage($row['other_image'] ?? '', $uploadPath)."</td>";

        echo "</tr>";
    }

}else{

    echo "
    <tr>
        <td colspan='32' class='text-center text-danger'>
            No Data Found
        </td>
    </tr>
    ";
}

?>

</tbody>

</table>

</div>

</div>

</div>

</body>
</html>