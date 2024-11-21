<?php
include "classes/connection.php"; // Ensure this path is correct

// Display HTML form for entering NID number
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    echo '
    <h2>View Family Details</h2>
    <form action="view_family.php" method="POST">
        <label for="nidnumber">Enter NID Number:</label>
        <input type="text" id="nidnumber" name="nidnumber" required>
        <button type="submit">Search</button>
    </form>
    ';
}

// Check if form data was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nidNumber = $_POST['nidnumber'] ?? '';

    if ($nidNumber) {
        // Prepare and execute the query to fetch family details
        $sql = "SELECT * FROM families WHERE nid_number = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $nidNumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Display family details
                $family = $result->fetch_assoc();
                echo "<h2>Family Details</h2>";
                echo "<p>Family Name: " . htmlspecialchars($family['family_name']) . "</p>";
                echo "<p>NID Number: " . htmlspecialchars($family['nid_number']) . "</p>";
                echo "<p>Full Name: " . htmlspecialchars($family['full_name']) . "</p>";
                echo "<p>Father Name: " . htmlspecialchars($family['father_name']) . "</p>";
                echo "<p>Mother Name: " . htmlspecialchars($family['mother_name']) . "</p>";
                echo "<p>Mobile Number: " . htmlspecialchars($family['mobile_number']) . "</p>";
                echo "<p>Number of Family Members: " . htmlspecialchars($family['family_members']) . "</p>";
                echo "<p>Family Card Number: " . htmlspecialchars($family['family_card_number']) . "</p>";
            } else {
                echo "<p>No family found with this NID number.</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Please provide a valid NID number.</p>";
    }
}

$conn->close();
?>
