<?php
include "classes/connection.php"; // Database connection

// Initialize error message variable
$message = "";

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

    // Validate required fields
    if ($familyName && $nidNumber && $fullName && $fatherName && $motherName && $password) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO families (family_name, nid_number, full_name, father_name, mother_name, mobile_number, family_members, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssis", $familyName, $nidNumber, $fullName, $fatherName, $motherName, $mobileNumber, $familyMembers, $hashedPassword);

            // Execute the statement and check for success
            if ($stmt->execute()) {
                echo "Registration successful!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Please fill in all required fields.";
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
    <div class="container0">
        <h2>Register Family Card</h2>
        <form action="login.php" method="POST">
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
    </div>
</body>
</html>
