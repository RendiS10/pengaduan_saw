<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    exit('Unauthorized');
}

include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

if (!isset($_GET['id'])) {
    exit('ID tidak ditemukan');
}

$id_pengaduan = $_GET['id'];
$saw = new SAWCalculator($conn);
$detail = $saw->getDetailSAW($id_pengaduan);

if (!$detail) {
    exit('Data tidak ditemukan');
}
?>

<div class="row">
    <div class="col-md-6">
        <h6><i class="fa-solid fa-user text-primary"></i> Informasi Pengadu</h6>
        <table class="table table-sm">
            <tr><td>Nama</td><td>: <?php echo htmlspecialchars($detail['nama_pengadu']); ?></td></tr>
            <tr><td>Alamat</td><td>: <?php echo htmlspecialchars($detail['alamat_pengadu']); ?></td></tr>
            <tr><td>Tanggal Pengaduan</td><td>: <?php echo date('d/m/Y H:i', strtotime($detail['tanggal_pengaduan'])); ?></td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6><i class="fa-solid fa-map-marker-alt text-success"></i> Lokasi Diadukan</h6>
        <p><?php echo htmlspecialchars($detail['alamat_diadukan']); ?></p>
        
        <h6><i class="fa-solid fa-chart-line text-warning"></i> Nilai SAW</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6"><?php echo number_format($detail['nilai_saw'], 4); ?></span>
            <span class="badge bg-danger">Ranking: <?php echo $detail['ranking_saw']; ?></span>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <h6><i class="fa-solid fa-calculator text-info"></i> Detail Perhitungan SAW</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
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
                        <td><span class="badge bg-success">Benefit</span></td>
                    </tr>
                    <tr>
                        <td>Potensi Dampak (C2)</td>
                        <td><?php echo $detail['c2_value']; ?></td>
                        <td>25%</td>
                        <td><span class="badge bg-success">Benefit</span></td>
                    </tr>
                    <tr>
                        <td>Jenis Pengaduan (C3)</td>
                        <td><?php echo $detail['c3_value']; ?></td>
                        <td>20%</td>
                        <td><span class="badge bg-success">Benefit</span></td>
                    </tr>
                    <tr>
                        <td>Tingkat Kompleksitas (C4)</td>
                        <td><?php echo $detail['c4_value']; ?></td>
                        <td>15%</td>
                        <td><span class="badge bg-danger">Cost</span></td>
                    </tr>
                    <tr>
                        <td>Lama Laporan (C5)</td>
                        <td><?php echo $detail['lama_laporan']; ?> hari</td>
                        <td>10%</td>
                        <td><span class="badge bg-success">Benefit</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <h6><i class="fa-solid fa-image text-warning"></i> Bukti Pengaduan</h6>
        <?php if ($detail['bukti_pengaduan']): ?>
            <img src="../../<?php echo $detail['bukti_pengaduan']; ?>" 
                 class="img-fluid rounded" 
                 style="max-height: 300px;" 
                 alt="Bukti Pengaduan">
        <?php else: ?>
            <p class="text-muted">Tidak ada bukti pengaduan</p>
        <?php endif; ?>
    </div>
</div> 