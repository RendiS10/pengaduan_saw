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

$query = "SELECT * FROM pengaduan WHERE id_pengaduan = $id_pengaduan AND user_id = $user_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    exit('Data tidak ditemukan');
}
?>

<form action="proses_edit_pengaduan.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">

    <div class="modal-header bg-warning text-white">
        <h5 class="modal-title"><i class="fa-solid fa-edit me-2"></i>Edit Pengaduan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label for="nama_pengadu" class="form-label">Nama Pengadu</label>
            <input type="text" name="nama_pengadu" id="nama_pengadu" class="form-control" required value="<?php echo htmlspecialchars($data['nama_pengadu']); ?>">
        </div>

        <div class="mb-3">
            <label for="alamat_pengadu" class="form-label">Alamat Pengadu</label>
            <input type="text" name="alamat_pengadu" id="alamat_pengadu" class="form-control" required value="<?php echo htmlspecialchars($data['alamat_pengadu']); ?>">
        </div>

        <div class="mb-3">
            <label for="alamat_diadukan" class="form-label">Alamat Diadukan</label>
            <input type="text" name="alamat_diadukan" id="alamat_diadukan" class="form-control" required value="<?php echo htmlspecialchars($data['alamat_diadukan']); ?>">
        </div>

        <div class="mb-3">
            <label for="bukti_pengaduan" class="form-label">Bukti Pengaduan</label>
            <?php if ($data['bukti_pengaduan']): ?>
                <div class="mb-2">
                    <img src="../../<?php echo $data['bukti_pengaduan']; ?>" style="max-height: 150px;" class="img-fluid rounded" alt="Bukti Lama">
                </div>
            <?php endif; ?>
            <input type="file" name="bukti_pengaduan" id="bukti_pengaduan" class="form-control">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah bukti.</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-save me-1"></i> Simpan Perubahan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    </div>
</form>
