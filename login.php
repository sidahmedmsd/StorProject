<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        $sql = "SELECT * FROM users WHERE username = :u";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":u", $username);
        oci_execute($stmt);

        if ($row = oci_fetch_assoc($stmt)) {
            $db_password = $row['PASSWORD'];
            $user_id = $row['ID'];
            
            // 1. Check if password matches hash
            if (password_verify($password, $db_password)) {
                // Password is correct
                $_SESSION['user_id'] = $row['ID']; 
                $_SESSION['username'] = $row['USERNAME'];
                $_SESSION['role'] = $row['ROLE']; 
                header("Location: index.php"); 
                exit();
            } 
            // 2. Fallback: Check if it's an old plain text password
            elseif ($password === $db_password) {
                // Password is correct (legacy), now UPGRADE IT
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password = :h WHERE id = :id";
                $update_stmt = oci_parse($conn, $update_sql);
                oci_bind_by_name($update_stmt, ":h", $new_hash);
                oci_bind_by_name($update_stmt, ":id", $user_id);
                oci_execute($update_stmt);
                oci_commit($conn);

                $_SESSION['user_id'] = $row['ID']; 
                $_SESSION['username'] = $row['USERNAME'];
                $_SESSION['role'] = $row['ROLE']; 
                header("Location: index.php"); 
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Login</h2>
    
    <?php if($error): ?>
        <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="post" novalidate>
        <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        Don't have an account? <a href="register.php" style="color:var(--primary-color)">Create Account</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
