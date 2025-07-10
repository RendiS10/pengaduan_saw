<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    exit('Unauthorized');
}

include_once '../../config/koneksi.php';

if (!isset($_GET['id'])) {
    exit('ID tidak ditemukan');
}

$id_pengaduan = $_GET['id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT p.*, na.nama_alternatif 
          FROM pengaduan p 
          LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
          WHERE p.id_pengaduan = $id_pengaduan AND p.user_id = $user_id";
$result = mysqli_query($conn, $query);
$detail = mysqli_fetch_assoc($result);

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
        
        <h6><i class="fa-solid fa-exclamation-triangle text-warning"></i> Jenis Pengaduan</h6>
        <p><strong><?php echo htmlspecialchars($detail['nama_alternatif']); ?></strong></p>
        
        <?php if ($detail['nilai_saw'] > 0): ?>
        <h6><i class="fa-solid fa-chart-line text-info"></i> Nilai SAW</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6"><?php echo number_format($detail['nilai_saw'], 4); ?></span>
            <span class="badge bg-danger">Ranking: <?php echo $detail['ranking_saw']; ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <h6><i class="fa-solid fa-calculator text-info"></i> Kriteria Pengaduan</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Kriteria</th>
                        <th>Nilai</th>
                        <th>Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tingkat Urgensi (C1)</td>
                        <td><?php echo htmlspecialchars($detail['tingkat_urgensi']); ?></td>
                        <td>30%</td>
                    </tr>
                    <tr>
                        <td>Potensi Dampak (C2)</td>
                        <td><?php echo htmlspecialchars($detail['potensi_dampak']); ?></td>
                        <td>25%</td>
                    </tr>
                    <tr>
                        <td>Jenis Pengaduan (C3)</td>
                        <td><?php echo htmlspecialchars($detail['jenis_pengaduan']); ?></td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Tingkat Kompleksitas (C4)</td>
                        <td><?php echo htmlspecialchars($detail['tingkat_kompleksitas']); ?></td>
                        <td>15%</td>
                    </tr>
                    <tr>
                        <td>Lama Laporan (C5)</td>
                        <td><?php echo $detail['lama_laporan']; ?> hari</td>
                        <td>10%</td>
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