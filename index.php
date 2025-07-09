<?php 
include('config/koneksi.php');
// Proses login
$showSwal = false;
$swalError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            // Jika role pengadu, tampilkan swal lalu redirect
            if ($row['role'] == 'pengadu') {
                $showSwal = true;
            } elseif ($row['role'] == 'admin') {
                header('Location: src/admin/dashboard_admin.php');
                exit;
            } elseif ($row['role'] == 'bidang') {
                header('Location: src/bidang/cetak_aduan.php');
                exit;
            }
        } else {
            $swalError = true;
        }
    } else {
        $swalError = true;
    }
}
?>
<?php include('src/template/header.php') ?>
    <div class="login-container">
      <h2 class="mb-4 text-center">Login Pengaduan</h2>
      <form method="post" action="" id="loginForm">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required />
        </div>
        <div id="loginError" class="alert alert-danger d-none" role="alert"></div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
      <div class="mt-3 text-center">
        <span>Belum punya akun?</span>
        <a href="src/register.php" id="registerLink">Daftar di sini</a>
      </div>
    </div>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS (optional, for interactivity) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($showSwal): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        text: 'Selamat datang, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>!',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        window.location.href = 'src/pengadu/dashboard_pengadu.php';
      });
    </script>
    <?php endif; ?>
    <?php if ($swalError): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: 'Username atau password salah!',
        timer: 1800,
        showConfirmButton: false
      });
    </script>
    <?php endif; ?>
  </body>
</html>
<?php include('src/template/footer.php') ?>