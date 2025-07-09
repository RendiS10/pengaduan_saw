<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
$user_id = $_SESSION['user_id'];
// Ambil riwayat pengaduan user
$query = "SELECT * FROM pengaduan WHERE user_id='$user_id' ORDER BY tanggal_pengaduan DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status Pengaduan</title>
  <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
</head>
<body class="d-flex">
  <?php include('sidebar_pengadu.php'); ?>
  <div class="flex-grow-1 p-4">
    <h2>Status Pengaduan</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>No</th>
            <th>Jenis Pengaduan</th>
            <th>Bukti</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          // Format tanggal ke WIB
          $tanggal = $row['tanggal_pengaduan'];
          // Asumsikan waktu di database sudah dalam waktu server (Waktu Windows/Laragon biasanya Asia/Jakarta)
          // Jika ternyata waktu di database sudah WIB, cukup format saja tanpa konversi zona
          $dt = new DateTime($tanggal);
          $tanggal_wib = $dt->format('d-m-Y H:i:s') . ' WIB';
          echo '<tr>';
          echo '<td>' . $no++ . '</td>';
          echo '<td>' . htmlspecialchars($row['alternatif']) . '</td>';
          echo '<td>';
          if ($row['bukti_pengaduan']) {
            echo '<a href="../../' . htmlspecialchars($row['bukti_pengaduan']) . '" target="_blank">Lihat</a>';
          } else {
            echo '-';
          }
          echo '</td>';
          echo '<td>' . htmlspecialchars($row['status']) . '</td>';
          echo '<td>' . $tanggal_wib . '</td>';
          echo '<td class="d-flex align-items-center gap-1">';
          if ($row['status'] == 'diajukan') {
            echo '<button type="button" class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal'.$row['id_pengaduan'].'">Detail / Edit</button>';
            echo '<form method="post" style="display:inline; margin:0; padding:0;" onsubmit="event.stopPropagation(); return confirm(\'Yakin ingin menghapus pengaduan ini?\');">';
            echo '<input type="hidden" name="hapus_id_pengaduan" value="' . $row['id_pengaduan'] . '">';
            echo '<button type="submit" class="btn btn-danger btn-sm">Hapus</button>';
            echo '</form>';
          } else {
            echo '-';
          }
          echo '</td>';
          echo '</tr>';
          // Modal Edit
          if ($row['status'] == 'diajukan') {
            ?>
            <div class="modal fade" id="editModal<?php echo $row['id_pengaduan']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['id_pengaduan']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_pengaduan" value="<?php echo $row['id_pengaduan']; ?>">
                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title" id="editModalLabel<?php echo $row['id_pengaduan']; ?>">Edit Pengaduan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Nama Pengadu</label>
                        <input type="text" class="form-control" name="edit_nama_pengadu" value="<?php echo htmlspecialchars($row['nama_pengadu']); ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Alamat Pengadu</label>
                        <input type="text" class="form-control" name="edit_alamat_pengadu" value="<?php echo htmlspecialchars($row['alamat_pengadu']); ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Alamat yang Diadukan</label>
                        <input type="text" class="form-control" name="edit_alamat_diadukan" value="<?php echo htmlspecialchars($row['alamat_diadukan']); ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Jenis Pengaduan</label>
                        <select class="form-select" name="edit_alternatif" required>
                          <option value="A1" <?php if($row['alternatif']=='A1') echo 'selected'; ?>>Longsor di Area Pemakaman</option>
                          <option value="A2" <?php if($row['alternatif']=='A2') echo 'selected'; ?>>Saluran Drainase Tersumbat</option>
                          <option value="A3" <?php if($row['alternatif']=='A3') echo 'selected'; ?>>Aduan Mengenai Bangunan Tak Berizin di Kawasan Padat</option>
                          <option value="A4" <?php if($row['alternatif']=='A4') echo 'selected'; ?>>Tumpukan sampah liar di lahan kosong</option>
                          <option value="A5" <?php if($row['alternatif']=='A5') echo 'selected'; ?>>Aduan IRK, PBG, KRK, IKTR Mengenai Administrasi Pemberkasan</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Bukti Pengaduan (Upload Foto Baru jika ingin ganti)</label>
                        <input type="file" class="form-control" name="edit_bukti_pengaduan" accept="image/*">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary" name="update_pengaduan">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php
          }
        }
        ?>
        </tbody>
      </table>
    </div>
    <?php
    // Proses update pengaduan
    if (isset($_POST['update_pengaduan'])) {
      $id_pengaduan = intval($_POST['id_pengaduan']);
      $edit_nama_pengadu = mysqli_real_escape_string($conn, $_POST['edit_nama_pengadu']);
      $edit_alamat_pengadu = mysqli_real_escape_string($conn, $_POST['edit_alamat_pengadu']);
      $edit_alamat_diadukan = mysqli_real_escape_string($conn, $_POST['edit_alamat_diadukan']);
      $edit_alternatif = mysqli_real_escape_string($conn, $_POST['edit_alternatif']);
      $update_bukti = '';
      if (!empty($_FILES['edit_bukti_pengaduan']['name'])) {
        $bukti = $_FILES['edit_bukti_pengaduan'];
        $upload_dir = '../../public/image/';
        $file_ext = strtolower(pathinfo($bukti['name'], PATHINFO_EXTENSION));
        $file_name = 'bukti_' . time() . '_' . rand(1000,9999) . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        $allowed_ext = ['jpg','jpeg','png','gif','webp'];
        if (in_array($file_ext, $allowed_ext) && $bukti['size'] <= 2*1024*1024 && move_uploaded_file($bukti['tmp_name'], $target_file)) {
          $update_bukti = ", bukti_pengaduan='public/image/$file_name'";
        }
      }
      $sql_update = "UPDATE pengaduan SET nama_pengadu='$edit_nama_pengadu', alamat_pengadu='$edit_alamat_pengadu', alamat_diadukan='$edit_alamat_diadukan', alternatif='$edit_alternatif' $update_bukti WHERE id_pengaduan='$id_pengaduan' AND user_id='$user_id' AND status='diajukan'";
      if (mysqli_query($conn, $sql_update)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil!",text:"Pengaduan berhasil diupdate.",timer:1800,showConfirmButton:false}).then(()=>{window.location.href=window.location.pathname;});});</script>';
        exit;
      } else {
        echo '<div class="alert alert-danger mt-3">Gagal update pengaduan.</div>';
      }
    }
    // Proses hapus pengaduan
    if (isset($_POST['hapus_id_pengaduan'])) {
      $hapus_id = intval($_POST['hapus_id_pengaduan']);
      // Hapus file bukti jika ada
      $q = mysqli_query($conn, "SELECT bukti_pengaduan FROM pengaduan WHERE id_pengaduan='$hapus_id' AND user_id='$user_id' AND status='diajukan'");
      $d = mysqli_fetch_assoc($q);
      if ($d && !empty($d['bukti_pengaduan']) && file_exists('../../'.$d['bukti_pengaduan'])) {
        unlink('../../'.$d['bukti_pengaduan']);
      }
      $del = mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan='$hapus_id' AND user_id='$user_id' AND status='diajukan'");
      if ($del) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11}"></script>';
        echo '<script>window.onload = function(){Swal.fire({icon:"success",title:"Berhasil!",text:"Pengaduan berhasil dihapus.",timer:1500,showConfirmButton:false}).then(()=>{window.location.href=window.location.pathname;});}</script>';
        exit;
      } else {
        echo '<div class="alert alert-danger mt-3">Gagal menghapus pengaduan.</div>';
      }
    }
    ?>
  </div>
  <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
  <script>
// Fix: agar form hapus tidak reload sebelum swal tampil
const hapusForms = document.querySelectorAll('form[method="post"]');
hapusForms.forEach(form => {
  form.addEventListener('submit', function(e) {
    if (form.querySelector('input[name="hapus_id_pengaduan"]')) {
      e.preventDefault();
      Swal.fire({
        title: 'Yakin ingin menghapus pengaduan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    }
  });
});
</script>
</body>
</html>
