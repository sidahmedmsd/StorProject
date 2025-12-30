<?php
session_start();
include 'db.php';

// Fetch products with user info
$sql = "SELECT p.*, u.phone, u.username as seller_name FROM products p JOIN users u ON p.user_id = u.id WHERE p.approved = 1";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المتجر - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header style="background: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; color: var(--primary-color);">MSD Store</h1>
        <nav>
            <?php if (isset($_SESSION['username'])): ?>
                <span>مرحباً, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" class="nav-link">لوحة التحكم</a>
                <?php endif; ?>
                <a href="my_products.php" class="nav-link">منشوراتي</a>
                <a href="add_product.php" class="btn-add">أضف منتج</a>
                <a href="logout.php" class="btn-logout">خروج</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">دخول</a>
                <a href="register.php" class="nav-link">تسجيل</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<h2 style="text-align:center;">المنتوجات المتاحة</h2>

<div class="products-wrapper">
    <?php
    while ($row = oci_fetch_assoc($stmt)) {
        echo '<div class="product">';
        if (!empty($row['IMAGE']) && file_exists($row['IMAGE'])) {
            echo '<a href="product_details.php?id=' . $row['ID'] . '">';
            echo '<img src="' . htmlspecialchars($row['IMAGE']) . '" alt="Product Image">';
            echo '</a>';
        }
        echo '<h3><a href="product_details.php?id=' . $row['ID'] . '" style="text-decoration:none; color:inherit;">' . htmlspecialchars($row['TITLE']) . '</a></h3>';
        echo '<p style="font-size: 0.9em; color: #777;">البائع: ' . htmlspecialchars($row['SELLER_NAME']) . '</p>';
        echo '<p style="margin: 10px 0; flex-grow: 1;">' . htmlspecialchars($row['DESCRIPTION']) . '</p>';
        echo '<strong>' . htmlspecialchars($row['PRICE']) . ' DA</strong>';
        
        echo '<a href="product_details.php?id=' . $row['ID'] . '" class="btn-add" style="display:block; text-align:center; margin-top:10px; background:#2c3e50;">📄 تفاصيل</a>';

        if (!empty($row['PHONE'])) {
            echo '<a href="tel:' . htmlspecialchars($row['PHONE']) . '" class="btn-add" style="display:block; text-align:center; margin-top:10px; background:#3498db;">📞 اتصل: ' . htmlspecialchars($row['PHONE']) . '</a>';
        }

        // Admin Delete Button
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
            echo '<a href="delete_product.php?id=' . $row['ID'] . '" onclick="return confirm(\'أدمن: هل أنت متأكد من حذف هذا المنتج؟\')" class="btn-logout" style="display:block; text-align:center; margin-top:5px; background:red;">🗑️ حذف (Admin)</a>';
        }
        
        echo '</div>';
    }
    ?>
</div>

<script src="script.js"></script>
</body>
</html>
