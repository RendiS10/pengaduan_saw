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
    // Batasi hanya status 'disetujui' yang bisa dicetak
    if ($detail && $detail['status'] !== 'disetujui') {
        echo '<script>alert("Hanya pengaduan dengan status DISETUJUI yang dapat dicetak!");window.location.href="cetak_aduan.php";</script>';
        exit;
    }
}

// Ambil semua pengaduan untuk daftar
$pengaduan_list = $saw->getPengaduanWithRanking();

// Filter hanya pengaduan dengan status 'disetujui'
$filtered_list = [];
while ($pengaduan = mysqli_fetch_assoc($pengaduan_list)) {
    if ($pengaduan['status'] === 'disetujui') {
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
            body { 
                margin: 0; 
                padding: 10px !important; 
                background: white !important; 
                font-family: 'Times New Roman', serif !important;
                font-size: 11px !important;
                line-height: 1.2 !important;
            }
            .card { 
                border: 1px solid #000; 
                box-shadow: none; 
                background: white !important; 
                margin: 0 !important;
                padding: 8px !important;
            }
            .main-container { 
                background: white !important; 
                box-shadow: none !important; 
                margin: 0 !important;
                padding: 0 !important;
            }
            .signature-section { 
                page-break-inside: avoid; 
                margin-top: 15px !important;
            }
            .signature-box {
                width: 45% !important;
                padding: 8px !important;
                font-size: 9px !important;
            }
            .table-print th { background: #f0f0f0 !important; color: #000 !important; }
            .table-print td { background: white !important; }
            .ranking-badge { background: #f0f0f0 !important; color: #000 !important; }
            .status-badge { background: #f0f0f0 !important; color: #000 !important; }
            h5 { font-size: 12px !important; margin: 8px 0 !important; }
            p { margin: 3px 0 !important; font-size: 10px !important; }
            table { margin: 8px 0 !important; }
            table td, table th { padding: 4px !important; font-size: 9px !important; }
            .header-section { margin-bottom: 8px !important; }
            .detail-section { margin-bottom: 8px !important; }
            .bukti-section { margin-bottom: 8px !important; }
            .bukti-section img { max-width: 200px !important; max-height: 120px !important; }
            .signature-line { margin-top: 15px !important; }
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
        .status-ditolak { background: #ff7675; color: #d63031; }
        .status-disetujui { background: #55a3ff; color: #00b894; }
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
                        <!-- Header Surat Formal -->
                        <div class="header-section" style="text-align:center; margin-bottom:15px;">
                            <div style="display:flex; align-items:center; justify-content:center; margin-bottom:8px;">
                                <div style="width:80px; height:30px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:5px; font-weight:bold; font-size:0.8rem; margin-right:10px;">
                                    <img src="../../public/image/logo.png" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:3px;">
                                </div>
                                <div style="text-align:left;">
                                    <span style="font-size:14px; font-weight:bold; color:#2c3e50;">PEMERINTAH KOTA BANDUNG</span><br>
                                    <span style="font-size:12px; font-weight:600; color:#34495e;">DINAS PELAYANAN MASYARAKAT</span><br>
                                    <span style="font-size:9px; color:#7f8c8d;">Jl. Cianjur No.34, Kacapiring, Batununggal</span><br>
                                    <span style="font-size:9px; color:#7f8c8d;">Telp: 0227217451</span>
                                </div>
                            </div>
                            <hr style="border:2px solid #667eea; margin:8px 0; width:100%;">
                            <div style="margin-top:5px;">
                                <span style="font-size:9px; color:#7f8c8d;">No: <?php echo str_pad($detail['id_pengaduan'], 4, '0', STR_PAD_LEFT); ?>/SIPETRUK/<?php echo date('Y'); ?> | Tanggal: <?php echo date('d F Y'); ?></span>
                            </div>
                        </div>

                        <!-- Lampiran dan Perihal -->
                        <div style="margin-bottom:10px;">
                            <table style="width:100%; margin-bottom:8px; font-size:10px;">
                                <tr>
                                    <td style="width:15%; vertical-align:top; font-weight:600;">Lampiran</td>
                                    <td style="width:5%; vertical-align:top;">:</td>
                                    <td style="width:80%;">1 (satu) berkas</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top; font-weight:600;">Perihal</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td><strong>Penanganan Pengaduan Masyarakat</strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Tujuan Surat -->
                        <div style="margin-bottom:10px;">
                            <p style="margin-bottom:5px; font-size:10px;">
                                Kepada Yth. <strong>Petugas Bidang Penanganan Pengaduan</strong><br>
                                Dinas Pelayanan Masyarakat Kota Bandung
                            </p>
                        </div>

                        <!-- Pembuka Surat -->
                        <div style="margin-bottom:10px;">
                            <p style="text-align:justify; line-height:1.3; margin-bottom:8px; font-size:10px;">
                                Dengan hormat, bersama surat ini kami sampaikan adanya pengaduan masyarakat yang telah masuk ke dalam sistem SIPETRUK dengan rincian sebagai berikut:
                            </p>
                        </div>

                        <!-- Informasi Pengaduan -->
                        <div class="detail-section" style="margin-bottom:10px;">
                            <h5 style="color:#2c3e50; margin-bottom:8px; text-align:center; font-size:11px; font-weight:600;">
                                DETAIL PENGADUAN
                            </h5>
                            <table style="width:100%; border-collapse:collapse; border:1px solid #ddd; font-size:9px;">
                                <tr>
                                    <td style="width:25%; padding:3px; background:#f8f9fa; border:1px solid #ddd; font-weight:600;">Nama Pengadu</td>
                                    <td style="width:75%; padding:3px; border:1px solid #ddd;"><?php echo htmlspecialchars($detail['nama_pengadu']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:3px; background:#f8f9fa; border:1px solid #ddd; font-weight:600;">Alamat Pengadu</td>
                                    <td style="padding:3px; border:1px solid #ddd;"><?php echo htmlspecialchars($detail['alamat_pengadu']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:3px; background:#f8f9fa; border:1px solid #ddd; font-weight:600;">Tanggal</td>
                                    <td style="padding:3px; border:1px solid #ddd;"><?php echo date('d/m/Y H:i', strtotime($detail['tanggal_pengaduan'])); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:3px; background:#f8f9fa; border:1px solid #ddd; font-weight:600;">Lokasi Aduan</td>
                                    <td style="padding:3px; border:1px solid #ddd;"><?php echo htmlspecialchars($detail['alamat_diadukan']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:3px; background:#f8f9fa; border:1px solid #ddd; font-weight:600;">Jenis Pengaduan</td>
                                    <td style="padding:3px; border:1px solid #ddd;"><?php echo htmlspecialchars($detail['nama_alternatif']); ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Bukti Pengaduan -->
                        <?php if ($detail['bukti_pengaduan']): ?>
                        <div class="bukti-section" style="margin-bottom:10px;">
                            <h5 style="color:#2c3e50; margin-bottom:8px; text-align:center; font-size:11px; font-weight:600;">
                                BUKTI PENDUKUNG
                            </h5>
                            <div style="text-align:center; border:1px solid #ddd; padding:8px; background:#f8f9fa;">
                                <img src="../../<?php echo $detail['bukti_pengaduan']; ?>" 
                                     class="img-fluid" 
                                     style="max-width: 200px; max-height: 120px; border: 1px solid #ddd;" 
                                     alt="Bukti Pengaduan">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Penutup Surat -->
                        <div style="margin-bottom:15px;">
                            <p style="text-align:justify; line-height:1.3; margin-bottom:8px; font-size:10px;">
                                Demikian surat pengaduan ini kami buat agar dapat ditindaklanjuti sesuai prosedur yang berlaku. Atas perhatian dan kerjasama Bapak/Ibu, kami ucapkan terima kasih.
                            </p>
                        </div>

                        <!-- Tanda Tangan -->
                        <div class="signature-section" style="display:flex; justify-content:space-between; margin-top:15px;">
                            <div class="signature-box" style="text-align:center; width:45%;">
                                <p style="margin-bottom:3px; font-size:9px;"><i class="fa-solid fa-calendar-check me-1"></i>Dicetak pada:</p>
                                <p style="font-weight:600; margin-bottom:15px; font-size:9px;"><?php echo date('d F Y H:i'); ?> WIB</p>
                                <div class="signature-line" style="border-top:1px solid #667eea; margin-top:15px; padding-top:3px;"></div>
                                <p style="margin-top:5px; font-weight:600; font-size:9px;"><i class="fa-solid fa-user-cog me-1"></i>Operator SIPETRUK</p>
                                <p style="font-size:8px; color:#7f8c8d;">Dinas Pelayanan Masyarakat</p>
                            </div>
                            <div class="signature-box" style="text-align:center; width:45%;">
                                <p style="margin-bottom:3px; font-size:9px;"><i class="fa-solid fa-map-marker-alt me-1"></i>Bandung, <?php echo date('d F Y'); ?></p>
                                <p style="margin-bottom:3px; font-weight:600; font-size:9px;">Mengetahui,</p>
                                <p style="margin-bottom:15px; font-weight:600; font-size:9px;">Kepala Dinas</p>
                                <div class="signature-line" style="border-top:1px solid #667eea; margin-top:15px; padding-top:3px;"></div>
                                <p style="margin-top:5px; font-weight:600; font-size:9px;"><i class="fa-solid fa-user-tie me-1"></i>Nama Kepala Dinas</p>
                                <p style="font-size:8px; color:#7f8c8d;">NIP. 123456789012345678</p>
                            </div>
                        </div>

                        <!-- Footer Surat -->
                        <div style="text-align:center; margin-top:10px; padding-top:8px; border-top:1px solid #ddd; color:#7f8c8d; font-size:8px;">
                            <p style="margin-bottom:2px;"><strong>SIPETRUK - Sistem Pengaduan Terpadu</strong></p>
                            <p style="margin-bottom:0;">Dinas Pelayanan Masyarakat Kota Bandung</p>
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
                                    <?php 
                                    $no = 1;
                                    foreach ($filtered_list as $pengaduan): 
                                    ?>
                                    <tr>
                                        <td><span class="ranking-badge"><?php echo $no++; ?></span></td>
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

