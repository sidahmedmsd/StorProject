<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch Product Details + Seller Phone
$sql = "SELECT p.*, u.phone, u.username as seller_name 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = :pid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":pid", $product_id);
oci_execute($stmt);
$product = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_LOBS);

if (!$product) {
    die("Product not found.");
}

// Security: If not approved, only admin or owner can see it
if ($product['APPROVED'] == 0) {
    $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
    $is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['USER_ID']);
    
    if (!$is_admin && !$is_owner) {
        die("This product is pending approval.");
    }
}

// Fetch Product Images
// Fetch Product Images
// Use @ to supress error if table doesn't exist yet
$sql_imgs = "SELECT * FROM product_images WHERE product_id = :pid";
$stmt_imgs = oci_parse($conn, $sql_imgs);
oci_bind_by_name($stmt_imgs, ":pid", $product_id);
$images_found = @oci_execute($stmt_imgs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['TITLE']); ?> - MSD Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .details-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .gallery {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }
        .main-image {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .thumbnails {
            display: flex;
            gap: 5px;
            justify-content: center;
            overflow-x: auto;
        }
        .thumbnails img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            cursor: pointer;
            border-radius: 4px;
            border: 2px solid transparent;
        }
        .thumbnails img:hover {
            border-color: var(--primary-color);
        }
        .info {
            flex: 1;
            min-width: 300px;
        }
        .price-tag {
            font-size: 1.5em;
            color: #27ae60;
            font-weight: bold;
            margin: 10px 0;
        }
        .seller-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>

<header style="background: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; color: var(--primary-color);"><a href="index.php" style="text-decoration:none; color:inherit;">MSD Store</a></h1>
        <nav>
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="index.php" class="nav-link">Home</a>
                <a href="my_products.php" class="nav-link">My Products</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="container">
    <div class="details-container">
        <!-- Image Gallery -->
        <div class="gallery">
            <?php 
                $mainImg = !empty($product['IMAGE']) ? $product['IMAGE'] : 'placeholder.jpg';
                // Collect other images
                $images = [];
                if (!empty($product['IMAGE'])) $images[] = $product['IMAGE'];
                
                if ($images_found) {
                    while ($imgRow = oci_fetch_array($stmt_imgs, OCI_ASSOC + OCI_RETURN_LOBS)) {
                        $images[] = $imgRow['IMAGE_PATH'];
                    }
                }
                $images = array_unique($images);
            ?>
            <img id="mainImg" src="<?php echo htmlspecialchars($mainImg); ?>" class="main-image">
            
            <div class="thumbnails">
                <?php foreach($images as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" onclick="document.getElementById('mainImg').src=this.src">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div class="info">
            <h2><?php echo htmlspecialchars($product['TITLE']); ?></h2>
            <div class="price-tag"><?php echo htmlspecialchars($product['PRICE']); ?> DA</div>
            
            <p style="line-height: 1.6; color: #555;">
                <?php echo nl2br(htmlspecialchars($product['DESCRIPTION'])); ?>
            </p>

            <div class="seller-info">
                <h4>Seller Info:</h4>
                <p>👤 <strong><?php echo htmlspecialchars($product['SELLER_NAME']); ?></strong></p>
                <?php if (!empty($product['PHONE'])): ?>
                    <p>📞 <strong><?php echo htmlspecialchars($product['PHONE']); ?></strong></p>
                <?php else: ?>
                    <p>🚫 No phone number available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
