<?php
include "classes/connection.php";

// Start the session to access session variables
session_start();

// Initialize variables
$family_data = [];
$message = "";

// Check if user is logged in and session contains a valid user ID
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Retrieve user ID from session
} else {
    // Redirect to login page if no user ID in session
    header("Location: login.php");
    exit;
}

// Create a Database instance
$DB = new Database();
$conn = $DB->connect(); // Assuming `connect` is a method in your `Database` class

// Fetch family data based on the user ID
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id); // Bind the user ID as an integer

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $family_data = $result->fetch_assoc(); // Fetch user data
    } else {
        $message = "No family data found in the database.";
    }
} else {
    $message = "Error executing query: " . $stmt->error;
}

$stmt->close();

// Update profile data when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'job', 'job_type', 'job_salary', 'family_jc_members',
        'father_job', 'father_salary', 'mother_job', 'mother_salary',
        'wife_job', 'wife_salary', 'son_job', 'son_salary',
        'son_job1', 'son_salary1', 'son_job2', 'son_salary2',
        'dau_job', 'dau_salary', 'dau_job1', 'dau_salary1',
        'dau_job2', 'dau_salary2', 'family_other_members', 'family_other_members_salary'
    ];

    // Prepare dynamic query
    $updateFields = [];
    $values = [];
    foreach ($fields as $field) {
        $updateFields[] = "$field = ?";
        $values[] = $_POST[$field] ?? '';
    }
    $values[] = $_SESSION['user_id']; // Assuming user_id is stored in the session

    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);

    // Bind dynamic values
    $types = str_repeat('s', count($values) - 1) . 'i'; // Strings and final integer for user_id
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        $message2 = "Job details updated successfully!";
    } else {
        $message2 = "Failed to update job details.";
    }
    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit;
}
$total_salary = 0;

// List of salary fields to sum up
$salary_fields = [
    'job_salary', 
    'father_salary', 
    'mother_salary', 
    'wife_salary', 
    'son_salary', 
    'son_salary1', 
    'son_salary2', 
    'dau_salary', 
    'dau_salary1', 
    'dau_salary2', 
    'family_other_members_salary'
];

foreach ($salary_fields as $field) {
    $total_salary += (int)($family_data[$field] ?? 0); // Add salary values, defaulting to 0 if not set
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Profile</title>
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
    <link rel="stylesheet" href="css/stylel.css">

    <!-- Replace HTTP with HTTPS in the CDN links -->
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

</head>
<body>
<div class="header">
    <h1 style="color:white;">Welcome to Family Card</h1>
    <h4 style="color:white;">Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4>
</div> 
   <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="asset.php">Asset</a>
        <a href="jobcompany.php">Job/Company</a>
        <a href="gift.php">Gift</a>
        <a href="upload_family_image.php">Upload Image</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- <div class="container">
    <div style="width: 100%;padding: 50px; background-color: #5c9ded; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex;">
        <div>
             <h2 style="color:white;">Family Profile</h2>
        
        </div>
     </div>
    </div> -->

    <div class="container">
    <div style="width: 100%; padding: 50px; background-color: #0072ff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex; margin: 5px;">
        <div>
            <h2 style="color: white;">Job/Company Information</h2>
            <?php if (!empty($family_data)): ?>
                <div><br>
                    <p style="color:white;"><strong>Job/Company:</strong> <?= htmlspecialchars($family_data['job'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Designation:</strong> <?= htmlspecialchars($family_data['job_type'] ?? 'Not Available') ?></p>
                    <p style="color:white;"><strong>Annual Salary:</strong> <?= htmlspecialchars($family_data['job_salary'] ?? 'Not Available') ?> TK</p>
                    <p style="color:white;"><strong>Family Members in Job/Company:</strong> <?= htmlspecialchars($family_data['family_jc_members'] ?? 'Not Available') ?></p>

                    <!-- Dynamically handle family members' jobs/salaries -->
                    <?php
                    $roles = [
                        'Father' => ['father_job', 'father_salary'],
                        'Mother' => ['mother_job', 'mother_salary'],
                        'Wife' => ['wife_job', 'wife_salary'],
                        'Son' => ['son_job', 'son_salary'],
                        'Son1' => ['son_job1', 'son_salary1'],
                        'Son2' => ['son_job2', 'son_salary2'],
                        'Daughter' => ['dau_job', 'dau_salary'],
                        'Daughter1' => ['dau_job1', 'dau_salary1'],
                        'Daughter2' => ['dau_job2', 'dau_salary2'],
                        'Other Members' => ['family_other_members', 'family_other_members_salary']
                    ];

                    foreach ($roles as $label => [$jobField, $salaryField]) {
                        echo "<p style='color:white;'><strong>{$label}'s Job:</strong> " . htmlspecialchars($family_data[$jobField] ?? 'Not Available') . "</p>";
                        echo "<p style='color:white;'><strong>{$label}'s Salary:</strong> " . htmlspecialchars($family_data[$salaryField] ?? 'Not Available') . " TK</p>";
                    }
                    ?>
                </div>
            <?php else: ?>
                <p style="color:white;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <p style="color:white;"><strong>Total Annual Salary:</strong> <?= htmlspecialchars($total_salary) ?> TK</p>
        </div>
    </div>
</div>


    <div class="container">
    <div style="width: 100%; padding: 50px; background-color: #0072ff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); justify-content: center; display: flex; margin: 5px;">
    <div>
        <h2>Job/Company Edit</h2>
        <?php if (!empty($message2)): ?>
            <p style="color: green;"><?= htmlspecialchars($message2) ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="job">Job/Company:</label>
                <input type="text" class="form-control" id="job" name="job" 
                    value="<?= htmlspecialchars($family_data['job'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="job_type">Job/Company Designation:</label>
                <input type="text" class="form-control" id="job_type" name="job_type" 
                    value="<?= htmlspecialchars($family_data['job_type'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="job_salary">Job/Company Salary (Annually):</label>
                <input type="number" class="form-control" id="job_salary" name="job_salary" 
                    value="<?= htmlspecialchars($family_data['job_salary'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="family_jc_members">Family Job/Company Members:</label>
                <input type="text" class="form-control" id="family_jc_members" name="family_jc_members" 
                    value="<?= htmlspecialchars($family_data['family_jc_members'] ?? '') ?>" required>
            </div>
            <!-- Dynamically handle family members' jobs/salaries -->
            <?php 
            $roles = [
                'Father' => ['father_job', 'father_salary'],
                'Mother' => ['mother_job', 'mother_salary'],
                'Wife' => ['wife_job', 'wife_salary'],
                'Son' => ['son_job', 'son_salary'],
                'Son1' => ['son_job1', 'son_salary1'],
                'Son2' => ['son_job2', 'son_salary2'],
                'Daughter' => ['dau_job', 'dau_salary'],
                'Daughter1' => ['dau_job1', 'dau_salary1'],
                'Daughter2' => ['dau_job2', 'dau_salary2'],
                'Other Members' => ['family_other_members', 'family_other_members_salary']
            ];
            foreach ($roles as $label => [$jobField, $salaryField]) {
                ?>
                <div class="form-group">
                    <label for="<?= $jobField ?>"><?= $label ?>'s Job:</label>
                    <input type="text" class="form-control" id="<?= $jobField ?>" name="<?= $jobField ?>" 
                        value="<?= htmlspecialchars($family_data[$jobField] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="<?= $salaryField ?>"><?= $label ?>'s Salary:</label>
                    <input type="number" class="form-control" id="<?= $salaryField ?>" name="<?= $salaryField ?>" 
                        value="<?= htmlspecialchars($family_data[$salaryField] ?? '') ?>" required>
                </div>
            <?php } ?>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<!-- Back to Top Button (Right-Aligned) -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top position-fixed bottom-0 end-0 m-4">
    <i class="bi bi-arrow-up"></i>
</a>

</body>
</html>
