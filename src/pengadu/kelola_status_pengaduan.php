<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';

$user_id = $_SESSION['user_id'];

// Proses hapus pengaduan
if (isset($_POST['hapus_pengaduan'])) {
    $id_pengaduan = $_POST['id_pengaduan'];

    $query = "SELECT bukti_pengaduan FROM pengaduan WHERE id_pengaduan = $id_pengaduan AND user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $pengaduan = mysqli_fetch_assoc($result);

    if ($pengaduan) {
        if (file_exists('../../' . $pengaduan['bukti_pengaduan'])) {
            unlink('../../' . $pengaduan['bukti_pengaduan']);
        }

        $delete_query = "DELETE FROM pengaduan WHERE id_pengaduan = $id_pengaduan AND user_id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Pengaduan berhasil dihapus!",timer:1500,showConfirmButton:false});});</script>';
        }
    }
}

// Ambil daftar pengaduan user
$query = "SELECT p.*, na.nama_alternatif 
          FROM pengaduan p 
          LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
          WHERE p.user_id = $user_id 
          ORDER BY p.tanggal_pengaduan DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Status Pengaduan</title>
  <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
  <style>
      body { background: #f8fafc; }
      .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
      .status-badge {
          padding: 8px 15px;
          border-radius: 20px;
          font-size: 0.9rem;
          font-weight: bold;
      }
      .status-diajukan { background: #ffeaa7; color: #d63031; }
      .status-diproses { background: #74b9ff; color: #0984e3; }
      .status-selesai { background: #55a3ff; color: #00b894; }
      .btn-action { transition: all 0.3s; }
      .btn-action:hover { transform: scale(1.05); }
  </style>
</head>
<body class="d-flex">
<?php include('sidebar_pengadu.php'); ?>
<div class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="fa-solid fa-clipboard-list text-primary"></i> Status Pengaduan Saya</h1>
            <p class="text-secondary">Kelola dan pantau status pengaduan Anda</p>
        </div>
        <a href="mengajukan_pengaduan.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Ajukan Pengaduan Baru
        </a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Daftar Pengaduan Saya</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Jenis Pengaduan</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $no=1; while ($pengaduan = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($pengaduan['nama_alternatif']); ?></strong></td>
                                <td><small class="text-muted"><i class="fa-solid fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($pengaduan['alamat_diadukan']); ?></small></td>
                                <td><small class="text-muted"><i class="fa-solid fa-calendar me-1"></i> <?php echo date('d/m/Y H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?></small></td>
                                <td><span class="status-badge status-<?php echo $pengaduan['status']; ?>"> <?php echo ucfirst($pengaduan['status']); ?> </span></td>
                                <td><?php if ($pengaduan['bukti_pengaduan']): ?><img src="../../<?php echo $pengaduan['bukti_pengaduan']; ?>" class="img-fluid rounded" style="max-height: 60px; width: 80px; object-fit: cover;" alt="Bukti Pengaduan"><?php endif; ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" onclick="viewDetail(<?php echo $pengaduan['id_pengaduan']; ?>)"><i class="fa-solid fa-eye"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action" onclick="editPengaduan(<?php echo $pengaduan['id_pengaduan']; ?>)"><i class="fa-solid fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="hapusPengaduan(<?php echo $pengaduan['id_pengaduan']; ?>)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Belum ada pengaduan</h4>
            <p class="text-muted">Anda belum mengajukan pengaduan apapun.</p>
            <a href="mengajukan_pengaduan.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Ajukan Pengaduan Pertama
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Pengaduan -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-info-circle me-2"></i>Detail Pengaduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Konten AJAX di sini -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Pengaduan -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="editContent">
            <!-- Konten AJAX edit di sini -->
        </div>
    </div>
</div>

<!-- Form hapus -->
<form method="post" id="hapusForm" style="display: none;">
    <input type="hidden" name="id_pengaduan" id="hapus_id_pengaduan">
    <input type="hidden" name="hapus_pengaduan" value="1">
</form>

<script>
function viewDetail(id) {
    fetch(`get_detail_pengaduan_user.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            const detailModalEl = document.getElementById('detailModal');
            const modalInstance = bootstrap.Modal.getInstance(detailModalEl) || new bootstrap.Modal(detailModalEl);

            document.getElementById('detailContent').innerHTML = data;
            modalInstance.show();
        });
}

document.getElementById('detailModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('detailContent').innerHTML = '';
});

function editPengaduan(id) {
    fetch(`edit_pengaduan_modal.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            const editModalEl = document.getElementById('editModal');
            const modalInstance = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);

            document.getElementById('editContent').innerHTML = data;
            modalInstance.show();
        });
}

document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editContent').innerHTML = '';
});

function hapusPengaduan(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus pengaduan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('hapus_id_pengaduan').value = id;
            document.getElementById('hapusForm').submit();
        }
    });
}
</script>

<?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>
