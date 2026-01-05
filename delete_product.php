<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($role == 'admin' || $role == 'superadmin') {
        // Admin can delete any product
        $sql = "DELETE FROM products WHERE id = :pid";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":pid", $product_id);
    } else {
        // Regular user can only delete their own
        $sql = "DELETE FROM products WHERE id = :pid AND user_id = :userid";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":pid", $product_id);
        oci_bind_by_name($stmt, ":userid", $user_id);
    }

    if (oci_execute($stmt)) {
        // Redirect back to where they came form
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: index.php");
        }
    } else {
        $e = oci_error($stmt);
        echo "Error deleting product: " . $e['message'];
    }
} else {
    header("Location: index.php");
}
?>
