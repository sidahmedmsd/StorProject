<?php
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $user_id);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);

if (!$user) {
    die("User not found.");
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Profile Update
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        if (empty($username) || empty($email) || empty($phone)) {
            $error = "Username, Email and Phone are required.";
        } else {
            // Check if username is taken by another user
            $check_sql = "SELECT COUNT(*) AS CNT FROM users WHERE username = :u AND id != :id";
            $check_stmt = oci_parse($conn, $check_sql);
            oci_bind_by_name($check_stmt, ":u", $username);
            oci_bind_by_name($check_stmt, ":id", $user_id);
            oci_execute($check_stmt);
            $row = oci_fetch_assoc($check_stmt);

            if ($row['CNT'] > 0) {
                $error = "Username is already taken by another user.";
            } else {
                $sql_update = "UPDATE users SET username = :u, email = :e, phone = :ph WHERE id = :id";
                $stmt_update = oci_parse($conn, $sql_update);
                oci_bind_by_name($stmt_update, ":u", $username);
                oci_bind_by_name($stmt_update, ":e", $email);
                oci_bind_by_name($stmt_update, ":ph", $phone);
                oci_bind_by_name($stmt_update, ":id", $user_id);
                
                if (oci_execute($stmt_update)) {
                    oci_commit($conn);
                    $message = "Profile updated successfully!";
                    // Refresh data
                    $user['USERNAME'] = $username;
                    $user['EMAIL'] = $email;
                    $user['PHONE'] = $phone;
                    $_SESSION['username'] = $username; // Update session
                } else {
                    $e = oci_error($stmt_update);
                    $error = "Error updating profile: " . $e['message'];
                }
            }
        }
    }

    // Password Update
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Verify current password
            // Note: $user['PASSWORD'] is the hash from DB
            $db_password = $user['PASSWORD'];
            $password_valid = false;

            if (password_verify($current_password, $db_password)) {
                $password_valid = true;
            } elseif ($current_password === $db_password) {
                // Legacy plain text check
                $password_valid = true;
            }

            if ($password_valid) {
                 $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                 $sql_pw = "UPDATE users SET password = :p WHERE id = :id";
                 $stmt_pw = oci_parse($conn, $sql_pw);
                 oci_bind_by_name($stmt_pw, ":p", $new_hash);
                 oci_bind_by_name($stmt_pw, ":id", $user_id);
                 
                 if (oci_execute($stmt_pw)) {
                     oci_commit($conn);
                     $message = "Password changed successfully!";
                     // Update current user array hash so subsequent checks work 
                     $user['PASSWORD'] = $new_hash;
                 } else {
                     $e = oci_error($stmt_pw);
                     $error = "Error updating password: " . $e['message'];
                 }
            } else {
                $error = "Current password is incorrect.";
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
    <title>Account Settings - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme_class; ?>">

<header>
    <div class="header-inner">
        <div class="header-logo">
            <h1><a href="index.php" style="text-decoration:none; color:inherit;">MSD Store</a></h1>
            <a href="settings.php" class="nav-link" title="Settings" style="font-size: 1.2rem;">⚙️</a>
        </div>
        
        <div class="header-search">
            <form action="index.php" method="get">
                <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : ''); ?>">
            </form>
        </div>

        <nav class="header-nav">
                <span class="user-welcome">Hi, <?php echo htmlspecialchars($user['USERNAME']); ?></span>
                <a href="index.php" class="nav-link">Home</a>
                <a href="my_products.php" class="nav-link">My Products</a>
                <a href="logout.php" class="btn-logout">Logout</a>
        </nav>
    </div>
</header>

<div class="container" style="max-width: 600px;">
    <h2>Account Settings</h2>

    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px;">
        <span style="margin-right: 10px; font-weight: bold;">Dark Mode</span>
        <label class="switch">
            <input type="checkbox" id="darkModeToggle">
            <span class="slider round"></span>
        </label>
    </div>
    
    <?php if($message): ?>
        <div style="color: #27ae60; text-align: center; margin-bottom: 15px; background: rgba(39, 174, 96, 0.1); padding: 10px; border-radius: 4px; border: 1px solid #27ae60;"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div style="color: var(--error-color); text-align: center; margin-bottom: 15px; background: rgba(231, 76, 60, 0.1); padding: 10px; border-radius: 4px; border: 1px solid var(--error-color);"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Profile Information</h3>
        <form action="settings.php" method="post">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['USERNAME']); ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL'] ?? ''); ?>" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['PHONE'] ?? ''); ?>" required>
            
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>

    <div class="card">
        <h3>Change Password</h3>
        <form action="settings.php" method="post">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
            
            <label>New Password:</label>
            <input type="password" name="new_password" required>
            
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required>
            
            <button type="submit" name="update_password" style="background: #e67e22;">Change Password</button>
        </form>
    </div>

</div>

<script src="script.js"></script>
</body>
</html>
