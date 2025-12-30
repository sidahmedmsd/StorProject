<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = oci_connect("system", "sidosido", "localhost/XE");

if (!$conn) {
    $e = oci_error();
    die("فشل الاتصال بقاعدة البيانات: " . $e['message']);
}
?>
