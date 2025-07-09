<?php
// Koneksi ke database MySQL
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sipetruk_saw';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}
?>
