<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id']; 

    if (empty($title) || empty($desc) || empty($price)) {
        $message = '<div style="color:red">يرجى ملء جميع الحقول</div>';
    } else {
        // 1. Insert Product first to get ID
        // Note: We use RETURNING ID INTO :prod_id to get the new ID in Oracle
        $sql = "INSERT INTO products (user_id, title, description, price, approved, image)
                VALUES (:userid, :title, :descr, :price, 0, 'temp') RETURNING id INTO :prod_id";
        
        $stmt = oci_parse($conn, $sql);
        
        $new_product_id = 0;
        
        oci_bind_by_name($stmt, ":userid", $user_id);
        oci_bind_by_name($stmt, ":title", $title);
        oci_bind_by_name($stmt, ":descr", $desc);
        oci_bind_by_name($stmt, ":price", $price);
        oci_bind_by_name($stmt, ":prod_id", $new_product_id, 32);

        if (oci_execute($stmt)) {
            // Product inserted, now handle images
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $firstImage = ''; // Will be set as main image
            
            if (isset($_FILES['images'])) {
                $fileCount = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $fileName = time() . '_' . $i . '_' . basename($_FILES['images']['name'][$i]);
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                            // Insert into product_images
                            $sql_img = "INSERT INTO product_images (product_id, image_path) VALUES (:pid, :path)";
                            $stmt_img = oci_parse($conn, $sql_img);
                            oci_bind_by_name($stmt_img, ":pid", $new_product_id);
                            oci_bind_by_name($stmt_img, ":path", $targetFile);
                            oci_execute($stmt_img);

                            if ($firstImage == '') {
                                $firstImage = $targetFile;
                            }
                        }
                    }
                }
            }
            
            // Update main product image if we have one
            if ($firstImage != '') {
                $sql_update = "UPDATE products SET image = :img WHERE id = :pid";
                $stmt_up = oci_parse($conn, $sql_update);
                oci_bind_by_name($stmt_up, ":img", $firstImage);
                oci_bind_by_name($stmt_up, ":pid", $new_product_id);
                oci_execute($stmt_up);
            }

            $message = '<div style="color:green">تم إرسال المنتوج للمراجعة بنجاح!</div>';
        } else {
            $e = oci_error($stmt);
            $message = '<div style="color:red">خطأ: ' . $e['message'] . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتوج - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>إضافة منتوج</h2>
    
    <?php if($message) echo $message; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data" novalidate>
        <input type="text" name="title" placeholder="اسم المنتوج" required>
        <textarea name="description" placeholder="وصف المنتوج" required></textarea>
        <input type="number" name="price" placeholder="السعر" required>
        
        <label style="display:block; margin:10px 0;">صور المنتوج (يمكنك اختيار أكثر من صورة):</label>
        <input type="file" name="images[]" accept="image/*" multiple style="background: white; padding: 5px;">

        <button type="submit">إرسال للمراجعة</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        <a href="index.php" style="color:var(--primary-color)">العودة للمنتجات</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
