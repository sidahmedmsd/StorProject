<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $phone    = $_POST['phone'];

    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required";
    } else {
        // Basic insert
        $sql = "INSERT INTO users (username, email, password, role, phone) VALUES (:u, :e, :p, 'user', :ph)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":u", $username);
        oci_bind_by_name($stmt, ":e", $email);
        oci_bind_by_name($stmt, ":p", $password);
        oci_bind_by_name($stmt, ":ph", $phone);

        if (@oci_execute($stmt)) {
            $success = "Account created successfully! You can now <a href='login.php'>Login</a>";
        } else {
            $e = oci_error($stmt);
            $error = "Error creating account: " . $e['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Create New Account</h2>
    
    <?php if($error): ?>
        <div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div style="color: green; text-align: center; margin-bottom: 15px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="post" novalidate>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="login.php" style="color:var(--primary-color)">Login</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
