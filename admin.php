<?php
include 'db.php';

// Check if admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: index.php");
    exit();
}

// Handle Approval
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $sql = "UPDATE products SET approved = 1 WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}

// Handle Delete/Reject
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}

// Handle Make Admin (Admin and Super Admin)
if (isset($_GET['make_admin']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
    $user_id = $_GET['make_admin'];
    $sql = "UPDATE users SET role = 'admin' WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $user_id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}

// Handle Remove Admin (Super Admin only)
if (isset($_GET['remove_admin']) && $_SESSION['role'] === 'superadmin') {
    $user_id = $_GET['remove_admin'];
    $sql = "UPDATE users SET role = 'user' WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $user_id);
    oci_execute($stmt);
    header("Location: admin.php");
    exit();
}

// Fetch Pending Products
$sql = "SELECT * FROM products WHERE approved = 0";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

// Fetch All Users
$sql_users = "SELECT * FROM users ORDER BY id DESC";
$stmt_users = oci_parse($conn, $sql_users);
oci_execute($stmt_users);
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
        <a href="index.php">Back to Store</a>
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
            echo '<a href="product_details.php?id=' . $row['ID'] . '" target="_blank" style="color:#3498db; text-decoration:none; font-weight:bold;">View</a>';
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

    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin'): ?>
    <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">

    <h2>Users Management</h2>
    <div class="users-list">
        <?php
        while ($user = oci_fetch_array($stmt_users, OCI_ASSOC)) {
            echo '<div class="product" style="flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<div>';
            echo '<h3>' . htmlspecialchars($user['USERNAME']) . '</h3>';
            echo '<p>' . htmlspecialchars($user['EMAIL']) . ' - Role: <strong>' . htmlspecialchars($user['ROLE']) . '</strong></p>';
            echo '</div>';
            echo '<div style="display:flex; gap:10px; align-items:center;">';
            if ($user['ROLE'] === 'user') {
                echo '<a href="admin.php?make_admin=' . $user['ID'] . '" style="background:#3498db; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;" onclick="return confirm(\'Are you sure you want to make this user an admin?\')">Make Admin</a>';
            } elseif ($user['ROLE'] === 'admin') {
                if ($_SESSION['role'] === 'superadmin') {
                    echo '<a href="admin.php?remove_admin=' . $user['ID'] . '" style="background:#f39c12; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;" onclick="return confirm(\'Are you sure you want to remove admin privileges from this user?\')">Remove Admin</a>';
                } else {
                    echo '<span style="color:#2ecc71; font-weight:bold;">Admin</span>';
                }
            } elseif ($user['ROLE'] === 'superadmin') {
                echo '<span style="color:#9b59b6; font-weight:bold;">Super Admin</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
