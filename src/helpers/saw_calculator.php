<?php
/**
 * SAWCalculator Class
 * 
 * Class ini mengimplementasikan metode Simple Additive Weighting (SAW)
 * untuk menghitung prioritas pengaduan berdasarkan 5 kriteria:
 * - C1: Tingkat Urgensi (30% - Benefit)
 * - C2: Potensi Dampak (25% - Benefit) 
 * - C3: Jenis Pengaduan (20% - Benefit)
 * - C4: Tingkat Kompleksitas (15% - Cost)
 * - C5: Lama Laporan (10% - Benefit - Dinamis)
 * 
 * Metode SAW menghitung nilai preferensi dengan formula:
 * Vi = Σ(Wj × Rij) dimana:
 * - Vi = Nilai preferensi alternatif ke-i
 * - Wj = Bobot kriteria ke-j
 * - Rij = Nilai ternormalisasi alternatif ke-i pada kriteria ke-j
 */
class SAWCalculator {
    private $conn; // Koneksi database MySQL
    
    /**
     * Constructor - Inisialisasi koneksi database
     * @param mysqli $conn - Koneksi database
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Fungsi utama untuk menghitung nilai SAW semua pengaduan
     * 
     * Proses:
     * 1. Ambil semua data pengaduan dari database
     * 2. Hitung nilai SAW untuk setiap pengaduan
     * 3. Update nilai SAW ke database
     * 4. Update ranking berdasarkan nilai SAW tertinggi
     */
    public function calculateAllPengaduan() {
        // Ambil semua pengaduan terurut berdasarkan tanggal (FIFO)
        $query = "SELECT * FROM pengaduan ORDER BY tanggal_pengaduan ASC";
        $result = mysqli_query($this->conn, $query);
        
        // Loop setiap pengaduan untuk dihitung nilai SAW-nya
        while ($pengaduan = mysqli_fetch_assoc($result)) {
            // Hitung nilai SAW untuk pengaduan ini
            $nilai_saw = $this->calculateSAW($pengaduan);
            // Simpan hasil perhitungan ke database
            $this->updateNilaiSAW($pengaduan['id_pengaduan'], $nilai_saw);
        }
        
        // Setelah semua nilai SAW dihitung, update ranking
        // Pengaduan dengan nilai SAW tertinggi mendapat ranking 1 (prioritas utama)
        $this->updateRanking();
    }
    
    /**
     * Fungsi untuk menghitung nilai SAW untuk satu pengaduan
     * 
     * Langkah-langkah perhitungan SAW:
     * 1. Ambil nilai kriteria dari tabel nilai_alternatif
     * 2. Hitung lama laporan secara dinamis (hari)
     * 3. Ambil bobot setiap kriteria
     * 4. Normalisasi nilai setiap kriteria
     * 5. Hitung nilai preferensi (Vi) = Σ(Wj × Rij)
     * 
     * @param array $pengaduan - Data pengaduan dari database
     * @return float - Nilai SAW yang sudah dibulatkan 4 desimal
     */
    public function calculateSAW($pengaduan) {
        // Step 1: Ambil nilai alternatif berdasarkan jenis pengaduan (A1-A5)
        $alternatif = $pengaduan['alternatif'];
        $query = "SELECT * FROM nilai_alternatif WHERE alternatif = '$alternatif'";
        $result = mysqli_query($this->conn, $query);
        $nilai_alt = mysqli_fetch_assoc($result);
        
        // Jika data alternatif tidak ditemukan, return 0
        if (!$nilai_alt) {
            return 0;
        }
        
        // Step 2: Hitung lama laporan secara dinamis (dalam hari)
        // Ini adalah kriteria C5 yang dihitung real-time
        $tanggal_pengaduan = new DateTime($pengaduan['tanggal_pengaduan']);
        $tanggal_sekarang = new DateTime();
        $selisih = $tanggal_sekarang->diff($tanggal_pengaduan);
        $lama_laporan = $selisih->days; // Selisih dalam hari
        
        // Update nilai lama laporan ke database untuk tracking
        $this->updateLamaLaporan($pengaduan['id_pengaduan'], $lama_laporan);
        
        // Step 3: Ambil bobot kriteria dari database
        $query = "SELECT * FROM bobot_kriteria ORDER BY kode_kriteria";
        $result = mysqli_query($this->conn, $query);
        $bobot = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Konversi persentase ke decimal (contoh: 30% = 0.3)
            $bobot[$row['kode_kriteria']] = $row['bobot'] / 100;
        }
        
        // Step 4: Ambil nilai maksimal dan minimal untuk normalisasi
        $max_values = $this->getMaxValues(); // Untuk kriteria Benefit
        $min_values = $this->getMinValues(); // Untuk kriteria Cost
        
        // Step 5: Hitung nilai ternormalisasi (Rij)
        // Formula normalisasi:
        // - Benefit: Rij = Xij / Max(Xij)
        // - Cost: Rij = Min(Xij) / Xij
        
        $r1 = $nilai_alt['c1_value'] / $max_values['c1']; // C1: Tingkat Urgensi (Benefit)
        $r2 = $nilai_alt['c2_value'] / $max_values['c2']; // C2: Potensi Dampak (Benefit)
        $r3 = $nilai_alt['c3_value'] / $max_values['c3']; // C3: Jenis Pengaduan (Benefit)
        $r4 = $min_values['c4'] / $nilai_alt['c4_value']; // C4: Tingkat Kompleksitas (Cost)
        
        // C5: Lama Laporan (Benefit) - Nilai dinamis berdasarkan hari
        $c5_value = $this->calculateC5Value($lama_laporan);
        $r5 = $c5_value / $max_values['c5'];
        
        // Step 6: Hitung nilai preferensi (Vi) dengan formula SAW
        // Vi = Σ(Wj × Rij) = (W1×R1) + (W2×R2) + (W3×R3) + (W4×R4) + (W5×R5)
        $v = ($r1 * $bobot['C1']) + ($r2 * $bobot['C2']) + ($r3 * $bobot['C3']) + 
             ($r4 * $bobot['C4']) + ($r5 * $bobot['C5']);
        
        // Return nilai yang dibulatkan 4 desimal
        return round($v, 4);
    }
    
    /**
     * Fungsi untuk mendapatkan nilai maksimal setiap kriteria
     * Digunakan untuk normalisasi kriteria bertipe Benefit
     * 
     * @return array - Array berisi nilai maksimal untuk setiap kriteria
     */
    private function getMaxValues() {
        $query = "SELECT 
                    MAX(c1_value) as c1,  -- Max Tingkat Urgensi
                    MAX(c2_value) as c2,  -- Max Potensi Dampak  
                    MAX(c3_value) as c3,  -- Max Jenis Pengaduan
                    MAX(c4_value) as c4,  -- Max Tingkat Kompleksitas
                    5 as c5               -- Max Lama Laporan (fix: 5 = ≥7 hari)
                  FROM nilai_alternatif";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Fungsi untuk mendapatkan nilai minimal setiap kriteria
     * Digunakan untuk normalisasi kriteria bertipe Cost (hanya C4)
     * 
     * @return array - Array berisi nilai minimal untuk setiap kriteria
     */
    private function getMinValues() {
        $query = "SELECT 
                    MIN(c1_value) as c1,  -- Min Tingkat Urgensi
                    MIN(c2_value) as c2,  -- Min Potensi Dampak
                    MIN(c3_value) as c3,  -- Min Jenis Pengaduan
                    MIN(c4_value) as c4,  -- Min Tingkat Kompleksitas (untuk Cost)
                    1 as c5               -- Min Lama Laporan (fix: 1 = <1 hari)
                  FROM nilai_alternatif";
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Fungsi untuk menyimpan nilai SAW ke database
     * 
     * @param int $id_pengaduan - ID pengaduan
     * @param float $nilai_saw - Nilai SAW yang dihitung
     */
    private function updateNilaiSAW($id_pengaduan, $nilai_saw) {
        $query = "UPDATE pengaduan SET nilai_saw = $nilai_saw WHERE id_pengaduan = $id_pengaduan";
        mysqli_query($this->conn, $query);
    }
    
    /**
     * Fungsi untuk update ranking berdasarkan nilai SAW
     * 
     * Ranking ditentukan berdasarkan:
     * 1. Nilai SAW tertinggi = Ranking 1 (prioritas utama)
     * 2. Jika nilai SAW sama, yang lebih dulu diajukan = ranking lebih tinggi
     */
    private function updateRanking() {
        // Reset variabel ranking
        $query = "SET @rank = 0";
        mysqli_query($this->conn, $query);
        
        // Update ranking berdasarkan nilai SAW tertinggi
        // ORDER BY: nilai_saw DESC (tertinggi dulu), tanggal_pengaduan ASC (FIFO)
        $query = "UPDATE pengaduan 
                  SET ranking_saw = (@rank := @rank + 1) 
                  WHERE nilai_saw > 0 
                  ORDER BY nilai_saw DESC, tanggal_pengaduan ASC";
        mysqli_query($this->conn, $query);
    }
    
    /**
     * Fungsi untuk menghitung nilai C5 berdasarkan lama laporan (dinamis)
     * 
     * Kriteria C5 (Lama Laporan) adalah satu-satunya kriteria yang dihitung
     * secara real-time berdasarkan selisih hari antara tanggal pengaduan
     * dengan tanggal sekarang.
     * 
     * Mapping nilai:
     * - < 1 hari = Nilai 1 (baru diajukan)
     * - 1-2 hari = Nilai 2 (masih fresh)
     * - 3-4 hari = Nilai 3 (perlu perhatian)
     * - 5-6 hari = Nilai 4 (sudah lama, prioritas tinggi)
     * - ≥ 7 hari = Nilai 5 (sangat lama, prioritas tertinggi)
     * 
     * @param int $lama_laporan - Lama laporan dalam hari
     * @return int - Nilai C5 (1-5)
     */
    private function calculateC5Value($lama_laporan) {
        // Semakin lama pengaduan tidak ditangani, semakin tinggi prioritasnya
        if ($lama_laporan >= 7) {
            return 5; // Sangat lama (≥7 hari) - Prioritas tertinggi
        } elseif ($lama_laporan >= 5) {
            return 4; // Lama (5-6 hari) - Prioritas tinggi
        } elseif ($lama_laporan >= 3) {
            return 3; // Sedang (3-4 hari) - Perlu perhatian
        } elseif ($lama_laporan >= 1) {
            return 2; // Masih fresh (1-2 hari)
        } else {
            return 1; // Baru diajukan (<1 hari)
        }
    }
    
    /**
     * Fungsi untuk update lama laporan di database
     * 
     * Setiap kali perhitungan SAW dilakukan, nilai lama laporan
     * akan diupdate sesuai dengan kondisi real-time
     * 
     * @param int $id_pengaduan - ID pengaduan
     * @param int $lama_laporan - Lama laporan dalam hari
     */
    private function updateLamaLaporan($id_pengaduan, $lama_laporan) {
        $query = "UPDATE pengaduan SET lama_laporan = $lama_laporan WHERE id_pengaduan = $id_pengaduan";
        mysqli_query($this->conn, $query);
    }
    
    /**
     * Fungsi untuk mendapatkan daftar pengaduan dengan ranking SAW
     * 
     * Data yang dikembalikan sudah terurut berdasarkan ranking SAW
     * (ranking 1 = prioritas tertinggi)
     * 
     * @param string|null $status - Filter berdasarkan status pengaduan (opsional)
     * @return mysqli_result - Result set berisi data pengaduan dengan ranking
     */
    public function getPengaduanWithRanking($status = null) {
        $where_clause = "";
        if ($status) {
            $where_clause = "WHERE status = '$status'";
        }
        
        // JOIN dengan tabel users dan nilai_alternatif untuk data lengkap
        $query = "SELECT p.*, u.nama as nama_user, na.nama_alternatif
                  FROM pengaduan p
                  LEFT JOIN users u ON p.user_id = u.user_id
                  LEFT JOIN nilai_alternatif na ON p.alternatif = na.alternatif
                  $where_clause
                  ORDER BY p.ranking_saw ASC, p.tanggal_pengaduan ASC";
        
        return mysqli_query($this->conn, $query);
    }
    
    /**
     * Fungsi untuk mendapatkan detail perhitungan SAW
     * 
     * Mengambil semua data yang diperlukan untuk menampilkan
     * detail perhitungan SAW suatu pengaduan
     * 
     * @param int $id_pengaduan - ID pengaduan yang ingin dilihat detailnya
     * @return array|null - Array berisi detail pengaduan atau null jika tidak ditemukan
     */
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