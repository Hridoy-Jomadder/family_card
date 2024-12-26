<?php
include "classes/connection.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $address = trim($_POST['address']);
    $number = trim($_POST['number']);

    if (!$email) {
        $message = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Check if all fields are filled
        if (!empty($username) && $email && !empty($password) && !empty($role) && !empty($address) && !empty($number)) {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Create a new Database object and get connection
            $DB = new Database();
            $conn = $DB->connect();

            if ($conn) {
                // Check if email or username already exists
                $result = $conn->query("SELECT id FROM leader WHERE email = '$email'");
                if ($result->num_rows > 0) {
                    $message = "This email is already registered.";
                } else {
                    $result = $conn->query("SELECT id FROM leader WHERE username = '$username'");
                    if ($result->num_rows > 0) {
                        $message = "This username is already taken.";
                    } else {
                        // Prepare the SQL statement
                        $stmt = $conn->prepare("INSERT INTO leader (username, email, password, role, address, number) VALUES (?, ?, ?, ?, ?, ?)");

                        if ($stmt) {
                            // Bind parameters and execute the statement
                            $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $role, $address, $number);

                            if ($stmt->execute()) {
                                $message = "Registration successful!";
                                header('Location: login.php'); 
                                exit;
                            } else {
                                $message = "SQL Execution Error: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $message = "SQL Preparation Error: " . $conn->error;
                        }
                    }
                }
                $conn->close();
            } else {
                $message = "Database connection error.";
            }
        } else {
            $message = "Please fill in all required fields with valid information.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Leader</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylel.css">
</head>
<body>
    <div class="container0">
        <h1>Welcome to Family Card</h1><br>
        <h4>Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4><br>
        <h2>Register Leader</h2>
        <?php if (!empty($message)): ?>
            <p style="color: red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="Admin">Admin</option>
                <option value="Editor">Editor</option>
                <option value="User" selected>User</option>
            </select>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="number">Phone Number:</label>
            <input type="text" id="number" name="number" required>

            <button type="submit">Register</button>

            <p>Do have an account? <a href="login.php">Login</a>.</p>
        </form>
    </div>
</body>
</html>
