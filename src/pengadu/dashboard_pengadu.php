<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
// Ambil data profil user dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
$profil = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pengadu</title>
  <?php include_once __DIR__.'/../template/cdn_head.php'; ?>
  <style>
    body { background: #f8fafc; }
    .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
    .profile-icon { font-size: 2.5rem; color: #0d6efd; margin-bottom: 10px; }
    .card-header span { font-size: 1.1rem; }
    .edit-btn-anim { transition: transform 0.2s; }
    .edit-btn-anim:hover { transform: scale(1.1) rotate(-10deg); background: #e9ecef; }
    .table th { width: 40%; }
    .modal-content { border-radius: 1rem; }
  </style>
</head>
<body class="d-flex">
  <?php include('sidebar_pengadu.php'); ?>
  <div class="flex-grow-1 p-4">
    <h1 class="mb-2"><i class="fa-solid fa-user-circle profile-icon"></i> Hai, <?php echo htmlspecialchars($profil['nama']); ?>!</h1>
    <p class="text-secondary mb-4">Selamat datang di dashboard pengadu. Kelola profil dan pengaduan Anda dengan mudah.</p>
    <div class="card mt-2" style="max-width: 420px;">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-id-card me-2"></i>Profil Anda</span>
        <button class="btn btn-light btn-sm edit-btn-anim" data-bs-toggle="modal" data-bs-target="#editProfilModal"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr><th><i class="fa-solid fa-user"></i> Username</th><td><?php echo htmlspecialchars($profil['username']); ?></td></tr>
          <tr><th><i class="fa-solid fa-signature"></i> Nama</th><td><?php echo htmlspecialchars($profil['nama']); ?></td></tr>
          <tr><th><i class="fa-solid fa-envelope"></i> Email</th><td><?php echo htmlspecialchars($profil['email']); ?></td></tr>
          <tr><th><i class="fa-solid fa-user-tag"></i> Role</th><td><?php echo htmlspecialchars($profil['role']); ?></td></tr>
        </table>
      </div>
    </div>
    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfilModal" tabindex="-1" aria-labelledby="editProfilModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="editProfilModalLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Profil</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="editUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="editUsername" name="editUsername" value="<?php echo htmlspecialchars($profil['username']); ?>" readonly disabled>
              </div>
              <div class="mb-3">
                <label for="editNama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="editNama" name="editNama" value="<?php echo htmlspecialchars($profil['nama']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="editEmail" value="<?php echo htmlspecialchars($profil['email']); ?>">
              </div>
              <div class="mb-3">
                <label for="editRole" class="form-label">Role</label>
                <input type="text" class="form-control" id="editRole" name="editRole" value="<?php echo htmlspecialchars($profil['role']); ?>" readonly disabled>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Batal</button>
              <button type="submit" class="btn btn-primary" name="simpanEdit"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php
    // Proses update profil
    if (isset($_POST['simpanEdit'])) {
      $newNama = mysqli_real_escape_string($conn, $_POST['editNama']);
      $newEmail = mysqli_real_escape_string($conn, $_POST['editEmail']);
      $updateQuery = "UPDATE users SET nama='$newNama', email='$newEmail' WHERE user_id='$user_id'";
      if (mysqli_query($conn, $updateQuery)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Profil berhasil diupdate!",timer:1500,showConfirmButton:false}).then(()=>{window.location.href=window.location.pathname;});});</script>';
        exit;
      } else {
        echo '<div class="alert alert-danger mt-3">Gagal update profil.</div>';
      }
    }
    ?>
  </div>
  <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>