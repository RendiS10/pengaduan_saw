<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Jika file ini diakses langsung via POST (AJAX), lakukan logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    exit;
}
// Redirect ke proses_logout.php jika diakses via GET
header('Location: proses_logout.php');
exit;
