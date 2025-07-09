<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
// Redirect absolut agar tidak ambigu
header('Location: /pengaduan/sipetruk/index.php');
exit;
