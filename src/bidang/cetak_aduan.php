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
    // Batasi hanya status 'diproses' yang bisa dicetak
    if ($detail && $detail['status'] !== 'diproses') {
        echo '<script>alert("Hanya pengaduan dengan status DIPROSES yang dapat dicetak!");window.location.href="cetak_aduan.php";</script>';
        exit;
    }
}

// Ambil semua pengaduan untuk daftar
$pengaduan_list = $saw->getPengaduanWithRanking();

// Filter hanya pengaduan dengan status 'diproses'
$filtered_list = [];
while ($pengaduan = mysqli_fetch_assoc($pengaduan_list)) {
    if ($pengaduan['status'] === 'diproses') {
        $filtered_list[] = $pengaduan;
    }
}
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
            body { margin: 0; padding: 20px; background: white !important; }
            .card { border: 1px solid #000; box-shadow: none; background: white !important; }
            .main-container { background: white !important; box-shadow: none !important; }
        }
        .print-only { display: none; }
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
        .header-print { 
            text-align: center; 
            border-bottom: 2px solid #667eea; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
        }
        .table-print { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table-print th, .table-print td { 
            border: 1px solid #e9ecef; 
            padding: 12px; 
            text-align: left; 
        }
        .table-print th { 
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
            font-weight: 600;
        }
        .table-print tr:nth-child(even) {
            background: rgba(102, 126, 234, 0.05);
        }
        .table-print tr:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        .signature-section { 
            margin-top: 50px; 
            display: flex; 
            justify-content: space-between; 
        }
        .signature-box { 
            text-align: center; 
            width: 200px; 
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .signature-line { 
            border-top: 2px solid #667eea; 
            margin-top: 50px; 
            padding-top: 5px; 
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
        .ranking-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .status-diajukan { background: #ffeaa7; color: #d63031; }
        .status-diproses { background: #74b9ff; color: #0984e3; }
        .status-selesai { background: #55a3ff; color: #00b894; }
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
    </style>
</head>
<body class="d-flex">
    <?php if (!isset($_GET['id'])): ?>
    <!-- Sidebar hanya untuk daftar pengaduan -->
    <?php include('sidebar_bidang.php'); ?>
    <?php endif; ?>
    
    <div class="flex-grow-1 p-3" <?php if (!isset($_GET['id'])): ?>style="margin-left:250px;"<?php endif; ?>>
        <div class="main-container">
            <?php if (isset($_GET['id']) && $detail): ?>
                <!-- Cetak Detail Pengaduan -->
                <div class="no-print mb-3">
                    <div class="welcome-section">
                        <h1><i class="fa-solid fa-print me-2"></i>Cetak Pengaduan</h1>
                        <p><i class="fa-solid fa-info-circle me-2"></i>Detail pengaduan dengan perhitungan SAW</p>
                    </div>
                    
                    <div class="d-flex gap-2 mb-3">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fa-solid fa-print me-1"></i>Cetak
                        </button>
                        <a href="update_status.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <!-- Header Cetak -->
                        <div class="header-print">
                            <h3><strong><i class="fa-solid fa-file-contract me-2"></i>SURAT PENGADUAN</strong></h3>
                            <h4>Sistem Pengaduan Terpadu (SIPETRUK)</h4>
                            <p><i class="fa-solid fa-calculator me-1"></i>Dengan Metode Simple Additive Weighting (SAW)</p>
                            <p><i class="fa-solid fa-hashtag me-1"></i>Nomor: <?php echo str_pad($detail['id_pengaduan'], 4, '0', STR_PAD_LEFT); ?>/SIPETRUK/<?php echo date('Y'); ?></p>
                        </div>
                        
                        <!-- Informasi Pengaduan -->
                        <table class="table-print">
                            <tr>
                                <th width="30%"><i class="fa-solid fa-user me-1"></i>Nama Pengadu</th>
                                <td><?php echo htmlspecialchars($detail['nama_pengadu']); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-location-dot me-1"></i>Alamat Pengadu</th>
                                <td><?php echo htmlspecialchars($detail['alamat_pengadu']); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-calendar me-1"></i>Tanggal Pengaduan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($detail['tanggal_pengaduan'])); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-map-marker-alt me-1"></i>Lokasi yang Diadukan</th>
                                <td><?php echo htmlspecialchars($detail['alamat_diadukan']); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-file-alt me-1"></i>Jenis Pengaduan</th>
                                <td><?php echo htmlspecialchars($detail['nama_alternatif']); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-tasks me-1"></i>Status</th>
                                <td><strong><span class="status-badge status-<?php echo $detail['status']; ?>"><?php echo ucfirst($detail['status']); ?></span></strong></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-chart-line me-1"></i>Nilai SAW</th>
                                <td><strong class="ranking-badge"><?php echo number_format($detail['nilai_saw'], 4); ?></strong></td>
                            </tr>
                            <tr>
                                <th><i class="fa-solid fa-trophy me-1"></i>Ranking Prioritas</th>
                                <td><strong class="ranking-badge"><?php echo $detail['ranking_saw']; ?></strong></td>
                            </tr>
                        </table>
                        
                        <!-- Bukti Pengaduan -->
                        <?php if ($detail['bukti_pengaduan']): ?>
                        <div class="mt-4">
                            <h5><strong><i class="fa-solid fa-image me-2"></i>Bukti Pengaduan:</strong></h5>
                            <img src="../../<?php echo $detail['bukti_pengaduan']; ?>" 
                                 class="img-fluid rounded" 
                                 style="max-width: 400px; border: 2px solid #667eea; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" 
                                 alt="Bukti Pengaduan">
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tanda Tangan -->
                        <div class="signature-section">
                            <div class="signature-box">
                                <p><i class="fa-solid fa-calendar-check me-1"></i>Dicetak pada:</p>
                                <p><?php echo date('d/m/Y H:i'); ?></p>
                                <div class="signature-line"></div>
                                <p><i class="fa-solid fa-user-cog me-1"></i>Operator Sistem</p>
                            </div>
                            <div class="signature-box">
                                <p><i class="fa-solid fa-map-marker-alt me-1"></i>Jakarta, <?php echo date('d/m/Y'); ?></p>
                                <div class="signature-line"></div>
                                <p><i class="fa-solid fa-user-tie me-1"></i>Petugas Bidang</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Daftar Pengaduan -->
                <div class="welcome-section">
                    <h1><i class="fa-solid fa-list me-2"></i>Daftar Pengaduan</h1>
                    <p><i class="fa-solid fa-info-circle me-2"></i>Pilih pengaduan untuk melihat detail dan mencetak</p>
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa-solid fa-table me-2"></i>Daftar Pengaduan dengan Ranking SAW</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-trophy me-1"></i>Ranking</th>
                                        <th><i class="fa-solid fa-user me-1"></i>Pengadu</th>
                                        <th><i class="fa-solid fa-file-alt me-1"></i>Jenis</th>
                                        <th><i class="fa-solid fa-map-marker-alt me-1"></i>Lokasi</th>
                                        <th><i class="fa-solid fa-tasks me-1"></i>Status</th>
                                        <th><i class="fa-solid fa-chart-line me-1"></i>Nilai SAW</th>
                                        <th><i class="fa-solid fa-cogs me-1"></i>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filtered_list as $pengaduan): ?>
                                    <tr>
                                        <td><span class="ranking-badge"><?php echo $pengaduan['ranking_saw']; ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengaduan['nama_alternatif']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($pengaduan['alamat_diadukan']); ?></small></td>
                                        <td><span class="status-badge status-<?php echo $pengaduan['status']; ?>"><?php echo ucfirst($pengaduan['status']); ?></span></td>
                                        <td><strong><?php echo number_format($pengaduan['nilai_saw'], 4); ?></strong></td>
                                        <td>
                                            <a href="?id=<?php echo $pengaduan['id_pengaduan']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-eye me-1"></i>Detail / Cetak
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
