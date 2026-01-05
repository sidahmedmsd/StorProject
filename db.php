<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session Persistence (30 days)
$lifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $lifetime);
session_set_cookie_params($lifetime);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Theme Priority: Session (Account) > Cookie (Guest) > Default (Light)
$theme_class = 'light-mode';
if (isset($_SESSION['theme'])) {
    $theme_class = $_SESSION['theme'];
} elseif (isset($_COOKIE['theme'])) {
    $theme_class = $_COOKIE['theme'];
}

$conn = oci_connect("projet", "projet", "localhost/XEPDB1");

if (!$conn) {
    $e = oci_error();
    die("فشل الاتصال بقاعدة البيانات: " . $e['message']);
}
