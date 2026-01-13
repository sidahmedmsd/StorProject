<?php
include 'db.php';

// Check if admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: index.php");
    exit();
}

// Approval
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $sql = "UPDATE products SET approved = 1 WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}



$sql = "SELECT * FROM products WHERE approved = 0";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme_class; ?>">

<div class="container" style="max-width: 800px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Products Pending Approval</h2>
        <div>
            <a href="admin_users.php" class="btn-edit" style="margin-right:10px;">👥 Users</a>
            <?php if ( $_SESSION['role'] === 'superadmin'): ?>
            <a href="admin_stats.php" class="btn-edit" style="margin-right:10px;">📊 Statistics</a>
            <?php endif; ?>
            <a href="index.php" class="btn-logout" style="background:#555;">Back to Store</a>
        </div>
    </div>

    <div class="products-list">
        <?php
        $count = 0;
        while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_LOBS)) {
            $count++;
            echo '<div class="product" style="flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<div>';
            if (!empty($row['IMAGE']) && file_exists($row['IMAGE'])) {
                echo '<img src="' . htmlspecialchars($row['IMAGE']) . '" alt="Product Image" style="width:50px; height:50px; border-radius:4px; margin-left:10px; vertical-align:middle;">';
            }
            echo '<h3>' . htmlspecialchars($row['TITLE']) . '</h3>';
            echo '<p>' . htmlspecialchars($row['DESCRIPTION']) . ' - <strong>' . htmlspecialchars($row['PRICE']) . ' DA</strong></p>';
            echo '</div>';
            echo '<div style="display:flex; gap:10px; align-items:center;">'; 
            echo '<a href="admin.php?approve=' . $row['ID'] . '" style="background:#2ecc71; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;">Approve</a>';
            echo '<a href="admin.php?delete=' . $row['ID'] . '" style="background:#e74c3c; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;">Reject</a>';
            echo '</div>';
            echo '</div>';
        }
        
        if ($count == 0) {
            echo '<p style="text-align:center;">No pending products.</p>';
        }
        ?>
    </div>


</div>

</body>
</html>
