<?php
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
                
                // Load Theme Preference from DB
                $user_theme = $row['THEME'] ?? 'light-mode';
                $_SESSION['theme'] = $user_theme;
                
                // Reset cookie to match account preference
                setcookie('theme', $user_theme, time() + (30 * 24 * 60 * 60), "/");
                
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
                
                // Load Theme Preference from DB
                $user_theme = $row['THEME'] ?? 'light-mode';
                $_SESSION['theme'] = $user_theme;
                
                // Reset cookie
                setcookie('theme', $user_theme, time() + (30 * 24 * 60 * 60), "/");
                
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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="login-body <?php echo $theme_class; ?>">

<div class="login-wrapper">
    <!-- Image Section -->
    <div class="login-image-section">
        <div class="login-image-overlay"></div>
        <img src="photo_2026-01-07_20-21-55.jpg" alt="Login Visual">
    </div>

    <!-- Form Section -->
    <div class="login-form-section">
        <div class="login-form-container">
            <h2>Welcome Back</h2>
            <span class="login-subtitle">Please sign in to continue to your account</span>

            <?php if($error): ?>
                <div class="error-message shake" style="color: #e74c3c; background: #fceae9; padding: 10px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #e74c3c;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" novalidate>
                <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Sign In</button>
            </form>

            <div class="login-footer">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
