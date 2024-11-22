<?php
session_start(); // Ensure session is started
include "classes/connection.php";

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $DB = new Database();
    $conn = $DB->connect();

    if ($conn->connect_error) {
        $error_message = "Database connection failed. Please try again later.";
    } else {
        // Prepare and execute the SQL statement securely
        $stmt = $conn->prepare("SELECT * FROM leader WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);  // Use email to find the user
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify the hashed password
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(); // Prevent session fixation attacks
                    $_SESSION['user_id'] = $user['id']; // Store user id in session
                    header("Location: index.php"); // Redirect to a secure page after login
                    exit();
                } else {
                    $error_message = "Invalid login credentials.";
                }
            } else {
                $error_message = "Invalid login credentials.";
            }
        } else {
            error_log("Database prepare statement failed: " . $conn->error);
            $error_message = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylel.css">
</head>
<body>
    <div class="container0">
        <h1>Welcome to Family Card</h1><br>
        <h4>Hand in hand, the country of pride is Shahid Zia's Bangladesh.</h4><br>
        <h2>Login</h2>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST"> <!-- Change action to this page -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <br><br>
        <p>Don't have an account? <a href="register_family.php">Register here</a>.</p>
    </div>
</body>
</html>
