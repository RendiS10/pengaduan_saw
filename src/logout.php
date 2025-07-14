<?php
session_start();
session_destroy();
// Tambahkan SweetAlert2 sebelum redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Logout berhasil!',
        text: 'Anda telah keluar dari sistem.',
        confirmButtonText: 'OK',
        background: 'rgba(255,255,255,0.95)',
        backdrop: 'rgba(102,126,234,0.2)'
    }).then(function(){
        window.location.href = '../index.php';
    });
</script>
</body>
</html>
<?php
exit;
?> 