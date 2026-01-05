<?php
session_start();
include 'db.php';

// Fetch products with user info
$search = "";
if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $sql = "SELECT p.*, u.phone, u.username as seller_name 
            FROM products p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.approved = 1 
            AND (UPPER(p.title) LIKE UPPER(:search_term) OR UPPER(p.description) LIKE UPPER(:search_term))";
    $stmt = oci_parse($conn, $sql);
    $search_term = "%" . $search . "%";
    oci_bind_by_name($stmt, ":search_term", $search_term);
} else {
    $sql = "SELECT p.*, u.phone, u.username as seller_name FROM products p JOIN users u ON p.user_id = u.id WHERE p.approved = 1";
    $stmt = oci_parse($conn, $sql);
}

oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <div class="header-logo">
            <h1><a href="index.php" style="text-decoration:none; color:inherit;">MSD Store</a></h1>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="settings.php" class="nav-link" title="Settings" style="font-size: 1.2rem;">⚙️</a>
            <?php endif; ?>
        </div>
        
        <div class="header-search">
            <form action="index.php" method="get">
                <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : ''); ?>">
            </form>
        </div>

        <nav class="header-nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="user-welcome">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" class="nav-link" title="Dashboard">Dashboard</a>
                <?php endif; ?>
                
                <a href="my_products.php" class="nav-link">My Products</a>
                <a href="add_product.php" class="btn-add">+ Product</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="btn-add">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<h2 style="text-align:center;">Available Products</h2>



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
        echo '<p style="font-size: 0.9em; color: #777;">Seller: ' . htmlspecialchars($row['SELLER_NAME']) . '</p>';
        echo '<p style="margin: 10px 0; flex-grow: 1;">' . htmlspecialchars($row['DESCRIPTION']) . '</p>';
        echo '<strong>' . htmlspecialchars($row['PRICE']) . ' DA</strong>';
        
        echo '<a href="product_details.php?id=' . $row['ID'] . '" class="btn-add" style="display:block; text-align:center; margin-top:10px; background:#2c3e50;">📄 Details</a>';



        // Admin Delete Button
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
            echo '<a href="delete_product.php?id=' . $row['ID'] . '" onclick="return confirm(\'Admin: Are you sure you want to delete this product?\')" class="btn-logout" style="display:block; text-align:center; margin-top:5px; background:red;">🗑️ Delete (Admin)</a>';
        }
        
        echo '</div>';
    }
    ?>
</div>

<script src="script.js"></script>
</body>
</html>
