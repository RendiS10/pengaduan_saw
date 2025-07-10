<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidang') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once '../../config/koneksi.php';
include_once '../helpers/saw_calculator.php';

try {
    $saw = new SAWCalculator($conn);
    $saw->calculateAllPengaduan();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'SAW berhasil dihitung ulang']);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 