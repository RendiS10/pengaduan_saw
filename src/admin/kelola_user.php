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
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Username sudah digunakan!",timer:2000,showConfirmButton:false});});</script>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, nama, email, role) VALUES ('$username', '$hash', '$nama', '$email', '$role')";
        if (mysqli_query($conn, $sql)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"User berhasil ditambahkan!",timer:1500,showConfirmButton:false});});</script>';
        } else {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Gagal menambahkan user!",timer:2000,showConfirmButton:false});});</script>';
        }
    }
}

// Proses hapus user
if (isset($_POST['hapus_user'])) {
    $user_id = $_POST['user_id'];
    
    // Jangan hapus diri sendiri
    if ($user_id == $_SESSION['user_id']) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Tidak dapat menghapus akun sendiri!",timer:2000,showConfirmButton:false});});</script>';
    } else {
        $delete_query = "DELETE FROM users WHERE user_id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"User berhasil dihapus!",timer:1500,showConfirmButton:false});});</script>';
        } else {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"error",title:"Gagal",text:"Gagal menghapus user!",timer:2000,showConfirmButton:false});});</script>';
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
        body { background: #f8fafc; }
        .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
        .role-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .role-pengadu { background: #ffeaa7; color: #d63031; }
        .role-bidang { background: #74b9ff; color: #0984e3; }
        .role-admin { background: #55a3ff; color: #00b894; }
        .btn-action { transition: all 0.3s; }
        .btn-action:hover { transform: scale(1.05); }
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
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1"><i class="fa-solid fa-users text-primary"></i> Kelola User</h1>
                <p class="text-secondary">Kelola data pengguna sistem pengaduan</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                <i class="fa-solid fa-plus"></i> Tambah User
            </button>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Daftar User</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
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
                                        <span class="badge bg-success ms-2">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['nama']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['role']; ?>')">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                    onclick="hapusUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>')">
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
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="pengadu">Pengadu</option>
                                <option value="bidang">Bidang</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_user" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fa-solid fa-user-edit me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="edit_username" id="edit_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="edit_nama" id="edit_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="edit_email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" name="edit_password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="edit_role" id="edit_role" required>
                                <option value="pengadu">Pengadu</option>
                                <option value="bidang">Bidang</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_user" class="btn btn-warning">
                            <i class="fa-solid fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form untuk hapus user -->
    <form method="post" id="hapusForm" style="display: none;">
        <input type="hidden" name="user_id" id="hapus_user_id">
        <input type="hidden" name="hapus_user" value="1">
    </form>

    <script>
        function editUser(id, username, nama, email, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function hapusUser(id, username) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus user "${username}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('hapus_user_id').value = id;
                    document.getElementById('hapusForm').submit();
                }
            });
        }
    </script>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>
