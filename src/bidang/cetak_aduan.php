<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

$saw = new SAWCalculator($conn);

// Ambil detail pengaduan jika ada ID
$detail = null;
if (isset($_GET['id'])) {
    $id_pengaduan = $_GET['id'];
    $detail = $saw->getDetailSAW($id_pengaduan);
}

// Ambil semua pengaduan untuk daftar
$pengaduan_list = $saw->getPengaduanWithRanking();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pengaduan - Sistem SAW</title>
    <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { margin: 0; padding: 20px; }
            .card { border: 1px solid #000; box-shadow: none; }
        }
        .print-only { display: none; }
        .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
        .header-print { 
            text-align: center; 
            border-bottom: 2px solid #000; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .table-print { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-print th, .table-print td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
        }
        .table-print th { background: #f0f0f0; }
        .signature-section { 
            margin-top: 50px; 
            display: flex; 
            justify-content: space-between; 
        }
        .signature-box { 
            text-align: center; 
            width: 200px; 
        }
        .signature-line { 
            border-top: 1px solid #000; 
            margin-top: 50px; 
            padding-top: 5px; 
        }
    </style>
</head>
<body class="d-flex">
    <?php if (!isset($_GET['id'])): ?>
    <!-- Sidebar hanya untuk daftar pengaduan -->
    <?php include('sidebar_bidang.php'); ?>
    <?php endif; ?>
    
    <div class="flex-grow-1 p-4">
        <?php if (isset($_GET['id']) && $detail): ?>
            <!-- Cetak Detail Pengaduan -->
            <div class="no-print mb-3">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa-solid fa-print"></i> Cetak
                </button>
                <a href="update_status.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <!-- Header Cetak -->
                    <div class="header-print">
                        <h3><strong>SURAT PENGADUAN</strong></h3>
                        <h4>Sistem Pengaduan Terpadu (SIPETRUK)</h4>
                        <p>Dengan Metode Simple Additive Weighting (SAW)</p>
                        <p>Nomor: <?php echo str_pad($detail['id_pengaduan'], 4, '0', STR_PAD_LEFT); ?>/SIPETRUK/<?php echo date('Y'); ?></p>
                    </div>
                    
                    <!-- Informasi Pengaduan -->
                    <table class="table-print">
                        <tr>
                            <th width="30%">Nama Pengadu</th>
                            <td><?php echo htmlspecialchars($detail['nama_pengadu']); ?></td>
                        </tr>
                        <tr>
                            <th>Alamat Pengadu</th>
                            <td><?php echo htmlspecialchars($detail['alamat_pengadu']); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengaduan</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($detail['tanggal_pengaduan'])); ?></td>
                        </tr>
                        <tr>
                            <th>Lokasi yang Diadukan</th>
                            <td><?php echo htmlspecialchars($detail['alamat_diadukan']); ?></td>
                        </tr>
                        <tr>
                            <th>Jenis Pengaduan</th>
                            <td><?php echo htmlspecialchars($detail['nama_alternatif']); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><strong><?php echo ucfirst($detail['status']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Nilai SAW</th>
                            <td><strong><?php echo number_format($detail['nilai_saw'], 4); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Ranking Prioritas</th>
                            <td><strong><?php echo $detail['ranking_saw']; ?></strong></td>
                        </tr>
                    </table>
                    
                    <!-- Detail Kriteria SAW -->
                    <h5><strong>Detail Perhitungan SAW:</strong></h5>
                    <table class="table-print">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th>Nilai</th>
                                <th>Bobot</th>
                                <th>Atribut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tingkat Urgensi (C1)</td>
                                <td><?php echo $detail['c1_value']; ?></td>
                                <td>30%</td>
                                <td>Benefit</td>
                            </tr>
                            <tr>
                                <td>Potensi Dampak (C2)</td>
                                <td><?php echo $detail['c2_value']; ?></td>
                                <td>25%</td>
                                <td>Benefit</td>
                            </tr>
                            <tr>
                                <td>Jenis Pengaduan (C3)</td>
                                <td><?php echo $detail['c3_value']; ?></td>
                                <td>20%</td>
                                <td>Benefit</td>
                            </tr>
                            <tr>
                                <td>Tingkat Kompleksitas (C4)</td>
                                <td><?php echo $detail['c4_value']; ?></td>
                                <td>15%</td>
                                <td>Cost</td>
                            </tr>
                            <tr>
                                <td>Lama Laporan (C5)</td>
                                <td><?php echo $detail['lama_laporan']; ?> hari</td>
                                <td>10%</td>
                                <td>Benefit</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Bukti Pengaduan -->
                    <?php if ($detail['bukti_pengaduan']): ?>
                    <div class="mt-4">
                        <h5><strong>Bukti Pengaduan:</strong></h5>
                        <img src="../../<?php echo $detail['bukti_pengaduan']; ?>" 
                             class="img-fluid" 
                             style="max-width: 400px; border: 1px solid #000;" 
                             alt="Bukti Pengaduan">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tanda Tangan -->
                    <div class="signature-section">
                        <div class="signature-box">
                            <p>Dicetak pada:</p>
                            <p><?php echo date('d/m/Y H:i'); ?></p>
                            <div class="signature-line"></div>
                            <p>Operator Sistem</p>
                        </div>
                        <div class="signature-box">
                            <p>Jakarta, <?php echo date('d/m/Y'); ?></p>
                            <div class="signature-line"></div>
                            <p>Petugas Bidang</p>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Daftar Pengaduan untuk Dipilih -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1"><i class="fa-solid fa-print text-primary"></i> Cetak Pengaduan</h1>
                    <p class="text-secondary">Pilih pengaduan yang akan dicetak</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Daftar Pengaduan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Rank</th>
                                    <th>Pengadu</th>
                                    <th>Jenis Pengaduan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pengaduan = mysqli_fetch_assoc($pengaduan_list)): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-danger"><?php echo $pengaduan['ranking_saw']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($pengaduan['alamat_pengadu']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pengaduan['nama_alternatif']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $pengaduan['status'] == 'selesai' ? 'success' : ($pengaduan['status'] == 'diproses' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($pengaduan['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($pengaduan['tanggal_pengaduan'])); ?>
                                    </td>
                                    <td>
                                        <a href="?id=<?php echo $pengaduan['id_pengaduan']; ?>" 
                                           class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fa-solid fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
