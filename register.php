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
        $error = "جميع الحقول مطلوبة";
    } else {
        // Basic insert
        $sql = "INSERT INTO users (username, email, password, role, phone) VALUES (:u, :e, :p, 'user', :ph)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":u", $username);
        oci_bind_by_name($stmt, ":e", $email);
        oci_bind_by_name($stmt, ":p", $password);
        oci_bind_by_name($stmt, ":ph", $phone);

        if (@oci_execute($stmt)) {
            $success = "تم إنشاء الحساب بنجاح! يمكنك الآن <a href='login.php'>تسجيل الدخول</a>";
        } else {
            $e = oci_error($stmt);
            $error = "خطأ في إنشاء الحساب: " . $e['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>إنشاء حساب جديد</h2>
    
    <?php if($error): ?>
        <div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div style="color: green; text-align: center; margin-bottom: 15px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="post" novalidate>
        <input type="text" name="username" placeholder="اسم المستخدم" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="text" name="phone" placeholder="رقم الهاتف" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit">تسجيل</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        لديك حساب بالفعل؟ <a href="login.php" style="color:var(--primary-color)">سجل الدخول</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
