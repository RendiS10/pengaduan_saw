<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030;">
  <div class="p-3">
    <h5 class="text-center mb-4">
      <i class="fa-solid fa-building text-primary"></i>
      <br>BIDANG
    </h5>
    
    <nav class="nav flex-column">
      <a class="nav-link text-white-50 mb-2" href="dashboard_bidang.php">
        <i class="fa-solid fa-tachometer-alt me-2"></i>
        Dashboard
      </a>
      
      <a class="nav-link text-white-50 mb-2" href="update_status.php">
        <i class="fa-solid fa-list-check me-2"></i>
        Update Status Pengaduan
      </a>
      
      <a class="nav-link text-white-50 mb-2" href="cetak_aduan.php">
        <i class="fa-solid fa-print me-2"></i>
        Cetak Pengaduan
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
      window.location.href = '../logout.php';
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