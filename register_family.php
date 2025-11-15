<?php
include "classes/connection.php"; // Database connection

// Initialize error message variable
$message = "";

// Create Database instance and get connection
$DB = new Database();
$conn = $DB->connect();

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect form data
    $familyName = $_POST['family_name'];
    $nidNumber = $_POST['nidnumber'];
    $fullName = $_POST['full_name'];
    $fatherName = $_POST['father_name'];
    $motherName = $_POST['mother_name'];
    $mobileNumber = $_POST['mobile_number'];
    $familyMembers = $_POST['family_members'];
    $password = $_POST['password'];

    // Print the POST data for debugging
    // echo '<pre>';
    // var_dump($_POST);
    // echo '</pre>';

    // Validate required fields
    if ($familyName && $nidNumber && $fullName && $fatherName && $motherName && $password) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (family_name, family_image, nid_number, full_name, father_name, mother_name, mobile_number, family_members, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            echo "Error preparing statement: " . $conn->error;
        } else {
            $familyImage = 'uploads/default-image.jpg';  // Default value for family image

            // Bind the parameters: 9 parameters here because we have 9 values (including the family image)
            $stmt->bind_param("sssssssis", $familyName, $familyImage, $nidNumber, $fullName, $fatherName, $motherName, $mobileNumber, $familyMembers, $hashedPassword);

            // Execute the statement and check for success
            if ($stmt->execute()) {
                $message = "Registration successful!";
            } else {
                $message = "Error executing query: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Family Card</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container0" style="background-image: url('img/02.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <h2>Register Family Card</h2>
            <!-- Display message (success or error) -->
            <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="family_name">Family Name:</label>
            <input type="text" id="family_name" name="family_name" required>

            <label for="nidnumber">NID Number:</label>
            <input type="text" id="nidnumber" name="nidnumber" required>

            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="father_name">Father's Name:</label>
            <input type="text" id="father_name" name="father_name" required>

            <label for="mother_name">Mother's Name:</label>
            <input type="text" id="mother_name" name="mother_name" required>

            <label for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number">

            <label for="family_members">Number of Family Members:</label>
            <input type="number" id="family_members" name="family_members" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Register</button>
        </form>
          <br>
        <!-- Display message (success or error) -->
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <p>Do have an account? <a href="login.php" style="text-decoration: none;">Login</a>.</p>

    </div>
</body>
</html>
