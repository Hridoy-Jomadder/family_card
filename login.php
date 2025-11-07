<?php
session_start();
include "classes/connection.php";

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nid = $_POST['nidnumber']; // Match the form name attribute
    $password = $_POST['password'];

    $DB = new Database();
    $conn = $DB->connect();

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Use prepared statements for security
    $stmt = $conn->prepare("SELECT * FROM users WHERE nid_number = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Show SQL preparation error
    }

    $stmt->bind_param("s", $nid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(); // Regenerate session ID for security
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "Invalid NID.";
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
</head>
<body>
    <div class="container0" style="background-image: url('img/022.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <h1>Welcome to Family Card</h1>
        <h4>Hand in hand, the country of pride is Shahid Zia Bangladesh.</h4>
        <br>
        <h2>Login</h2>

        <!-- Display error messages -->
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form action="" method="POST">
            <label for="nidnumber">NID Number:</label>
            <input type="text" id="nidnumber" name="nidnumber" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label>
                <input type="checkbox" id="remember" name="remember" <?php if (!empty($_COOKIE['remember_nid'])) echo 'checked'; ?>>
                Remember my NID on this device
            </label>

           

            <button type="submit">Login</button>
        </form>
        <br><br>
        <p>Don't have an account? <a href="register_family.php" style="text-decoration: none;">Register here</a>.</p>
    </div>
     <script>
            (function(){
                function getCookie(name){
                    const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
                    return v ? decodeURIComponent(v.pop()) : '';
                }
                function setCookie(name, value, days){
                    const d = new Date();
                    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
                    document.cookie = name + "=" + encodeURIComponent(value) + ";path=/;expires=" + d.toUTCString();
                }
                function deleteCookie(name){
                    document.cookie = name + "=;path=/;expires=Thu, 01 Jan 1970 00:00:00 GMT";
                }

                // Prefill NID if cookie exists
                const savedNid = getCookie('remember_nid');
                if (savedNid) {
                    const nidField = document.getElementById('nidnumber');
                    if (nidField && !nidField.value) nidField.value = savedNid;
                }

                // On submit set or remove the cookie (only NID, not password)
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function(){
                        const remember = document.getElementById('remember');
                        const nid = (document.getElementById('nidnumber') || {}).value || '';
                        if (remember && remember.checked && nid) {
                            setCookie('remember_nid', nid, 30); // keep for 30 days
                        } else {
                            deleteCookie('remember_nid');
                        }
                    });
                }
            })();
            </script>
</body>
</html>
