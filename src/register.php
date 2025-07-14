<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pengaduan Terpadu</title>
    <?php include('template/cdn_head.php'); ?>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .register-header .subtitle {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 400;
        }
        
        .form-label {
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 184, 148, 0.4);
        }
        
        .btn-success:active {
            transform: translateY(-1px);
        }
        
        .btn-success::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-success:hover::before {
            left: 100%;
        }
        
        .login-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            font-weight: 500;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            z-index: 10;
        }
        
        .form-control.with-icon {
            padding-left: 45px;
        }
        .position-relative{
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
  <div class="register-container">
        <div class="register-header">
            <h2><i class="fa-solid fa-user-plus me-2"></i>Register</h2>
            <p class="subtitle">Daftar Akun Baru SIPETRUK</p>
        </div>
        
    <form method="post" action="" id="registerForm">
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" class="form-control with-icon" id="username" name="username" required placeholder="Masukkan username Anda" />
                </div>
            </div>
            
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" class="form-control with-icon" id="email" name="email" required placeholder="Masukkan email Anda" />
                </div>
            </div>
            
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" class="form-control with-icon" id="password" name="password" required placeholder="Masukkan password Anda" />
                </div>
      </div>
            
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" class="form-control with-icon" id="confirm_password" name="confirm_password" required placeholder="Konfirmasi password Anda" />
      </div>
      </div>
            
            <div id="registerError" class="alert alert-danger d-none" role="alert">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                <span id="errorMessage"></span>
      </div>
            
            <button type="submit" class="btn btn-success w-100">
                <i class="fa-solid fa-user-plus me-2"></i>Register
            </button>
    </form>
        
        <div class="mt-4 text-center">
            <span class="text-muted">Sudah punya akun?</span>
            <a href="../index.php" class="login-link ms-2">
                <i class="fa-solid fa-sign-in-alt me-1"></i>Login di sini
            </a>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tambahkan animasi pada form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';
            submitBtn.disabled = true;
        });
        
        // Animasi input focus
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>

  <?php
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
    echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Konfirmasi password tidak cocok.",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});</script>';
    return;
  }
  // Cek username/email sudah ada
  $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
  if (mysqli_num_rows($cek) > 0) {
    echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Username atau email sudah terdaftar.",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});</script>';
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, nama, email, role) VALUES ('$username', '$hash', '$nama', '$email', '$role')";
    if (mysqli_query($conn, $sql)) {
      echo '<script>Swal.fire({icon:"success",title:"Registrasi Berhasil!",text:"Silakan login.",timer:1800,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"}).then(()=>{window.location.href=\'../index.php\';});</script>';
      exit;
    } else {
      echo '<script>Swal.fire({icon:"error",title:"Gagal!",text:"Registrasi gagal. Silakan coba lagi.",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});</script>';
    }
  }
}
?>
