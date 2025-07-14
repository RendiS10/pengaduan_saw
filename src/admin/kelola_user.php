<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';

// Proses tambah user
if (isset($_POST['tambah_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Cek username sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Username sudah digunakan!",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});});</script>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, nama, email, role) VALUES ('$username', '$hash', '$nama', '$email', '$role')";
        if (mysqli_query($conn, $sql)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"User berhasil ditambahkan!",timer:1500,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"});});</script>';
        } else {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Gagal menambahkan user!",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});});</script>';
        }
    }
}

// Proses hapus user
if (isset($_POST['hapus_user'])) {
    $user_id = $_POST['user_id'];
    
    // Jangan hapus diri sendiri
    if ($user_id == $_SESSION['user_id']) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Tidak dapat menghapus akun sendiri!",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});});</script>';
    } else {
        $delete_query = "DELETE FROM users WHERE user_id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"User berhasil dihapus!",timer:1500,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"});});</script>';
        } else {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Gagal menghapus user!",timer:2000,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(231, 76, 60, 0.3)"});});</script>';
        }
    }
}

// Ambil daftar user
$query = "SELECT * FROM users ORDER BY role, nama";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
    <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            margin: 20px;
            padding: 30px;
        }
        .card { 
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); 
            border: none; 
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .role-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .role-badge:hover {
            transform: scale(1.1);
        }
        .role-pengadu { background: #ffeaa7; color: #d63031; }
        .role-bidang { background: #74b9ff; color: #0984e3; }
        .role-admin { background: #55a3ff; color: #00b894; }
        .btn-action { 
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 6px 10px;
        }
        .btn-action:hover { 
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .modal-header {
            border-radius: 15px 15px 0 0;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        .table th {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
            font-size: 0.85rem;
            padding: 12px 8px;
            border: none;
        }
        .table td {
            padding: 10px 8px;
            vertical-align: middle;
        }
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="d-flex">
    <div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh;">
        <div class="p-3">
            <h5 class="text-center mb-4">
                <i class="fa-solid fa-user-shield text-primary"></i>
                <br>ADMIN
            </h5>
            
            <nav class="nav flex-column">
                <a class="nav-link text-white-50 mb-2" href="dashboard_admin.php">
                    <i class="fa-solid fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
                
                <a class="nav-link text-white mb-2" href="kelola_user.php">
                    <i class="fa-solid fa-users me-2"></i>
                    Kelola User
                </a>
                
                <hr class="my-3">
                
                <a class="nav-link text-white-50 mb-2" href="../logout.php">
                    <i class="fa-solid fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </nav>
        </div>
    </div>
    
    <div class="flex-grow-1 p-3">
        <div class="main-container">
            <div class="welcome-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fa-solid fa-users me-2"></i>Kelola User</h1>
                        <p><i class="fa-solid fa-info-circle me-2"></i>Kelola data pengguna sistem pengaduan</p>
                    </div>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                        <i class="fa-solid fa-plus me-1"></i>Tambah User
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Daftar User</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-hashtag me-1"></i>No</th>
                                    <th><i class="fa-solid fa-user me-1"></i>Username</th>
                                    <th><i class="fa-solid fa-signature me-1"></i>Nama</th>
                                    <th><i class="fa-solid fa-envelope me-1"></i>Email</th>
                                    <th><i class="fa-solid fa-user-tag me-1"></i>Role</th>
                                    <th><i class="fa-solid fa-cogs me-1"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($user = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-success ms-2"><i class="fa-solid fa-user-check me-1"></i>Anda</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                    <td><i class="fa-solid fa-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <i class="fa-solid fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : ($user['role'] == 'bidang' ? 'user-tie' : 'user'); ?> me-1"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                    onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['nama']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['role']; ?>')"
                                                    title="Edit User">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                        onclick="hapusUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>')"
                                                        title="Hapus User">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal fade" id="tambahUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-user me-2"></i>Username</label>
                            <input type="text" class="form-control" name="username" required placeholder="Masukkan username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-signature me-2"></i>Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-envelope me-2"></i>Email</label>
                            <input type="email" class="form-control" name="email" required placeholder="Masukkan email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-lock me-2"></i>Password</label>
                            <input type="password" class="form-control" name="password" required placeholder="Masukkan password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-user-tag me-2"></i>Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="pengadu">Pengadu</option>
                                <option value="bidang">Bidang</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" name="tambah_user">
                            <i class="fa-solid fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editUser(userId, username, nama, email, role) {
            // Implementasi edit user (bisa menggunakan modal atau redirect)
            alert('Fitur edit user akan diimplementasikan');
        }
        
        function hapusUser(userId, username) {
            if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="user_id" value="' + userId + '">' +
                               '<input type="hidden" name="hapus_user" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>
