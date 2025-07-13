<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika diminta logout via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    exit;
}

// Jika diakses langsung, redirect saja
header('Location: /pengaduan/sipetruk/index.php');
exit;
