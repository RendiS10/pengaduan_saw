<?php
include_once __DIR__ . '/../config/koneksi.php';
$username = 'admin';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$hash' WHERE username='$username'";
if (mysqli_query($conn, $sql)) {
  echo "Password user 'rendi' berhasil direset ke 'bidang123' (hash bcrypt).";
} else {
  echo "Gagal reset password: ".mysqli_error($conn);
}
?>
