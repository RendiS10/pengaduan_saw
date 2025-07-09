<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengadu') {
    header('Location: ../../index.php');
    exit;
}
?>
<?php // Sidebar Pengadu Responsive Bootstrap ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary flex-column align-items-start min-vh-100 p-3" style="width: 250px;">
  <a class="navbar-brand mb-4" href="dashboard_pengadu.php">
    <img src="#" alt="Logo" width="32" height="32" class="d-inline-block align-text-top me-2">
    Pengaduan
  </a>
  <button class="navbar-toggler mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse w-100" id="sidebarMenu">
    <ul class="navbar-nav flex-column w-100">
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="./dashboard_pengadu.php">
          <i class="bi bi-house-door me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="./mengajukan_pengaduan.php">
          <i class="bi bi-pencil-square me-2"></i> Ajukan Pengaduan
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="./kelola_status_pengaduan.php">
          <i class="bi bi-list-check me-2"></i> Status Pengaduan
        </a>
      </li>
      <li class="nav-item mt-4">
        <button id="logoutBtn" class="nav-link text-white btn btn-link w-100 text-start" style="text-decoration:none;">
          <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
      </li>
    </ul>
  </div>
</nav>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  @media (max-width: 991.98px) {
    nav.navbar {
      width: 100% !important;
      min-height: auto !important;
    }
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
