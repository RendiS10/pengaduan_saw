<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    exit('Unauthorized');
}

include_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengaduan = $_POST['id_pengaduan'];
    $user_id = $_SESSION['user_id'];

    $nama_pengadu = mysqli_real_escape_string($conn, $_POST['nama_pengadu']);
    $alamat_pengadu = mysqli_real_escape_string($conn, $_POST['alamat_pengadu']);
    $alamat_diadukan = mysqli_real_escape_string($conn, $_POST['alamat_diadukan']);

    // Ambil data pengaduan lama
    $cek = mysqli_query($conn, "SELECT * FROM pengaduan WHERE id_pengaduan = $id_pengaduan AND user_id = $user_id");
    $pengaduan = mysqli_fetch_assoc($cek);

    if (!$pengaduan) {
        exit('Data tidak ditemukan.');
    }

    $bukti_pengaduan = $pengaduan['bukti_pengaduan'];

    // Handle file upload jika ada file baru
    if (isset($_FILES['bukti_pengaduan']) && $_FILES['bukti_pengaduan']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['bukti_pengaduan']['name'];
        $tmp_file = $_FILES['bukti_pengaduan']['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_baru = 'public/image/bukti_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

        if (move_uploaded_file($tmp_file, '../../' . $nama_baru)) {
            // Hapus file lama jika ada
            if ($bukti_pengaduan && file_exists('../../' . $bukti_pengaduan)) {
                unlink('../../' . $bukti_pengaduan);
            }
            $bukti_pengaduan = $nama_baru;
        }
    }

    // Update ke database
    $update = "UPDATE pengaduan SET 
                nama_pengadu = '$nama_pengadu',
                alamat_pengadu = '$alamat_pengadu',
                alamat_diadukan = '$alamat_diadukan',
                bukti_pengaduan = '$bukti_pengaduan'
               WHERE id_pengaduan = $id_pengaduan AND user_id = $user_id";

    if (mysqli_query($conn, $update)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: "Pengaduan berhasil diperbarui",
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "kelola_status_pengaduan.php";
                });
            });
        </script>';
    } else {
        echo 'Gagal memperbarui: ' . mysqli_error($conn);
    }
}
?>
