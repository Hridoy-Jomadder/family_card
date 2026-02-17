<?php
include "classes/connection.php";
$conn = (new Database())->connect();

$query = "
SELECT d.name_en, COUNT(u.id) as total
FROM divisions d
LEFT JOIN users u ON u.division_id = d.id
GROUP BY d.id
ORDER BY d.name_en ASC
";

$result = $conn->query($query);
$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<h4>📊 Division Wise Family Count</h4>

<canvas id="myChart"></canvas>
</div>

<script>
let labels = <?= json_encode(array_column($data,'name_en')) ?>;
let values = <?= json_encode(array_column($data,'total')) ?>;

new Chart(document.getElementById('myChart'),{
    type:'bar',
    data:{
        labels:labels,
        datasets:[{
            label:'Total Families',
            data:values
        }]
    }
});
</script>

</body>
</html>
