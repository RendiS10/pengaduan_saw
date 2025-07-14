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
        .profile-icon { 
            font-size: 2.5rem; 
            color: #667eea; 
            margin-bottom: 10px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .stat-card { 
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            animation: slideIn 0.8s ease-out;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .stat-card.success { 
            background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%);
            animation-delay: 0.1s;
        }
        .stat-card.warning { 
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            animation-delay: 0.2s;
        }
        .stat-card.info { 
            background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
            animation-delay: 0.3s;
        }
        .stat-card.primary { 
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            animation-delay: 0s;
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            animation: slideIn 0.8s ease-out;
        }
        .welcome-section h1 {
            font-size: 2rem;
            margin-bottom: 5px;
            font-weight: 700;
        }
        .welcome-section p {
            font-size: 1.1rem;
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .progress {
            border-radius: 10px;
            height: 10px;
        }
        .progress-bar {
            border-radius: 10px;
        }
        .table th {
            color: #2c3e50;
            font-weight: 600;
        }
        .table td {
            color: #34495e;
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
    
    <div class="flex-grow-1 p-3">
        <div class="main-container">
            <div class="welcome-section">
                <h1><i class="fa-solid fa-user-shield me-2"></i>Hai, <?php echo htmlspecialchars($profil['nama']); ?>!</h1>
                <p><i class="fa-solid fa-chart-line me-2"></i>Selamat datang di dashboard admin. Kelola sistem pengaduan dengan mudah.</p>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                <p class="mb-0"><i class="fa-solid fa-users me-1"></i>Total Users</p>
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
                                <p class="mb-0"><i class="fa-solid fa-clipboard-list me-1"></i>Total Pengaduan</p>
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
                                <p class="mb-0"><i class="fa-solid fa-clock me-1"></i>Menunggu Proses</p>
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
                                <p class="mb-0"><i class="fa-solid fa-check-circle me-1"></i>Selesai</p>
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
                                <tr>
                                    <th><i class="fa-solid fa-user me-2"></i>Username</th>
                                    <td><?php echo htmlspecialchars($profil['username']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa-solid fa-signature me-2"></i>Nama</th>
                                    <td><?php echo htmlspecialchars($profil['nama']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa-solid fa-envelope me-2"></i>Email</th>
                                    <td><?php echo htmlspecialchars($profil['email']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa-solid fa-user-tag me-2"></i>Role</th>
                                    <td><span class="badge bg-danger"><?php echo htmlspecialchars($profil['role']); ?></span></td>
                                </tr>
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
                                    <span><i class="fa-solid fa-clock me-1"></i>Diajukan</span>
                                <span><?php echo $pengaduan_diajukan; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?php echo $total_pengaduan > 0 ? ($pengaduan_diajukan/$total_pengaduan)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                    <span><i class="fa-solid fa-spinner me-1"></i>Diproses</span>
                                <span><?php echo $pengaduan_diproses; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: <?php echo $total_pengaduan > 0 ? ($pengaduan_diproses/$total_pengaduan)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                    <span><i class="fa-solid fa-check-circle me-1"></i>Selesai</span>
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
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfilModal" tabindex="-1" aria-labelledby="editProfilModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProfilModalLabel">
                            <i class="fa-solid fa-pen-to-square me-2"></i>Edit Profil
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">
                                <i class="fa-solid fa-user me-2"></i>Username
                            </label>
                            <input type="text" class="form-control" id="editUsername" name="editUsername" value="<?php echo htmlspecialchars($profil['username']); ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label for="editNama" class="form-label">
                                <i class="fa-solid fa-signature me-2"></i>Nama
                            </label>
                            <input type="text" class="form-control" id="editNama" name="editNama" value="<?php echo htmlspecialchars($profil['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">
                                <i class="fa-solid fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="editEmail" name="editEmail" value="<?php echo htmlspecialchars($profil['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">
                                <i class="fa-solid fa-user-tag me-2"></i>Role
                            </label>
                            <input type="text" class="form-control" id="editRole" name="editRole" value="<?php echo htmlspecialchars($profil['role']); ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" name="simpanEdit">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Simpan
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
            echo '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({icon:"success",title:"Berhasil",text:"Profil berhasil diupdate!",timer:1500,showConfirmButton:false,background:"rgba(255, 255, 255, 0.95)",backdrop:"rgba(0, 184, 148, 0.3)"}).then(()=>{window.location.href=window.location.pathname;});});</script>';
            exit;
        } else {
            echo '<div class="alert alert-danger mt-3">Gagal update profil.</div>';
        }
    }
    ?>

    <?php include_once(__DIR__.'/../template/cdn_footer.php'); ?>
</body>
</html>