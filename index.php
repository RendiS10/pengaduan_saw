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
                $_SESSION['login_success'] = true;
                header('Location: src/bidang/dashboard_bidang.php');
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pengaduan Terpadu</title>
    <?php include('src/template/cdn_head.php'); ?>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .position-relative{
          width: 100%;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 450px;
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
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-header .subtitle {
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .register-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .register-link:hover {
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
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="login-container">
        <div class="login-header">
            <h2><i class="fa-solid fa-shield-halved me-2"></i>SIPETRUK</h2>
            <p class="subtitle">Sistem Pengaduan Terpadu</p>
        </div>
        
        <form method="post" action="" id="loginForm">
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" class="form-control with-icon" id="username" name="username" required placeholder="Masukkan username Anda" />
                </div>
            </div>
            
            <div class="input-group">
                <div class="position-relative">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" class="form-control with-icon" id="password" name="password" required placeholder="Masukkan password Anda" />
                </div>
            </div>
            
            <div id="loginError" class="alert alert-danger d-none" role="alert">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                <span id="errorMessage"></span>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <span class="text-muted">Belum punya akun?</span>
            <a href="src/register.php" id="registerLink" class="register-link ms-2">
                <i class="fa-solid fa-user-plus me-1"></i>Daftar di sini
            </a>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($showSwal): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil',
            text: 'Selamat datang, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>!',
            timer: 1500,
            showConfirmButton: false,
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: 'rgba(102, 126, 234, 0.3)',
            customClass: {
                popup: 'animated fadeInDown'
            }
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
            showConfirmButton: false,
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: 'rgba(231, 76, 60, 0.3)',
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    </script>
    <?php endif; ?>
    
    <script>
        // Tambahkan animasi pada form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
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