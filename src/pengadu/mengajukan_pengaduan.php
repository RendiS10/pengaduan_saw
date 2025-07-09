<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';

// Proses form pengaduan
if (isset($_POST['ajukan'])) {
    $user_id = $_SESSION['user_id'];
    $nama_pengadu = mysqli_real_escape_string($conn, $_POST['nama_pengadu']);
    $alamat_pengadu = mysqli_real_escape_string($conn, $_POST['alamat_pengadu']);
    $alamat_diadukan = mysqli_real_escape_string($conn, $_POST['alamat_diadukan']);
    $alternatif = mysqli_real_escape_string($conn, $_POST['alternatif']);
    
    // Proses upload file
    $bukti = $_FILES['bukti_pengaduan'];
    $upload_dir = '../../public/image/';
    $file_ext = strtolower(pathinfo($bukti['name'], PATHINFO_EXTENSION));
    $file_name = 'bukti_' . time() . '_' . rand(1000,9999) . '.' . $file_ext;
    $target_file = $upload_dir . $file_name;
    $upload_ok = true;
    $allowed_ext = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($file_ext, $allowed_ext)) {
        $upload_ok = false;
        $error_msg = 'Format file tidak didukung!';
    } elseif ($bukti['size'] > 2*1024*1024) {
        $upload_ok = false;
        $error_msg = 'Ukuran file maksimal 2MB!';
    }
    if ($upload_ok && move_uploaded_file($bukti['tmp_name'], $target_file)) {
        $bukti_path = 'public/image/' . $file_name;
        $sql = "INSERT INTO pengaduan (user_id, nama_pengadu, alamat_pengadu, alamat_diadukan, alternatif, bukti_pengaduan) VALUES ('$user_id', '$nama_pengadu', '$alamat_pengadu', '$alamat_diadukan', '$alternatif', '$bukti_path')";
        if (mysqli_query($conn, $sql)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil!",text:"Pengaduan berhasil diajukan.",timer:1800,showConfirmButton:false}).then(()=>{window.location.href="dashboard_pengadu.php";});});</script>';
            exit;
        } else {
            echo '<div class="alert alert-danger mt-3">Gagal menyimpan pengaduan ke database.</div>';
        }
    } else {
        echo '<div class="alert alert-danger mt-3">Gagal upload file. ' . ($error_msg ?? '') . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajukan Pengaduan</title>
  <?php include_once(__DIR__ . '/../template/cdn_head.php'); ?>
</head>
<body class="d-flex">
  <?php include('sidebar_pengadu.php'); ?>
  <div class="flex-grow-1 p-4">
    <h2>Ajukan Pengaduan</h2>
    <div class="card p-4" style="max-width: 700px; margin: 0 auto;">
      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nama Pengadu:</label>
          <input type="text" class="form-control" name="nama_pengadu" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Alamat Pengadu:</label>
          <input type="text" class="form-control" name="alamat_pengadu" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Alamat yang Diadukan:</label>
          <input type="text" class="form-control" name="alamat_diadukan" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Pilih Jenis Pengaduan</label>
          <select class="form-select" name="alternatif" required>
            <option value="A1">Longsor di Area Pemakaman</option>
            <option value="A2">Saluran Drainase Tersumbat</option>
            <option value="A3">Aduan Mengenai Bangunan Tak Berizin di Kawasan Padat</option>
            <option value="A4">Tumpukan sampah liar di lahan kosong</option>
            <option value="A5">Aduan IRK, PBG, KRK, IKTR Mengenai Administrasi Pemberkasan</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Bukti Pengaduan (Upload Foto):</label>
          <input type="file" class="form-control" name="bukti_pengaduan" accept="image/*" required>
        </div>
        <!-- ...form pengaduan lain di sini, bisa tambahkan field lain sesuai kebutuhan... -->
        <button type="submit" class="btn btn-primary w-100" name="ajukan"><i class="fa-solid fa-paper-plane"></i> Kirim Pengaduan</button>
      </form>
    </div>
  </div>
  <?php include_once(__DIR__ . '/../template/cdn_footer.php'); ?>
</body>
</html>