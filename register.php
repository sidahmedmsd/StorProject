<?php
include 'db.php';


// If user is already logged in, redirect to index
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone    = $_POST['phone'];

    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username unique
        $check_sql = "SELECT COUNT(*) AS CNT FROM users WHERE username = :u";
        $check_stmt = oci_parse($conn, $check_sql);
        oci_bind_by_name($check_stmt, ":u", $username);
        oci_execute($check_stmt);
        $row = oci_fetch_assoc($check_stmt);
        if ($row['CNT'] > 0) {
            $error = "Username already taken";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, email, password, role, phone) VALUES (:u, :e, :p, 'user', :ph)";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":u", $username);
            oci_bind_by_name($stmt, ":e", $email);
            oci_bind_by_name($stmt, ":p", $hashed_password);
            oci_bind_by_name($stmt, ":ph", $phone);

            if (@oci_execute($stmt)) {
                oci_commit($conn); // IMPORTANT for Oracle
                header("Location: login.php");
                exit();
            } else {
                $e = oci_error($stmt);
                $error = "Error creating account: " . $e['message'];
            }
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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="login-body <?php echo $theme_class; ?>">

<div class="login-wrapper">
    <!-- Image Section -->
    <div class="login-image-section">
        <div class="login-image-overlay"></div>
        <img src="photo_2026-01-07_20-21-55.jpg" alt="Register Visual">
    </div>

    <!-- Form Section -->
    <div class="login-form-section">
        <div class="login-form-container">
            <h2>Create New Account</h2>
            <span class="login-subtitle">Join us to explore our products</span>

            <?php if($error): ?>
                <div class="error-message shake" style="color: #e74c3c; background: #fceae9; padding: 10px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #e74c3c;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post" novalidate>
                <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <input type="text" name="phone" placeholder="Phone Number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Sign Up</button>
            </form>

            <div class="login-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
