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

-- 3. Tabel Pengaduan
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
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
