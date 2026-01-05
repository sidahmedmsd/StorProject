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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

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
            <span class="user-welcome">Hi, <?php echo htmlspecialchars($username); ?></span>
            <a href="index.php" class="nav-link">Home</a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="admin.php" class="nav-link" title="Dashboard">Dashboard</a>
            <?php endif; ?>
            <a href="add_product.php" class="btn-add">+ Add</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </nav>
    </div>
</header>

<div class="container" style="max-width: 1200px;">
    <h2 style="text-align:center;">Products Published by You</h2>

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
                echo '<span style="color:green; font-weight:bold; display:block; margin-bottom:5px;">✅ Published</span>';
            } else {
                echo '<span style="color:orange; font-weight:bold; display:block; margin-bottom:5px;">⏳ Pending Approval</span>';
            }

            echo '<p style="margin: 10px 0; flex-grow: 1;">' . htmlspecialchars($row['DESCRIPTION']) . '</p>';
            echo '<strong>' . htmlspecialchars($row['PRICE']) . ' DA</strong>';
            
            // Delete Button
            echo '<div style="display:flex; justify-content:center; margin-top:10px;">';
            echo '<a href="edit_product.php?id=' . $row['ID'] . '" class="btn-edit">✏️ Edit</a>';
            echo '<a href="delete_product.php?id=' . $row['ID'] . '" onclick="return confirm(\'Are you sure you want to delete this product?\')" class="btn-logout">🗑️ Delete</a>';
            echo '</div>';
            
            echo '</div>';
        }

        if (!$has_products) {
            echo '<p style="text-align:center; width:100%;">You haven\'t published any products yet.</p>';
        }
        ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
