<?php
class SAWCalculator {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Fungsi untuk menghitung nilai SAW untuk semua pengaduan
    public function calculateAllPengaduan() {
        // Ambil semua pengaduan
        $query = "SELECT * FROM pengaduan ORDER BY tanggal_pengaduan ASC";
        $result = mysqli_query($this->conn, $query);
        
        while ($pengaduan = mysqli_fetch_assoc($result)) {
            $nilai_saw = $this->calculateSAW($pengaduan);
            $this->updateNilaiSAW($pengaduan['id_pengaduan'], $nilai_saw);
        }
        
        // Update ranking setelah semua nilai SAW dihitung
        $this->updateRanking();
    }
    
    // Fungsi untuk menghitung nilai SAW untuk satu pengaduan
    public function calculateSAW($pengaduan) {
        // Ambil nilai alternatif berdasarkan jenis pengaduan
        $alternatif = $pengaduan['alternatif'];
        $query = "SELECT * FROM nilai_alternatif WHERE alternatif = '$alternatif'";
        $result = mysqli_query($this->conn, $query);
        $nilai_alt = mysqli_fetch_assoc($result);
        
        if (!$nilai_alt) {
            return 0;
        }
        
        // Hitung lama laporan secara dinamis (dalam hari)
        $tanggal_pengaduan = new DateTime($pengaduan['tanggal_pengaduan']);
        $tanggal_sekarang = new DateTime();
        $selisih = $tanggal_sekarang->diff($tanggal_pengaduan);
        $lama_laporan = $selisih->days;
        
        // Update lama laporan di database
        $this->updateLamaLaporan($pengaduan['id_pengaduan'], $lama_laporan);
        
        // Ambil bobot kriteria
        $query = "SELECT * FROM bobot_kriteria ORDER BY kode_kriteria";
        $result = mysqli_query($this->conn, $query);
        $bobot = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $bobot[$row['kode_kriteria']] = $row['bobot'] / 100; // Konversi ke decimal
        }
        
        // Ambil nilai maksimal dan minimal untuk normalisasi
        $max_values = $this->getMaxValues();
        $min_values = $this->getMinValues();
        
        // Hitung nilai ternormalisasi
        $r1 = $nilai_alt['c1_value'] / $max_values['c1']; // C1: Benefit
        $r2 = $nilai_alt['c2_value'] / $max_values['c2']; // C2: Benefit
        $r3 = $nilai_alt['c3_value'] / $max_values['c3']; // C3: Benefit
        $r4 = $min_values['c4'] / $nilai_alt['c4_value']; // C4: Cost
        
        // Hitung nilai C5 berdasarkan lama laporan dinamis
        $c5_value = $this->calculateC5Value($lama_laporan);
        $r5 = $c5_value / $max_values['c5']; // C5: Benefit
        
        // Hitung nilai preferensi (Vi)
        $v = ($r1 * $bobot['C1']) + ($r2 * $bobot['C2']) + ($r3 * $bobot['C3']) + 
             ($r4 * $bobot['C4']) + ($r5 * $bobot['C5']);
        
        return round($v, 4);
    }
    
    // Fungsi untuk mendapatkan nilai maksimal setiap kriteria
    private function getMaxValues() {
        $query = "SELECT 
                    MAX(c1_value) as c1,
                    MAX(c2_value) as c2,
                    MAX(c3_value) as c3,
                    MAX(c4_value) as c4,
                    5 as c5
                  FROM nilai_alternatif";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    // Fungsi untuk mendapatkan nilai minimal setiap kriteria
    private function getMinValues() {
        $query = "SELECT 
                    MIN(c1_value) as c1,
                    MIN(c2_value) as c2,
                    MIN(c3_value) as c3,
                    MIN(c4_value) as c4,
                    1 as c5
                  FROM nilai_alternatif";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    // Fungsi untuk update nilai SAW di database
    private function updateNilaiSAW($id_pengaduan, $nilai_saw) {
        $query = "UPDATE pengaduan SET nilai_saw = $nilai_saw WHERE id_pengaduan = $id_pengaduan";
        mysqli_query($this->conn, $query);
    }
    
    // Fungsi untuk update ranking berdasarkan nilai SAW
    private function updateRanking() {
        $query = "SET @rank = 0";
        mysqli_query($this->conn, $query);
        
        $query = "UPDATE pengaduan 
                  SET ranking_saw = (@rank := @rank + 1) 
                  WHERE nilai_saw > 0 
                  ORDER BY nilai_saw DESC, tanggal_pengaduan ASC";
        mysqli_query($this->conn, $query);
    }
    
    // Fungsi untuk menghitung nilai C5 berdasarkan lama laporan
    private function calculateC5Value($lama_laporan) {
        // Mapping lama laporan ke nilai C5 sesuai kriteria SAW
        if ($lama_laporan >= 7) {
            return 5; // 7-8 hari
        } elseif ($lama_laporan >= 5) {
            return 4; // 5-6 hari
        } elseif ($lama_laporan >= 3) {
            return 3; // 3-4 hari
        } elseif ($lama_laporan >= 1) {
            return 2; // 1-2 hari
        } else {
            return 1; // <1 hari
        }
    }
    
    // Fungsi untuk update lama laporan di database
    private function updateLamaLaporan($id_pengaduan, $lama_laporan) {
        $query = "UPDATE pengaduan SET lama_laporan = $lama_laporan WHERE id_pengaduan = $id_pengaduan";
        mysqli_query($this->conn, $query);
    }
    
    // Fungsi untuk mendapatkan daftar pengaduan dengan ranking SAW
    public function getPengaduanWithRanking($status = null) {
        $where_clause = "";
        if ($status) {
            $where_clause = "WHERE status = '$status'";
        }
        
        $query = "SELECT p.*, u.nama as nama_user, na.nama_alternatif
                  FROM pengaduan p
                  LEFT JOIN users u ON p.user_id = u.user_id
                  LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
                  $where_clause
                  ORDER BY p.ranking_saw ASC, p.tanggal_pengaduan ASC";
        
        return mysqli_query($this->conn, $query);
    }
    
    // Fungsi untuk mendapatkan detail perhitungan SAW
    public function getDetailSAW($id_pengaduan) {
        $query = "SELECT p.*, u.nama as nama_user, na.*
                  FROM pengaduan p
                  LEFT JOIN users u ON p.user_id = u.user_id
                  LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
                  WHERE p.id_pengaduan = $id_pengaduan";
        
        return mysqli_fetch_assoc(mysqli_query($this->conn, $query));
    }
}
?> 