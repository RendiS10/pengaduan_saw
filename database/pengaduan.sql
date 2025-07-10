-- 1. Buat database
CREATE DATABASE IF NOT EXISTS sipetruk_saw;
USE sipetruk_saw;

-- 2. Tabel Users (pengadu, admin, bidang)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('pengadu', 'admin', 'bidang') NOT NULL
);

-- 3. Tabel Pengaduan dengan field untuk SAW
CREATE TABLE pengaduan (
    id_pengaduan INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_pengadu VARCHAR(100) NOT NULL,
    alamat_pengadu VARCHAR(255) NOT NULL,
    alamat_diadukan VARCHAR(255) NOT NULL,
    alternatif VARCHAR(10) NOT NULL,
    bukti_pengaduan VARCHAR(255) NOT NULL,
    tanggal_pengaduan DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('diajukan', 'diproses', 'selesai') DEFAULT 'diajukan',
    -- Field untuk SAW
    tingkat_urgensi ENUM('Sangat Mendesak', 'Mendesak', 'Sedang', 'Tidak Mendesak', 'Biasa') NOT NULL,
    potensi_dampak ENUM('Berdampak luas (masyarakat umum)', 'Beberapa RT', 'Satu RT', 'Jalan pribadi', 'Individual saja') NOT NULL,
    jenis_pengaduan ENUM('Infrastruktur rusak berat', 'Infrastruktur rusak ringan', 'Pelayanan administrative', 'Non-prioritas', 'Tidak relevan') NOT NULL,
    tingkat_kompleksitas ENUM('Kompleks dan melibatkan banyak pihak (lintas bidang)', 'Kompleks dan butuh verifikasi tambahan', 'Sedang, cukup jelas dan dapat langsung diproses', 'Sederhana dengan solusi teknis ringan', 'Sangat sederhana, keluhan sepele atau administratif') NOT NULL,
    lama_laporan INT NOT NULL, -- dalam hari
    nilai_saw DECIMAL(10,4) DEFAULT 0,
    ranking_saw INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 4. Tabel untuk menyimpan bobot kriteria SAW
CREATE TABLE bobot_kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kriteria VARCHAR(50) NOT NULL,
    kode_kriteria VARCHAR(10) NOT NULL,
    bobot DECIMAL(5,2) NOT NULL,
    atribut ENUM('Benefit', 'Cost') NOT NULL
);

-- 5. Insert bobot kriteria sesuai perhitungan SAW
INSERT INTO bobot_kriteria (kriteria, kode_kriteria, bobot, atribut) VALUES
('Tingkat Urgensi', 'C1', 30.00, 'Benefit'),
('Potensi Dampak', 'C2', 25.00, 'Benefit'),
('Jenis Pengaduan', 'C3', 20.00, 'Benefit'),
('Tingkat Kompleksitas Permasalahan', 'C4', 15.00, 'Cost'),
('Lama Laporan', 'C5', 10.00, 'Benefit');

-- 6. Tabel untuk mapping alternatif dengan nilai kriteria
CREATE TABLE nilai_alternatif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alternatif VARCHAR(10) NOT NULL,
    nama_alternatif VARCHAR(255) NOT NULL,
    c1_value INT NOT NULL, -- Tingkat Urgensi
    c2_value INT NOT NULL, -- Potensi Dampak
    c3_value INT NOT NULL, -- Jenis Pengaduan
    c4_value INT NOT NULL, -- Tingkat Kompleksitas
    c5_value INT NOT NULL  -- Lama Laporan
);

-- 7. Insert nilai alternatif sesuai perhitungan SAW
INSERT INTO nilai_alternatif (alternatif, nama_alternatif, c1_value, c2_value, c3_value, c4_value, c5_value) VALUES
('A1', 'Longsor di Area Pemakaman', 5, 4, 5, 5, 5),
('A2', 'Saluran Drainase Tersumbat', 4, 3, 5, 3, 4),
('A3', 'Aduan Mengenai Bangunan Tak Berizin di Kawasan Padat', 3, 4, 4, 4, 3),
('A4', 'Tumpukan sampah liar di lahan kosong', 2, 2, 2, 2, 2),
('A5', 'Aduan IRK, PBG, KRK, IKTR Mengenai Administrasi Pemberkasan', 3, 1, 3, 1, 1);
