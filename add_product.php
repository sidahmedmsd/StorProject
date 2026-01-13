<?php
include 'db.php';


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
        $message = '<div style="color:red">Please fill in all fields</div>';
    } else {
       
        $approved = 0;
        if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
            $approved = 1;
        }

       
        $sql = "INSERT INTO products (user_id, title, description, price, approved, image)
                VALUES (:userid, :title, :descr, :price, :appr, 'temp') RETURNING id INTO :prod_id";
        
        $stmt = oci_parse($conn, $sql);
        
        $new_product_id = 0;
        
        oci_bind_by_name($stmt, ":userid", $user_id);
        oci_bind_by_name($stmt, ":title", $title);
        oci_bind_by_name($stmt, ":descr", $desc);
        oci_bind_by_name($stmt, ":price", $price);
        oci_bind_by_name($stmt, ":appr", $approved);
        oci_bind_by_name($stmt, ":prod_id", $new_product_id, 32);

        if (oci_execute($stmt)) {
            
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $firstImage = ''; 
            
            if (isset($_FILES['images'])) {
                $fileCount = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $fileName = time() . '_' . $i . '_' . basename($_FILES['images']['name'][$i]);
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                           
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
            
            /
            if ($firstImage != '') {
                $sql_update = "UPDATE products SET image = :img WHERE id = :pid";
                $stmt_up = oci_parse($conn, $sql_update);
                oci_bind_by_name($stmt_up, ":img", $firstImage);
                oci_bind_by_name($stmt_up, ":pid", $new_product_id);
                oci_execute($stmt_up);
            }

            if ($approved == 1) {
                $message = '<div style="color:green">Product added successfully!</div>';
            } else {
                $message = '<div style="color:green">Product submitted for review successfully!</div>';
            }
        } else {
            $e = oci_error($stmt);
            $message = '<div style="color:red">Error: ' . $e['message'] . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme_class; ?>">

<div class="container">
    <h2>Add Product</h2>
    
    <?php if($message) echo $message; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data" novalidate>
        <input type="text" name="title" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description" required></textarea>
        <input type="number" name="price" placeholder="Price" required>
        
        <label style="display:block; margin:10px 0;">Product Images (You can select multiple):</label>
        <input type="file" name="images[]" accept="image/*" multiple style="background: white; padding: 5px;">

        <button type="submit">Submit for Review</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        <a href="index.php" style="color:var(--primary-color)">Back to Products</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
