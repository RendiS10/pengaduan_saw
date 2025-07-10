<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

// Proses form pengaduan
if (isset($_POST['ajukan'])) {
    $user_id = $_SESSION['user_id'];
    $nama_pengadu = mysqli_real_escape_string($conn, $_POST['nama_pengadu']);
    $alamat_pengadu = mysqli_real_escape_string($conn, $_POST['alamat_pengadu']);
    $alamat_diadukan = mysqli_real_escape_string($conn, $_POST['alamat_diadukan']);
    $alternatif = mysqli_real_escape_string($conn, $_POST['alternatif']);
    
    // Ambil nilai kriteria berdasarkan alternatif yang dipilih
    $query = "SELECT * FROM nilai_alternatif WHERE alternatif = '$alternatif'";
    $result = mysqli_query($conn, $query);
    $nilai_alt = mysqli_fetch_assoc($result);
    
    // Mapping nilai ke string untuk database
    $tingkat_urgensi_map = [
        5 => 'Sangat Mendesak',
        4 => 'Mendesak', 
        3 => 'Sedang',
        2 => 'Tidak Mendesak',
        1 => 'Biasa'
    ];
    
    $potensi_dampak_map = [
        5 => 'Berdampak luas (masyarakat umum)',
        4 => 'Beberapa RT',
        3 => 'Satu RT',
        2 => 'Jalan pribadi',
        1 => 'Individual saja'
    ];
    
    $jenis_pengaduan_map = [
        5 => 'Infrastruktur rusak berat',
        4 => 'Infrastruktur rusak ringan',
        3 => 'Pelayanan administrative',
        2 => 'Non-prioritas',
        1 => 'Tidak relevan'
    ];
    
    $tingkat_kompleksitas_map = [
        5 => 'Kompleks dan melibatkan banyak pihak (lintas bidang)',
        4 => 'Kompleks dan butuh verifikasi tambahan',
        3 => 'Sedang, cukup jelas dan dapat langsung diproses',
        2 => 'Sederhana dengan solusi teknis ringan',
        1 => 'Sangat sederhana, keluhan sepele atau administratif'
    ];
    
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
        
        // Hitung lama laporan (dalam hari)
        $lama_laporan = $nilai_alt['c5_value'];
        
        $sql = "INSERT INTO pengaduan (
            user_id, nama_pengadu, alamat_pengadu, alamat_diadukan, alternatif, bukti_pengaduan,
            tingkat_urgensi, potensi_dampak, jenis_pengaduan, tingkat_kompleksitas, lama_laporan
        ) VALUES (
            '$user_id', '$nama_pengadu', '$alamat_pengadu', '$alamat_diadukan', '$alternatif', '$bukti_path',
            '{$tingkat_urgensi_map[$nilai_alt['c1_value']]}',
            '{$potensi_dampak_map[$nilai_alt['c2_value']]}',
            '{$jenis_pengaduan_map[$nilai_alt['c3_value']]}',
            '{$tingkat_kompleksitas_map[$nilai_alt['c4_value']]}',
            '$lama_laporan'
        )";
        
        if (mysqli_query($conn, $sql)) {
            // Hitung SAW untuk pengaduan yang baru dibuat
            $saw = new SAWCalculator($conn);
            $saw->calculateAllPengaduan();
            
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil!",text:"Pengaduan berhasil diajukan dan telah diproses dengan sistem SAW.",timer:2000,showConfirmButton:false}).then(()=>{window.location.href="dashboard_pengadu.php";});});</script>';
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