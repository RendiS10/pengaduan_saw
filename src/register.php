<?php include('template/header.php') ?>
  <div class="register-container">
    <h2 class="mb-4 text-center">Register Pengaduan</h2>
    <form method="post" action="" id="registerForm">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
      </div>
      <div id="registerError" class="alert alert-danger d-none" role="alert"></div>
      <button type="submit" class="btn btn-success w-100">Register</button>
    </form>
    <div class="mt-3 text-center">
      <span>Sudah punya akun?</span>
      <a href="../index.php">Login di sini</a>
    </div>
  </div>
  <?php
include('template/footer.php');
include_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $role = 'pengadu';
  $nama = $username;

  // Validasi password
  if ($password !== $confirm_password) {
    echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Konfirmasi password tidak cocok.",timer:2000,showConfirmButton:false});</script>';
    return;
  }
  // Cek username/email sudah ada
  $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
  if (mysqli_num_rows($cek) > 0) {
    echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Username atau email sudah terdaftar.",timer:2000,showConfirmButton:false});</script>';
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, nama, email, role) VALUES ('$username', '$hash', '$nama', '$email', '$role')";
    if (mysqli_query($conn, $sql)) {
      echo '<script>Swal.fire({icon:"success",title:"Registrasi Berhasil!",text:"Silakan login.",timer:1800,showConfirmButton:false}).then(()=>{window.location.href=\'../index.php\';});</script>';
      exit;
    } else {
      echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Registrasi gagal. Silakan coba lagi.",timer:2000,showConfirmButton:false});</script>';
    }
  }
}
?>
