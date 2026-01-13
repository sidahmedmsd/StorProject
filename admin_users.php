<?php
include 'db.php';

// Check if admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: index.php");
    exit();
}

// Handle Make Admin (Admin and Super Admin)
if (isset($_GET['make_admin']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
    $user_id = $_GET['make_admin'];
    $promoter_id = $_SESSION['user_id'];
    $sql = "UPDATE users SET role = 'admin', promoted_by = :promoter WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":promoter", $promoter_id);
    oci_bind_by_name($stmt, ":id", $user_id);
    oci_execute($stmt);
    header("Location: admin_users.php");
    exit();
}

// Handle Remove Admin (Super Admin only)
if (isset($_GET['remove_admin']) && $_SESSION['role'] === 'superadmin') {
    $user_id = $_GET['remove_admin'];
    $sql = "UPDATE users SET role = 'user', promoted_by = NULL WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $user_id);
    oci_execute($stmt);
    header("Location: admin_users.php");
    exit();
}


$search_user = "";
$query_cond = "";
if (isset($_GET['search_user']) && !empty(trim($_GET['search_user']))) {
    $search_user = trim($_GET['search_user']);
    $query_cond = " WHERE (UPPER(u.username) LIKE UPPER(:search) OR UPPER(u.email) LIKE UPPER(:search))";
}

$sql_users = "SELECT u.*, p.username as promoter_name 
              FROM users u 
              LEFT JOIN users p ON u.promoted_by = p.id 
              $query_cond 
              ORDER BY u.id DESC";
$stmt_users = oci_parse($conn, $sql_users);

if ($search_user) {
    $s_term = "%" . $search_user . "%";
    oci_bind_by_name($stmt_users, ":search", $s_term);
}

oci_execute($stmt_users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - MSD Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme_class; ?>">

<div class="container" style="max-width: 800px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Users Management</h2>
        <div>
            <a href="admin.php" class="btn-logout" style="background:#555;">Back to Admin</a>
        </div>
    </div>
    
    <!-- Super Admin Search -->
    <?php if ($_SESSION['role'] === 'superadmin'): ?>
    <form method="get" action="admin_users.php" style="margin-bottom: 20px;">
        <input type="text" name="search_user" placeholder="Search users by name or email..." value="<?php echo htmlspecialchars($search_user); ?>">
    </form>
    <?php endif; ?>

    <div class="users-list">
        <?php
        while ($user = oci_fetch_array($stmt_users, OCI_ASSOC)) {
            echo '<div class="product" style="flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<div>';
            echo '<h3>' . htmlspecialchars($user['USERNAME']) . '</h3>';
            echo '<p>' . htmlspecialchars($user['EMAIL']) . ' - Role: <strong>' . htmlspecialchars($user['ROLE']) . '</strong>';
            
            
            if ($_SESSION['role'] === 'superadmin' && $user['ROLE'] === 'admin' && !empty($user['PROMOTER_NAME'])) {
                echo '<br><span style="font-size:0.9em; color:#e67e22;">Promoted by: ' . htmlspecialchars($user['PROMOTER_NAME']) . '</span>';
            }
            
            echo '</p>';
            echo '</div>';
            echo '<div style="display:flex; gap:10px; align-items:center;">';
            if ($user['ROLE'] === 'user') {
                echo '<a href="admin_users.php?make_admin=' . $user['ID'] . '" style="background:#3498db; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;" onclick="return confirm(\'Are you sure you want to make this user an admin?\')">Make Admin</a>';
            } elseif ($user['ROLE'] === 'admin') {
                if ($_SESSION['role'] === 'superadmin') {
                    echo '<a href="admin_users.php?remove_admin=' . $user['ID'] . '" style="background:#f39c12; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;" onclick="return confirm(\'Are you sure you want to remove admin privileges from this user?\')">Remove Admin</a>';
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
</div>

</body>
</html>
