<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$product_id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if (!$product_id) {
    header("Location: my_products.php");
    exit;
}

// Fetch existing product data
// Verify ownership (or admin)
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql = "SELECT * FROM products WHERE id = :pid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":pid", $product_id);
oci_execute($stmt);
$product = oci_fetch_assoc($stmt);

if (!$product) {
    die("Product not found.");
}

// Check permission
if ($role != 'admin' && $product['USER_ID'] != $user_id) {
    die("Unauthorized access.");
}

// Handle POST Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $price = $_POST['price'];
    
    // Image Handling
    $imagePath = $product['IMAGE']; // Default to existing
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $sql_update = "UPDATE products SET title = :t, description = :d, price = :p, image = :img WHERE id = :pid";
    $stmt_update = oci_parse($conn, $sql_update);
    oci_bind_by_name($stmt_update, ":t", $title);
    oci_bind_by_name($stmt_update, ":d", $desc);
    oci_bind_by_name($stmt_update, ":p", $price);
    oci_bind_by_name($stmt_update, ":img", $imagePath);
    oci_bind_by_name($stmt_update, ":pid", $product_id);

    if (oci_execute($stmt_update)) {
        $message = '<div style="color:green">Product updated successfully!</div>';
        // Refresh data
        $product['TITLE'] = $title;
        $product['DESCRIPTION'] = $desc;
        $product['PRICE'] = $price;
        $product['IMAGE'] = $imagePath;
    } else {
        $e = oci_error($stmt_update);
        $message = '<div style="color:red">Update Error: ' . $e['message'] . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Edit Product</h2>
    
    <?php if($message) echo $message; ?>

    <form action="edit_product.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['ID']); ?>">
        
        <label>Product Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($product['TITLE']); ?>" required>
        
        <label>Description:</label>
        <textarea name="description" required><?php echo htmlspecialchars($product['DESCRIPTION']); ?></textarea>
        
        <label>Price (DA):</label>
        <input type="number" name="price" value="<?php echo htmlspecialchars($product['PRICE']); ?>" required>
        
        <label style="display:block; margin:10px 0;">Current Image:</label>
        <?php if($product['IMAGE']): ?>
            <img src="<?php echo htmlspecialchars($product['IMAGE']); ?>" style="max-width:100px; display:block; margin-bottom:10px;">
        <?php endif; ?>
        
        <label style="display:block; margin:10px 0;">Change Image (Optional):</label>
        <input type="file" name="image" accept="image/*" style="background: white; padding: 5px;">

        <button type="submit">Save Changes</button>
    </form>
    <p style="text-align:center; margin-top:15px;">
        <a href="my_products.php" style="color:var(--primary-color)">Back to My Products</a>
    </p>
</div>

</body>
</html>
