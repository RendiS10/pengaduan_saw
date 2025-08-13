<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}

include_once '../../config/koneksi.php';

$user_id = $_SESSION['user_id'];
$id_pengaduan = $_GET['id'] ?? 0;

// Ambil data pengaduan yang sudah disetujui dan milik user yang login
$query = "SELECT p.*, na.nama_alternatif, u.nama as nama_user
          FROM pengaduan p 
          LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
          LEFT JOIN users u ON p.user_id = u.user_id
          WHERE p.id_pengaduan = $id_pengaduan 
          AND p.user_id = $user_id 
          AND p.status = 'disetujui'";

$result = mysqli_query($conn, $query);
$pengaduan = mysqli_fetch_assoc($result);

if (!$pengaduan) {
    echo '<script>alert("Pengaduan tidak ditemukan atau belum disetujui!"); window.close();</script>';
    exit;
}

$tanggal_surat = date('d F Y');
$tanggal_pengaduan = date('d F Y', strtotime($pengaduan['tanggal_pengaduan']));

// Tentukan jenis pesan berdasarkan alternatif
$pesan_khusus = '';
$langkah_selanjutnya = '';

if ($pengaduan['alternatif'] == 'A5') {
    // Untuk administrasi pemberkasan
    $pesan_khusus = 'Pengaduan Anda terkait administrasi pemberkasan telah disetujui dan akan diproses lebih lanjut.';
    $langkah_selanjutnya = '
    <div class="alert alert-info mt-3">
        <h6><i class="fa-solid fa-info-circle me-2"></i>Langkah Selanjutnya:</h6>
        <p class="mb-2">Silakan datang langsung ke:</p>
        <p class="mb-1"><strong>Dinas Cipta Karya, Bina Marga dan Tata Ruang</strong></p>
        <p class="mb-1">dengan membawa dokumen pendukung yang diperlukan.</p>
        <p class="mb-0"><em>Pengaduan Anda sudah disetujui, tinggal melanjutkan ke tahap administrasi.</em></p>
    </div>';
} else {
    // Untuk jenis pengaduan lainnya
    $pesan_khusus = 'Pengaduan Anda telah disetujui dan akan segera ditindaklanjuti oleh instansi terkait.';
    $langkah_selanjutnya = '
    <div class="alert alert-success mt-3">
        <h6><i class="fa-solid fa-check-circle me-2"></i>Tindak Lanjut:</h6>
        <p class="mb-0">Tim teknis akan segera melakukan peninjauan dan penanganan di lokasi yang Anda adukan. 
        Terima kasih atas kontribusi Anda dalam membantu perbaikan infrastruktur dan pelayanan publik.</p>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Pengaduan Disetujui</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12pt;
                line-height: 1.5;
            }
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .kop-surat h4 {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .kop-surat p {
            margin-bottom: 2px;
            font-size: 0.9rem;
        }
        
        .nomor-surat {
            text-align: right;
            margin-bottom: 30px;
        }
        
        .isi-surat {
            text-align: justify;
            line-height: 1.8;
        }
        
        .signature-area {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 80px;
            margin-bottom: 5px;
        }
        
        .detail-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(40, 167, 69, 0.1);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="watermark">DISETUJUI</div>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Kop Surat -->
                <div class="kop-surat">
                    <h4>PEMERINTAH KOTA BANDUNG</h4>
                    <h5>DINAS CIPTA KARYA, BINA MARGA DAN TATA RUANG</h5>
                    <p>Jl. Wastukancana No. 2, Bandung 40117</p>
                    <p>Telp: (022) 4264069 | Email: disckbmtr@bandung.go.id</p>
                </div>

                <!-- Nomor Surat -->
                <div class="nomor-surat">
                    <p><strong>Nomor: <?= sprintf("%03d", $pengaduan['id_pengaduan']); ?>/SIPETRUK/<?= date('m/Y'); ?></strong></p>
                    <p><?= $tanggal_surat; ?></p>
                </div>

                <!-- Judul Surat -->
                <div class="text-center mb-4">
                    <h5><strong><u>SURAT KETERANGAN</u></strong></h5>
                    <h6><strong>PENGADUAN DISETUJUI</strong></h6>
                </div>

                <!-- Isi Surat -->
                <div class="isi-surat">
                    <p>Yang bertanda tangan di bawah ini, menerangkan bahwa:</p>
                    
                    <!-- Detail Pengadu -->
                    <div class="detail-box">
                        <div class="row">
                            <div class="col-4"><strong>Nama Pengadu</strong></div>
                            <div class="col-8">: <?= htmlspecialchars($pengaduan['nama_pengadu']); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Alamat Pengadu</strong></div>
                            <div class="col-8">: <?= htmlspecialchars($pengaduan['alamat_pengadu']); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Tanggal Pengaduan</strong></div>
                            <div class="col-8">: <?= $tanggal_pengaduan; ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Jenis Pengaduan</strong></div>
                            <div class="col-8">: <?= htmlspecialchars($pengaduan['nama_alternatif']); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Lokasi Diadukan</strong></div>
                            <div class="col-8">: <?= htmlspecialchars($pengaduan['alamat_diadukan']); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Status</strong></div>
                            <div class="col-8">: <span class="badge bg-success">DISETUJUI</span></div>
                        </div>
                    </div>

                    <p>Telah mengajukan pengaduan melalui Sistem Pengaduan Terpadu (SIPETRUK) dan pengaduan tersebut telah <strong>DISETUJUI</strong> untuk ditindaklanjuti.</p>
                    
                    <p><?= $pesan_khusus; ?></p>
                    
                    <p>Kami mengucapkan terima kasih atas partisipasi dan kontribusi Saudara/i dalam membantu meningkatkan kualitas pelayanan publik dan infrastruktur di Kota Bandung.</p>

                    <?= $langkah_selanjutnya; ?>

                    <p class="mt-4">Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
                </div>

                <!-- Tanda Tangan -->
                <div class="signature-area">
                    <div class="signature-box">
                        <p>Bandung, <?= $tanggal_surat; ?></p>
                        <p><strong>Kepala Dinas CKBMTR</strong></p>
                        <div class="signature-line"></div>
                        <p><strong>Dr. Ir. H. Ahmad Solichin, M.T.</strong></p>
                        <p>NIP. 19650815 199103 1 007</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-5 text-muted">
                    <small>
                        <i class="fa-solid fa-shield-alt me-1"></i>
                        Dokumen ini sah dan dikeluarkan oleh Sistem Pengaduan Terpadu (SIPETRUK)<br>
                        Kode Verifikasi: SPT-<?= $pengaduan['id_pengaduan']; ?>-<?= date('Ymd'); ?>
                    </small>
                </div>

                <!-- Tombol Cetak -->
                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa-solid fa-print me-1"></i> Cetak Surat
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary ms-2">
                        <i class="fa-solid fa-times me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto focus untuk print ketika halaman dimuat
        window.addEventListener('load', function() {
            // Delay sedikit untuk memastikan styling ter-load sempurna
            setTimeout(function() {
                window.focus();
            }, 500);
        });
    </script>
</body>
</html>
