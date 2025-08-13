<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

$saw = new SAWCalculator($conn);

// Ambil semua pengaduan dengan ranking SAW
$pengaduan_list = $saw->getPengaduanWithRanking();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan - Sistem SAW</title>
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
        .status-ditolak { background: #ff7675; color: white; }
        .status-disetujui { background: #55a3ff; color: white; }
        .table-responsive { 
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
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            border-radius: 0 20px 20px 0;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .modal-header {
            border-radius: 15px 15px 0 0;
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
                        <h1><i class="fa-solid fa-history me-2"></i> Riwayat Pengaduan</h1>
                        <p><i class="fa-solid fa-chart-line me-2"></i> Lihat semua pengaduan yang telah diproses berdasarkan prioritas SAW</p>
                    </div>
                    <div>
                        <a href="update_status.php" class="btn btn-warning">
                            <i class="fa-solid fa-list-check me-1"></i> Kelola Pengaduan Baru
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <?php
            $stats_query = "SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'diajukan' THEN 1 ELSE 0 END) as diajukan,
                                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
                            FROM pengaduan";
            $stats_result = mysqli_query($conn, $stats_query);
            $stats = mysqli_fetch_assoc($stats_result);
            ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="fa-solid fa-file-alt mb-2" style="font-size: 2rem;"></i>
                        <h4><?= $stats['total']; ?></h4>
                        <p>Total Pengaduan</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);">
                        <i class="fa-solid fa-clock mb-2" style="font-size: 2rem;"></i>
                        <h4><?= $stats['diajukan']; ?></h4>
                        <p>Menunggu Proses</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #55a3ff 0%, #003d82 100%);">
                        <i class="fa-solid fa-check-circle mb-2" style="font-size: 2rem;"></i>
                        <h4><?= $stats['disetujui']; ?></h4>
                        <p>Disetujui</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);">
                        <i class="fa-solid fa-times-circle mb-2" style="font-size: 2rem;"></i>
                        <h4><?= $stats['ditolak']; ?></h4>
                        <p>Ditolak</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Pengaduan -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Semua Pengaduan (Diurutkan berdasarkan SAW)</h5>
                    <div>
                        <select id="filterStatus" class="form-select form-select-sm" style="width: 180px; display: inline-block;">
                            <option value="">Semua Status</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="disetujui">Disetujui</option>
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
                                    <th width="10%"><i class="fa-solid fa-chart-bar me-1"></i>Nilai Preferensi</th>
                                    <th width="10%"><i class="fa-solid fa-tasks me-1"></i>Status</th>
                                    <th width="10%"><i class="fa-solid fa-calendar me-1"></i>Tanggal</th>
                                    <th width="15%"><i class="fa-solid fa-cogs me-1"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $total_pengaduan = mysqli_num_rows($pengaduan_list);
                                if ($total_pengaduan > 0):
                                    while ($pengaduan = mysqli_fetch_assoc($pengaduan_list)): 
                                ?>
                                    <tr data-status="<?php echo $pengaduan['status']; ?>">
                                        <td class="text-center">
                                            <span class="ranking-badge"><?php echo $no++; ?></span>
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
                                                <i class="fa-solid fa-<?php echo $pengaduan['status'] == 'disetujui' ? 'check-circle' : ($pengaduan['status'] == 'ditolak' ? 'times-circle' : 'clock'); ?> me-1"></i>
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
                                                <?php if ($pengaduan['status'] == 'diajukan'): ?>
                                                <a href="update_status.php" class="btn btn-sm btn-outline-success btn-action" 
                                                   title="Proses Pengaduan">
                                                    <i class="fa-solid fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fa-solid fa-file-alt text-muted mb-3" style="font-size: 3rem;"></i>
                                            <h5 class="text-muted">Belum Ada Pengaduan</h5>
                                            <p class="text-muted mb-0">Data pengaduan akan muncul di sini setelah ada yang diajukan.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
        function viewDetail(id) {
            // Load detail pengaduan via AJAX
            fetch(`get_detail_pengaduan.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                });
        }

        // Filter status
        document.addEventListener('DOMContentLoaded', function() {
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
