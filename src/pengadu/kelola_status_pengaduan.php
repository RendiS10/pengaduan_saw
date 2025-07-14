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
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Pengaduan berhasil dihapus!",timer:1500,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"});});</script>';
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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Status Pengaduan</title>
  <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
  <style>
      body { 
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          min-height: 100vh;
      }
      .main-container {
          background: rgba(255, 255, 255, 0.95);
          border-radius: 20px;
          box-shadow: 0 15px 35px rgba(0,0,0,0.1);
          backdrop-filter: blur(10px);
          margin: 20px;
          padding: 30px;
      }
      .card { 
          box-shadow: 0 8px 25px rgba(0,0,0,0.08); 
          border: none; 
          border-radius: 15px;
          transition: all 0.3s ease;
      }
      .card:hover {
          transform: translateY(-5px);
          box-shadow: 0 15px 35px rgba(0,0,0,0.15);
      }
      .status-badge {
          padding: 8px 15px;
          border-radius: 20px;
          font-size: 0.9rem;
          font-weight: bold;
          transition: all 0.3s ease;
      }
      .status-badge:hover {
          transform: scale(1.1);
      }
      .status-diajukan { background: #ffeaa7; color: #d63031; }
      .status-diproses { background: #74b9ff; color: #0984e3; }
      .status-selesai  { background: #55a3ff; color: #00b894; }
      .status-ditolak  { background: #fab1a0; color: #c0392b; }
      .btn-action { 
          transition: all 0.3s ease;
          border-radius: 8px;
          padding: 6px 10px;
      }
      .btn-action:hover { 
          transform: scale(1.1) rotate(5deg);
          box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      }
      .welcome-section {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: white;
          border-radius: 15px;
          padding: 25px;
          margin-bottom: 25px;
          animation: slideIn 0.8s ease-out;
      }
      @keyframes slideIn {
          from {
              opacity: 0;
              transform: translateX(-30px);
          }
          to {
              opacity: 1;
              transform: translateX(0);
          }
      }
      .welcome-section h1 {
          font-size: 1.8rem;
          margin-bottom: 5px;
          font-weight: 700;
      }
      .welcome-section p {
          font-size: 1rem;
          opacity: 0.9;
          margin-bottom: 0;
      }
      .sidebar {
          background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
          border-radius: 0 20px 20px 0;
      }
      .btn {
          border-radius: 10px;
          transition: all 0.3s ease;
      }
      .btn:hover {
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      }
      .modal-content {
          border-radius: 15px;
          border: none;
          box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      }
      .modal-header {
          border-radius: 15px 15px 0 0;
      }
      .table {
          margin-bottom: 0;
          font-size: 0.9rem;
      }
      .table th {
          background: linear-gradient(45deg, #2c3e50, #34495e);
          color: white;
          font-size: 0.85rem;
          padding: 12px 8px;
          border: none;
      }
      .table td {
          padding: 10px 8px;
          vertical-align: middle;
      }
      .table-responsive {
          border-radius: 15px;
          overflow: hidden;
          box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      }
      .empty-state {
          background: rgba(255, 255, 255, 0.9);
          border-radius: 15px;
          padding: 40px;
          text-align: center;
          animation: fadeIn 1s ease-out;
      }
      @keyframes fadeIn {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
      }
      .empty-state i {
          color: #667eea;
          margin-bottom: 20px;
      }
  </style>
</head>
<body class="d-flex">
<?php include('sidebar_pengadu.php'); ?>

<div class="flex-grow-1 p-3">
    <div class="main-container">
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
        <div>
                    <h1><i class="fa-solid fa-clipboard-list me-2"></i>Status Pengaduan Saya</h1>
                    <p><i class="fa-solid fa-info-circle me-2"></i>Kelola dan pantau status pengaduan Anda</p>
        </div>
                <a href="mengajukan_pengaduan.php" class="btn btn-light">
                    <i class="fa-solid fa-plus me-1"></i>Ajukan Pengaduan Baru
        </a>
            </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Daftar Pengaduan Saya</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                            <thead>
                            <tr>
                                <th><i class="fa-solid fa-hashtag me-1"></i>#</th>
                                <th><i class="fa-solid fa-file-alt me-1"></i>Jenis Pengaduan</th>
                                <th><i class="fa-solid fa-map-marker-alt me-1"></i>Lokasi</th>
                                <th><i class="fa-solid fa-calendar me-1"></i>Tanggal</th>
                                <th><i class="fa-solid fa-tasks me-1"></i>Status</th>
                                <th><i class="fa-solid fa-image me-1"></i>Bukti</th>
                                <th><i class="fa-solid fa-cogs me-1"></i>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $no=1; while ($pengaduan = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($pengaduan['nama_alternatif']); ?></strong></td>
                                <td><small class="text-muted"><i class="fa-solid fa-map-marker-alt me-1"></i> <?= htmlspecialchars($pengaduan['alamat_diadukan']); ?></small></td>
                                <td><small class="text-muted"><i class="fa-solid fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?></small></td>
                                    <td>
                                        <span class="status-badge status-<?= $pengaduan['status']; ?>">
                                            <i class="fa-solid fa-<?= $pengaduan['status'] == 'selesai' ? 'check-circle' : ($pengaduan['status'] == 'diproses' ? 'spinner' : ($pengaduan['status'] == 'ditolak' ? 'times-circle' : 'clock')); ?> me-1"></i>
                                            <?= ucfirst($pengaduan['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pengaduan['bukti_pengaduan']): ?>
                                            <img src="../../<?= $pengaduan['bukti_pengaduan']; ?>" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 60px; width: 80px; object-fit: cover;" 
                                                 alt="Bukti Pengaduan"
                                                 title="Klik untuk melihat">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa-solid fa-image"></i> Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                    onclick="viewDetail(<?= $pengaduan['id_pengaduan']; ?>)"
                                                    title="Lihat Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <?php if ($pengaduan['status'] === 'ditolak'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                        onclick="hapusPengaduan(<?= $pengaduan['id_pengaduan']; ?>)"
                                                        title="Hapus Pengaduan">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            <?php elseif ($pengaduan['status'] !== 'diproses'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning btn-action" 
                                                        onclick="editPengaduan(<?= $pengaduan['id_pengaduan']; ?>)"
                                                        title="Edit Pengaduan">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                        onclick="hapusPengaduan(<?= $pengaduan['id_pengaduan']; ?>)"
                                                        title="Hapus Pengaduan">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                        <?php endif; ?>
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
            <div class="empty-state">
                <i class="fa-solid fa-inbox fa-3x"></i>
            <h4 class="text-muted">Belum ada pengaduan</h4>
            <p class="text-muted">Anda belum mengajukan pengaduan apapun.</p>
            <a href="mengajukan_pengaduan.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i>Ajukan Pengaduan Pertama
            </a>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-info-circle me-2"></i>Detail Pengaduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent"></div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="editContent"></div>
    </div>
</div>

<!-- Form Hapus -->
<form method="post" id="hapusForm" style="display: none;">
    <input type="hidden" name="id_pengaduan" id="hapus_id_pengaduan">
    <input type="hidden" name="hapus_pengaduan" value="1">
</form>

<script>
function viewDetail(id) {
    fetch(`get_detail_pengaduan_user.php?id=${id}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('detailContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        });
}

function editPengaduan(id) {
    fetch(`edit_pengaduan_modal.php?id=${id}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('editContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
}

function hapusPengaduan(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus pengaduan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: 'rgba(255, 255, 255, 0.95)',
        backdrop: 'rgba(231, 76, 60, 0.3)'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('hapus_id_pengaduan').value = id;
            document.getElementById('hapusForm').submit();
        }
    });
}

document.getElementById('detailModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('detailContent').innerHTML = '';
});

document.getElementById('editModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('editContent').innerHTML = '';
});
</script>

<?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>
