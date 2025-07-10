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
        body { background: #f8fafc; }
        .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
        .ranking-badge { 
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .priority-high { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
        .priority-medium { background: linear-gradient(45deg, #feca57, #ff9ff3); }
        .priority-low { background: linear-gradient(45deg, #48dbfb, #0abde3); }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-diajukan { background: #ffeaa7; color: #d63031; }
        .status-diproses { background: #74b9ff; color: #0984e3; }
        .status-selesai { background: #55a3ff; color: #00b894; }
        .table-responsive { border-radius: 10px; overflow: hidden; }
        .btn-action { transition: all 0.3s; }
        .btn-action:hover { transform: scale(1.05); }
    </style>
</head>
<body class="d-flex">
    <?php include('sidebar_bidang.php'); ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1"><i class="fa-solid fa-list-check text-primary"></i> Update Status Pengaduan</h1>
                <p class="text-secondary">Kelola pengaduan berdasarkan prioritas SAW (Simple Additive Weighting)</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Cetak
                </button>
                <button class="btn btn-success" onclick="recalculateSAW()">
                    <i class="fa-solid fa-calculator"></i> Hitung Ulang SAW
                </button>
            </div>
        </div>

        <!-- Info SAW -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-info-circle me-2"></i>Informasi Sistem SAW</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-weight-hanging text-primary"></i> Bobot Kriteria:</h6>
                        <ul class="list-unstyled">
                            <li>• Tingkat Urgensi (C1): 30%</li>
                            <li>• Potensi Dampak (C2): 25%</li>
                            <li>• Jenis Pengaduan (C3): 20%</li>
                            <li>• Tingkat Kompleksitas (C4): 15%</li>
                            <li>• Lama Laporan (C5): 10%</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa-solid fa-sort-numeric-up text-success"></i> Ranking Prioritas:</h6>
                        <ul class="list-unstyled">
                            <li>• <span class="ranking-badge">1</span> = Prioritas Tertinggi</li>
                            <li>• <span class="ranking-badge">2-3</span> = Prioritas Menengah</li>
                            <li>• <span class="ranking-badge">4-5</span> = Prioritas Rendah</li>
                        </ul>
                        <h6 class="mt-3"><i class="fa-solid fa-clock text-warning"></i> Lama Laporan:</h6>
                        <ul class="list-unstyled">
                            <li>• < 1 hari = Nilai 1</li>
                            <li>• 1-2 hari = Nilai 2</li>
                            <li>• 3-4 hari = Nilai 3</li>
                            <li>• 5-6 hari = Nilai 4</li>
                            <li>• ≥ 7 hari = Nilai 5</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pengaduan -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Daftar Pengaduan (Diurutkan berdasarkan SAW)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">Rank</th>
                                <th width="15%">Pengadu</th>
                                <th width="20%">Jenis Pengaduan</th>
                                <th width="15%">Alamat Diadukan</th>
                                <th width="10%">Nilai SAW</th>
                                <th width="10%">Status</th>
                                <th width="10%">Tanggal</th>
                                <th width="15%">Aksi</th>
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
                            <tr class="<?php echo $priority_class; ?>">
                                <td class="text-center">
                                    <span class="ranking-badge"><?php echo $pengaduan['ranking_saw']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($pengaduan['alamat_pengadu']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($pengaduan['nama_alternatif']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($pengaduan['alternatif']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($pengaduan['alamat_diadukan']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo number_format($pengaduan['nilai_saw'], 4); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $pengaduan['status']; ?>">
                                        <?php echo ucfirst($pengaduan['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                onclick="viewDetail(<?php echo $pengaduan['id_pengaduan']; ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action" 
                                                onclick="updateStatus(<?php echo $pengaduan['id_pengaduan']; ?>, '<?php echo $pengaduan['status']; ?>')">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <a href="cetak_aduan.php?id=<?php echo $pengaduan['id_pengaduan']; ?>" 
                                           class="btn btn-sm btn-outline-info btn-action" target="_blank">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
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
                            <label class="form-label">Status Baru:</label>
                            <select class="form-select" name="status" required>
                                <option value="diajukan">Diajukan</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Update Status
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
    </script>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>