<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
?>
<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh;">
  <div class="p-3">
    <h3 class="text-center">
      <i class="fa-solid fa-user text-primary"></i>
      <h5 class="text-white text-center mb-4">
        PENGADU
      </h5>
    </h3>
    
    <nav class="nav flex-column">
      <a class="nav-link text-white-50 mb-2" href="dashboard_pengadu.php">
        <i class="fa-solid fa-tachometer-alt me-2"></i>
        Dashboard
      </a>
      
      <a class="nav-link text-white-50 mb-2" href="mengajukan_pengaduan.php">
        <i class="fa-solid fa-paper-plane me-2"></i>
        Ajukan Pengaduan
      </a>
      
      <a class="nav-link text-white-50 mb-2" href="kelola_status_pengaduan.php">
        <i class="fa-solid fa-clipboard-list me-2"></i>
        Status Pengaduan
      </a>
      
      <hr class="my-3">

      <!-- Tombol Logout yang aktif dengan SweetAlert -->
      <a class="nav-link text-white-50 mb-2" href="#" id="logoutBtn">
        <i class="fa-solid fa-sign-out-alt me-2"></i>
        Logout
      </a>
    </nav>
  </div>
</div>

<!-- CDN Icon dan SweetAlert -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SweetAlert Script -->
<script>
document.getElementById('logoutBtn').addEventListener('click', function(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Logout',
    text: 'Apakah Anda yakin ingin logout?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, logout',
    cancelButtonText: 'Batal',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('proses_logout.php', { method: 'POST' })
        .then(() => {
          window.location.href = '/pengaduan/sipetruk/index.php';
        });
    }
  });
});
</script>

<style>
  @media (max-width: 991.98px) {
    nav.navbar {
      width: 100% !important;
      min-height: auto !important;
    }
  }
</style>
