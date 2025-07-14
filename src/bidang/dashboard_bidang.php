<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
$profil = mysqli_fetch_assoc($result);
// Cek session flash swal login
$showSwalLogin = false;
if (isset($_SESSION['login_success'])) {
    $showSwalLogin = true;
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Bidang</title>
  <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
  <style>
    body { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    .profile-icon { 
        font-size: 2.5rem; 
        color: #667eea; 
        margin-bottom: 10px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .card-header span { 
        font-size: 1.1rem; 
        font-weight: 600;
    }
    .edit-btn-anim { 
        transition: all 0.3s ease;
        border-radius: 10px;
    }
    .edit-btn-anim:hover { 
        transform: scale(1.1) rotate(-10deg); 
        background: #e9ecef;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .table th { 
        width: 40%; 
        color: #2c3e50;
        font-weight: 600;
    }
    .table td {
        color: #34495e;
    }
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
    /* Modal tetap pakai class Bootstrap asli, tidak diubah */
    .modal-content, .modal-header, .modal-body, .modal-footer {
      border-radius: 0 !important;
      box-shadow: none !important;
      animation: none !important;
    }
    .modal-backdrop {
      opacity: 0.5 !important;
      background-color: #000 !important;
      z-index: 1050 !important;
      backdrop-filter: none !important;
    }
    .modal-backdrop.show {
      opacity: 0.5 !important;
    }
    body.modal-open {
      overflow: hidden !important;
    }
  </style>
</head>
<body class="d-flex">
  <?php include('sidebar_bidang.php'); ?>
  <div class="flex-grow-1 p-3" style="margin-left:250px;">
    <div class="main-container">
        <div class="welcome-section">
            <h1><i class="fa-solid fa-user-tie me-2"></i>Hai, <?php echo htmlspecialchars($profil['nama']); ?>!</h1>
            <p><i class="fa-solid fa-chart-line me-2"></i>Selamat datang di dashboard bidang. Kelola pengaduan dan data Anda di sini.</p>
        </div>
        
        <div class="card" style="max-width: 450px;">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-id-card me-2"></i>Profil Anda</span>
                <button class="btn btn-light btn-sm edit-btn-anim" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
                    <tr>
                        <th><i class="fa-solid fa-user me-2"></i>Username</th>
                        <td><?php echo htmlspecialchars($profil['username']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fa-solid fa-signature me-2"></i>Nama</th>
                        <td><?php echo htmlspecialchars($profil['nama']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fa-solid fa-envelope me-2"></i>Email</th>
                        <td><?php echo htmlspecialchars($profil['email']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fa-solid fa-user-tag me-2"></i>Role</th>
                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($profil['role']); ?></span></td>
                    </tr>
        </table>
      </div>
    </div>
        
    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfilModal" tabindex="-1" aria-labelledby="editProfilModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="editProfilModalLabel">
                                <i class="fa-solid fa-pen-to-square me-2"></i>Edit Profil
                            </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                                <label for="editUsername" class="form-label">
                                    <i class="fa-solid fa-user me-2"></i>Username
                                </label>
                <input type="text" class="form-control" id="editUsername" name="editUsername" value="<?php echo htmlspecialchars($profil['username']); ?>" readonly disabled>
              </div>
              <div class="mb-3">
                                <label for="editNama" class="form-label">
                                    <i class="fa-solid fa-signature me-2"></i>Nama
                                </label>
                <input type="text" class="form-control" id="editNama" name="editNama" value="<?php echo htmlspecialchars($profil['nama']); ?>" required>
              </div>
              <div class="mb-3">
                                <label for="editEmail" class="form-label">
                                    <i class="fa-solid fa-envelope me-2"></i>Email
                                </label>
                <input type="email" class="form-control" id="editEmail" name="editEmail" value="<?php echo htmlspecialchars($profil['email']); ?>">
              </div>
              <div class="mb-3">
                                <label for="editRole" class="form-label">
                                    <i class="fa-solid fa-user-tag me-2"></i>Role
                                </label>
                <input type="text" class="form-control" id="editRole" name="editRole" value="<?php echo htmlspecialchars($profil['role']); ?>" readonly disabled>
              </div>
            </div>
            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary" name="simpanEdit">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
                            </button>
            </div>
          </form>
        </div>
      </div>
    </div>
        
    <?php
    // Proses update profil bidang
    if (isset($_POST['simpanEdit'])) {
      $newNama = mysqli_real_escape_string($conn, $_POST['editNama']);
      $newEmail = mysqli_real_escape_string($conn, $_POST['editEmail']);
      $updateQuery = "UPDATE users SET nama='$newNama', email='$newEmail' WHERE user_id='$user_id'";
      if (mysqli_query($conn, $updateQuery)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
                echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Profil berhasil diupdate!",timer:1500,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"}).then(()=>{window.location.href=window.location.pathname;});});</script>';
        exit;
      } else {
        echo '<div class="alert alert-danger mt-3">Gagal update profil.</div>';
      }
    }
    ?>
        
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($showSwalLogin): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Login Berhasil',
          text: 'Selamat datang, <?php echo htmlspecialchars($profil['nama']); ?>!',
          timer: 1500,
                    showConfirmButton: false,
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(0, 184, 148, 0.3)',
                    customClass: {
                        popup: 'animated fadeInDown'
                    }
        });
      });
    </script>
    <?php endif; ?>
    </div>
  </div>
  <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>
