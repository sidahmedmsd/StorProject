<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch USER'S products (approved or not, they should see them)
// Join with users to be consistent, though we know the user
$sql = "SELECT p.*, u.phone, u.username as seller_name 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.user_id = :userid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":userid", $user_id);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "Query Error: " . $e['message'];
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منشوراتي - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header style="background: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; color: var(--primary-color);">MSD Store - منشوراتي</h1>
        <nav>
            <span>مرحباً, <?php echo htmlspecialchars($username); ?></span>
            <a href="index.php" class="nav-link">الرئيسية</a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="admin.php" class="nav-link">لوحة التحكم</a>
            <?php endif; ?>
            <a href="add_product.php" class="btn-add">أضف منتج</a>
            <a href="logout.php" class="btn-logout">خروج</a>
        </nav>
    </div>
</header>

<div class="container" style="max-width: 1200px;">
    <h2 style="text-align:center;">الـمـنـتـوجـات الـتـي نـشـرتـهـا</h2>

    <div class="products-wrapper">
        <?php
        $has_products = false;
        while ($row = oci_fetch_assoc($stmt)) {
            $has_products = true;
            echo '<div class="product">';
            
            if (!empty($row['IMAGE']) && file_exists($row['IMAGE'])) {
                echo '<img src="' . htmlspecialchars($row['IMAGE']) . '" alt="Product Image">';
            }
            
            echo '<h3>' . htmlspecialchars($row['TITLE']) . '</h3>';
            
            // Status badge
            if ($row['APPROVED'] == 1) {
                echo '<span style="color:green; font-weight:bold; display:block; margin-bottom:5px;">✅ منشور</span>';
            } else {
                echo '<span style="color:orange; font-weight:bold; display:block; margin-bottom:5px;">⏳ بانتظار الموافقة</span>';
            }

            echo '<p style="margin: 10px 0; flex-grow: 1;">' . htmlspecialchars($row['DESCRIPTION']) . '</p>';
            echo '<strong>' . htmlspecialchars($row['PRICE']) . ' DA</strong>';
            
            // Delete Button
            echo '<div style="display:flex; justify-content:center; margin-top:10px;">';
            echo '<a href="edit_product.php?id=' . $row['ID'] . '" class="btn-edit">✏️ تعديل</a>';
            echo '<a href="delete_product.php?id=' . $row['ID'] . '" onclick="return confirm(\'هل أنت متأكد من حذف هذا المنتج؟\')" class="btn-logout">🗑️ حذف</a>';
            echo '</div>';
            
            echo '</div>';
        }

        if (!$has_products) {
            echo '<p style="text-align:center; width:100%;">لم تقم بنشر أي منتج بعد.</p>';
        }
        ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
