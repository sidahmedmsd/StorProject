<?php
include 'db.php';

// Check permissions
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: index.php");
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Convert for Oracle
// We'll wrap date strings in TO_DATE logic in query
$sd = date('d-M-y', strtotime($start_date));
$ed = date('d-M-y', strtotime($end_date));

// 1. New Users Count
$sql_users = "SELECT COUNT(*) as CNT FROM users WHERE TRUNC(created_at) BETWEEN TO_DATE(:sd, 'DD-MON-RR') AND TO_DATE(:ed, 'DD-MON-RR')";
$stmt_users = oci_parse($conn, $sql_users);
oci_bind_by_name($stmt_users, ":sd", $sd);
oci_bind_by_name($stmt_users, ":ed", $ed);
oci_execute($stmt_users);
$user_count = oci_fetch_assoc($stmt_users)['CNT'];

// 2. New Products Count
$sql_products = "SELECT COUNT(*) as CNT FROM products WHERE TRUNC(created_at) BETWEEN TO_DATE(:sd, 'DD-MON-RR') AND TO_DATE(:ed, 'DD-MON-RR')";
$stmt_products = oci_parse($conn, $sql_products);
oci_bind_by_name($stmt_products, ":sd", $sd);
oci_bind_by_name($stmt_products, ":ed", $ed);
oci_execute($stmt_products);
$product_count = oci_fetch_assoc($stmt_products)['CNT'];

// 3. Visits Sum
$sql_visits = "SELECT SUM(visit_count) as CNT FROM daily_visits WHERE visit_date BETWEEN TO_DATE(:sd, 'DD-MON-RR') AND TO_DATE(:ed, 'DD-MON-RR')";
$stmt_visits = oci_parse($conn, $sql_visits);
oci_bind_by_name($stmt_visits, ":sd", $sd);
oci_bind_by_name($stmt_visits, ":ed", $ed);
oci_execute($stmt_visits);
$visit_row = oci_fetch_assoc($stmt_visits);
$visit_count = $visit_row['CNT'] ? $visit_row['CNT'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
        }
        .stats-table th, .stats-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
            color: var(--text-color);
        }
        .stats-table th {
            background-color: var(--primary-color);
            color: white;
        }
        .filter-form {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        @media print {
            @page { margin: 0; }
            body { margin: 1.6cm; }
            .no-print { display: none; }
            body { background: white; color: black; }
            .card, .container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body class="<?php echo $theme_class; ?>">

<div class="container" style="max-width: 900px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>📊 Site Statistics</h2>
        <div class="no-print" style="display:flex; gap:10px; align-items:center;">
            <a href="admin.php" class="btn-logout" style="background:#555; margin:0; display:inline-flex; align-items:center; justify-content:center; height:40px; padding: 0 20px; white-space: nowrap;">Back to Admin</a>
            <button onclick="window.print()" class="btn-add" style="background:#8e44ad; margin:0; display:inline-flex; align-items:center; justify-content:center; height:40px; padding: 0 20px; white-space: nowrap;">🖨️ PDF / Print</button>
        </div>
    </div>

    <form method="get" class="filter-form no-print">
        <div>
            <label style="display:block; margin-bottom:5px;">Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px;">End Date:</label>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
        </div>
        <div>
            <button type="submit" class="btn-edit" style="margin:0;">Filter</button>
        </div>
    </form>

    <div class="card">
        <h3>Report Period: <?php echo date('d M Y', strtotime($start_date)) . " to " . date('d M Y', strtotime($end_date)); ?></h3>
        
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>🆕 New Users Registered</td>
                    <td><?php echo $user_count; ?></td>
                </tr>
                <tr>
                    <td>📦 New Products Added</td>
                    <td><?php echo $product_count; ?></td>
                </tr>
                <tr>
                    <td>👁️ Total Site Visits</td>
                    <td><?php echo $visit_count; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
