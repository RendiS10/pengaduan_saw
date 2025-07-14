<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

$saw = new SAWCalculator($conn);

// Proses update status
if (isset($_POST['update_status'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $status_baru = $_POST['status'];
    
    $query = "UPDATE pengaduan SET status = '$status_baru' WHERE id_pengaduan = $id_pengaduan";
    if (mysqli_query($conn, $query)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Status pengaduan berhasil diupdate!",timer:1500,showConfirmButton:false});});</script>';
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Gagal update status pengaduan!",timer:1500,showConfirmButton:false});});</script>';
    }
}

// Ambil daftar pengaduan dengan ranking SAW
$pengaduan_list = $saw->getPengaduanWithRanking();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Pengaduan - Sistem SAW</title>
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
        .ranking-badge { 
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .priority-high { 
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            animation: glow 2s ease-in-out infinite alternate;
        }
        .priority-medium { 
            background: linear-gradient(45deg, #feca57, #ff9ff3);
            animation: glow 3s ease-in-out infinite alternate;
        }
        .priority-low { 
            background: linear-gradient(45deg, #48dbfb, #0abde3);
            animation: glow 4s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(255,107,107,0.3); }
            to { box-shadow: 0 0 20px rgba(255,107,107,0.6); }
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .status-badge:hover {
            transform: scale(1.1);
        }
        .status-diajukan { background: #ffeaa7; color: #d63031; }
        .status-diproses { background: #74b9ff; color: #0984e3; }
        .status-selesai { background: #55a3ff; color: #00b894; }
        .table-responsive { 
            border-radius: 15px; 
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
        .btn-action { 
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        .btn-action:hover { 
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-group .btn {
            margin: 0 2px;
        }
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-card h6 {
            color: #feca57;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-card ul li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header-section h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        .header-section p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .action-buttons .btn {
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .action-buttons .btn:hover {
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
        .form-select, .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-select:focus, .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            border-radius: 0 20px 20px 0;
        }
    </style>
</head>
<body class="d-flex">
    <?php include('sidebar_bidang.php'); ?>
    <div class="flex-grow-1 p-3" style="margin-left:250px;">
        <div class="main-container p-4">
            <!-- Header Section -->
            <div class="header-section">
                <div class="d-flex justify-content-between align-items-center">
            <div>
                        <h1><i class="fa-solid fa-list-check me-2"></i> Update Status Pengaduan</h1>
                        <p><i class="fa-solid fa-chart-line me-2"></i> Kelola pengaduan berdasarkan prioritas SAW (Simple Additive Weighting)</p>
            </div>
                    <div class="action-buttons d-flex gap-2">
                        <!-- Hapus tombol cetak -->
                <button class="btn btn-success" onclick="recalculateSAW()">
                            <i class="fa-solid fa-calculator me-1"></i> Hitung Ulang SAW
                </button>
            </div>
        </div>
            </div>

            <!-- Info SAW -->
            <div class="info-card">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-weight-hanging me-2"></i> Bobot Kriteria:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fa-solid fa-circle me-1"></i> Tingkat Urgensi (C1): 30%</li>
                            <li><i class="fa-solid fa-circle me-1"></i> Potensi Dampak (C2): 25%</li>
                            <li><i class="fa-solid fa-circle me-1"></i> Jenis Pengaduan (C3): 20%</li>
                            <li><i class="fa-solid fa-circle me-1"></i> Tingkat Kompleksitas (C4): 15%</li>
                            <li><i class="fa-solid fa-circle me-1"></i> Lama Laporan (C5): 10%</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-sort-numeric-up me-2"></i> Ranking Prioritas:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fa-solid fa-fire me-1"></i> <span class="ranking-badge">1</span> = Prioritas Tertinggi</li>
                            <li><i class="fa-solid fa-exclamation-triangle me-1"></i> <span class="ranking-badge">2-3</span> = Prioritas Menengah</li>
                            <li><i class="fa-solid fa-info-circle me-1"></i> <span class="ranking-badge">4-5</span> = Prioritas Rendah</li>
                        </ul>
                        <h6 class="mt-3"><i class="fa-solid fa-clock me-2"></i> Lama Laporan:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fa-solid fa-calendar-day me-1"></i> < 1 hari = Nilai 1</li>
                            <li><i class="fa-solid fa-calendar-week me-1"></i> 1-2 hari = Nilai 2</li>
                            <li><i class="fa-solid fa-calendar-alt me-1"></i> 3-4 hari = Nilai 3</li>
                            <li><i class="fa-solid fa-calendar-check me-1"></i> 5-6 hari = Nilai 4</li>
                            <li><i class="fa-solid fa-calendar-plus me-1"></i> ≥ 7 hari = Nilai 5</li>
                        </ul>
                </div>
            </div>
        </div>

        <!-- Tabel Pengaduan -->
        <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Daftar Pengaduan (Diurutkan berdasarkan SAW)</h5>
                    <div>
                        <select id="filterStatus" class="form-select form-select-sm" style="width: 180px; display: inline-block;">
                            <option value="">Semua Status</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="diproses">Diproses</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tabelPengaduan">
                            <thead>
                                <tr>
                                    <th width="5%"><i class="fa-solid fa-trophy me-1"></i>Rank</th>
                                    <th width="15%"><i class="fa-solid fa-user me-1"></i>Pengadu</th>
                                    <th width="20%"><i class="fa-solid fa-file-alt me-1"></i>Jenis Pengaduan</th>
                                    <th width="15%"><i class="fa-solid fa-map-marker-alt me-1"></i>Alamat Diadukan</th>
                                    <th width="10%"><i class="fa-solid fa-chart-bar me-1"></i>Nilai SAW</th>
                                    <th width="10%"><i class="fa-solid fa-tasks me-1"></i>Status</th>
                                    <th width="10%"><i class="fa-solid fa-calendar me-1"></i>Tanggal</th>
                                    <th width="15%"><i class="fa-solid fa-cogs me-1"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($pengaduan = mysqli_fetch_assoc($pengaduan_list)): 
                                $priority_class = '';
                                if ($pengaduan['ranking_saw'] <= 1) $priority_class = 'priority-high';
                                elseif ($pengaduan['ranking_saw'] <= 3) $priority_class = 'priority-medium';
                                else $priority_class = 'priority-low';
                            ?>
                                <tr class="<?php echo $priority_class; ?>" data-status="<?php echo $pengaduan['status']; ?>">
                                <td class="text-center">
                                    <span class="ranking-badge"><?php echo $pengaduan['ranking_saw']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></strong><br>
                                        <small class="text-muted"><i class="fa-solid fa-map-pin me-1"></i><?php echo htmlspecialchars($pengaduan['alamat_pengadu']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($pengaduan['nama_alternatif']); ?></strong><br>
                                        <small class="text-muted"><i class="fa-solid fa-tag me-1"></i><?php echo htmlspecialchars($pengaduan['alternatif']); ?></small>
                                </td>
                                    <td><i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($pengaduan['alamat_diadukan']); ?></td>
                                <td>
                                        <span class="badge bg-info"><i class="fa-solid fa-chart-line me-1"></i><?php echo number_format($pengaduan['nilai_saw'], 4); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $pengaduan['status']; ?>">
                                            <i class="fa-solid fa-<?php echo $pengaduan['status'] == 'selesai' ? 'check-circle' : ($pengaduan['status'] == 'diproses' ? 'spinner' : ($pengaduan['status'] == 'ditolak' ? 'times-circle' : 'clock')); ?> me-1"></i>
                                        <?php echo ucfirst($pengaduan['status']); ?>
                                    </span>
                                </td>
                                <td>
                                        <i class="fa-solid fa-calendar-day me-1"></i><?php echo date('d/m/Y H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                    onclick="viewDetail(<?php echo $pengaduan['id_pengaduan']; ?>)"
                                                    title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action" 
                                                    onclick="updateStatus(<?php echo $pengaduan['id_pengaduan']; ?>, '<?php echo $pengaduan['status']; ?>')"
                                                    title="Update Status">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                            <!-- Tombol cetak dihapus -->
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Status -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-edit me-2"></i>Update Status Pengaduan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pengaduan" id="modal_id_pengaduan">
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-tasks me-2"></i>Status Baru:</label>
                            <select class="form-select" name="status" required>
                                <option value="diajukan"><i class="fa-solid fa-clock"></i> Diajukan</option>
                                <option value="diproses"><i class="fa-solid fa-spinner"></i> Diproses</option>
                                <option value="ditolak"><i class="fa-solid fa-times-circle"></i> Ditolak</option>
                                <option value="selesai"><i class="fa-solid fa-times-circle"></i> Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" name="update_status" class="btn btn-success">
                            <i class="fa-solid fa-save me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(id, currentStatus) {
            document.getElementById('modal_id_pengaduan').value = id;
            document.querySelector('select[name="status"]').value = currentStatus;
            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
        }

        function viewDetail(id) {
            // Load detail pengaduan via AJAX
            fetch(`get_detail_pengaduan.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                });
        }

        function recalculateSAW() {
            if (confirm('Apakah Anda yakin ingin menghitung ulang ranking SAW?')) {
                fetch('recalculate_saw.php', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Ranking SAW berhasil dihitung ulang',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal menghitung ulang ranking SAW'
                            });
                        }
                    });
            }
        }

        // Tambahkan animasi loading untuk tombol
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-action');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Filter status
            const filterStatus = document.getElementById('filterStatus');
            const rows = document.querySelectorAll('#tabelPengaduan tbody tr');
            filterStatus.addEventListener('change', function() {
                const val = this.value;
                rows.forEach(row => {
                    if (!val || row.getAttribute('data-status') === val) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>