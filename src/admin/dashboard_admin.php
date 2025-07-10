<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
include_once '../../config/koneksi.php';

// Ambil statistik
$query_total_users = "SELECT COUNT(*) as total FROM users";
$query_total_pengaduan = "SELECT COUNT(*) as total FROM pengaduan";
$query_pengaduan_diajukan = "SELECT COUNT(*) as total FROM pengaduan WHERE status = 'diajukan'";
$query_pengaduan_diproses = "SELECT COUNT(*) as total FROM pengaduan WHERE status = 'diproses'";
$query_pengaduan_selesai = "SELECT COUNT(*) as total FROM pengaduan WHERE status = 'selesai'";

$total_users = mysqli_fetch_assoc(mysqli_query($conn, $query_total_users))['total'];
$total_pengaduan = mysqli_fetch_assoc(mysqli_query($conn, $query_total_pengaduan))['total'];
$pengaduan_diajukan = mysqli_fetch_assoc(mysqli_query($conn, $query_pengaduan_diajukan))['total'];
$pengaduan_diproses = mysqli_fetch_assoc(mysqli_query($conn, $query_pengaduan_diproses))['total'];
$pengaduan_selesai = mysqli_fetch_assoc(mysqli_query($conn, $query_pengaduan_selesai))['total'];

// Ambil profil admin
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
$profil = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIPETRUK</title>
    <?php include_once(__DIR__.'/../template/cdn_head.php'); ?>
    <style>
        body { background: #f8fafc; }
        .card { box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: none; }
        .profile-icon { font-size: 2.5rem; color: #0d6efd; margin-bottom: 10px; }
        .stat-card { 
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card.success { background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.warning { background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.info { background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.primary { background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); }
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
                <a class="nav-link text-white mb-2" href="dashboard_admin.php">
                    <i class="fa-solid fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
                
                <a class="nav-link text-white-50 mb-2" href="kelola_user.php">
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
                <h1 class="mb-1"><i class="fa-solid fa-user-shield profile-icon"></i> Hai, <?php echo htmlspecialchars($profil['nama']); ?>!</h1>
                <p class="text-secondary">Selamat datang di dashboard admin. Kelola sistem pengaduan dengan mudah.</p>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0"><?php echo $total_users; ?></h3>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa-solid fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0"><?php echo $total_pengaduan; ?></h3>
                            <p class="mb-0">Total Pengaduan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa-solid fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0"><?php echo $pengaduan_diajukan; ?></h3>
                            <p class="mb-0">Menunggu Proses</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa-solid fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0"><?php echo $pengaduan_selesai; ?></h3>
                            <p class="mb-0">Selesai</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa-solid fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Admin -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-id-card me-2"></i>Profil Admin</span>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr><th><i class="fa-solid fa-user"></i> Username</th><td><?php echo htmlspecialchars($profil['username']); ?></td></tr>
                            <tr><th><i class="fa-solid fa-signature"></i> Nama</th><td><?php echo htmlspecialchars($profil['nama']); ?></td></tr>
                            <tr><th><i class="fa-solid fa-envelope"></i> Email</th><td><?php echo htmlspecialchars($profil['email']); ?></td></tr>
                            <tr><th><i class="fa-solid fa-user-tag"></i> Role</th><td><?php echo htmlspecialchars($profil['role']); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <span><i class="fa-solid fa-chart-pie me-2"></i>Status Pengaduan</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Diajukan</span>
                                <span><?php echo $pengaduan_diajukan; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?php echo $total_pengaduan > 0 ? ($pengaduan_diajukan/$total_pengaduan)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Diproses</span>
                                <span><?php echo $pengaduan_diproses; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: <?php echo $total_pengaduan > 0 ? ($pengaduan_diproses/$total_pengaduan)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Selesai</span>
                                <span><?php echo $pengaduan_selesai; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?php echo $total_pengaduan > 0 ? ($pengaduan_selesai/$total_pengaduan)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="kelola_user.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fa-solid fa-users me-2"></i>Kelola User
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../bidang/update_status.php" class="btn btn-success w-100 mb-2">
                                    <i class="fa-solid fa-list-check me-2"></i>Lihat Pengaduan
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../bidang/cetak_aduan.php" class="btn btn-info w-100 mb-2">
                                    <i class="fa-solid fa-print me-2"></i>Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfilModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($profil['username']); ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="editNama" value="<?php echo htmlspecialchars($profil['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="editEmail" value="<?php echo htmlspecialchars($profil['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($profil['role']); ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpanEdit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Proses update profil
    if (isset($_POST['simpanEdit'])) {
        $newNama = mysqli_real_escape_string($conn, $_POST['editNama']);
        $newEmail = mysqli_real_escape_string($conn, $_POST['editEmail']);
        $updateQuery = "UPDATE users SET nama='$newNama', email='$newEmail' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Profil berhasil diupdate!",timer:1500,showConfirmButton:false}).then(()=>{window.location.href=window.location.pathname;});});</script>';
            exit;
        } else {
            echo '<div class="alert alert-danger mt-3">Gagal update profil.</div>';
        }
    }
    ?>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>