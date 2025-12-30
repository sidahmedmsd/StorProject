<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "الرجاء إدخال اسم المستخدم وكلمة المرور";
    } else {
        $sql = "SELECT * FROM users WHERE username = :u AND password = :p";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":u", $username);
        oci_bind_by_name($stmt, ":p", $password);
        oci_execute($stmt);

        if ($row = oci_fetch_assoc($stmt)) {
            $_SESSION['user_id'] = $row['ID']; 
            $_SESSION['username'] = $row['USERNAME'];
            $_SESSION['role'] = $row['ROLE']; 
            header("Location: index.php"); 
            exit();
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>تسجيل الدخول</h2>
    
    <?php if($error): ?>
        <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="post" novalidate>
        <input type="text" name="username" placeholder="اسم المستخدم" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit">دخول</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        ليس لديك حساب؟ <a href="register.php" style="color:var(--primary-color)">أنشئ حساباً</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
