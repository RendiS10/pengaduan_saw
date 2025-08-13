# SIPETRUK - Sistem Pengaduan Terpadu dengan SAW

## Deskripsi

SIPETRUK adalah sistem pengaduan online yang mengimplementasikan metode Simple Additive Weighting (SAW) untuk mengurutkan prioritas pengaduan berdasarkan kriteria tertentu.

## Fitur Utama

### 1. Multi-Role User

- **Pengadu**: Mengajukan dan melacak pengaduan
- **Bidang**: Memproses pengaduan berdasarkan ranking SAW
- **Admin**: Mengelola user dan monitoring sistem

### 2. Sistem SAW (Simple Additive Weighting)

Menggunakan 5 kriteria dengan bobot:

- **Tingkat Urgensi (C1)**: 30% - Benefit
- **Potensi Dampak (C2)**: 25% - Benefit
- **Jenis Pengaduan (C3)**: 20% - Benefit
- **Tingkat Kompleksitas (C4)**: 15% - Cost
- **Lama Laporan (C5)**: 10% - Benefit

### 3. Jenis Pengaduan (Alternatif)

1. **A1**: Longsor di Area Pemakaman
2. **A2**: Saluran Drainase Tersumbat
3. **A3**: Aduan Mengenai Bangunan Tak Berizin di Kawasan Padat
4. **A4**: Tumpukan sampah liar di lahan kosong
5. **A5**: Aduan IRK, PBG, KRK, IKTR Mengenai Administrasi Pemberkasan

### 4. Lama Laporan (C5) - Dinamis

Sistem menghitung lama laporan secara otomatis berdasarkan tanggal pengajuan:

- **< 1 hari**: Nilai 1
- **1-2 hari**: Nilai 2
- **3-4 hari**: Nilai 3
- **5-6 hari**: Nilai 4
- **≥ 7 hari**: Nilai 5

## Instalasi

### 1. Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Laragon/XAMPP (untuk development)

### 2. Langkah Instalasi

1. Clone atau download repository ini
2. Import database dari file `database/pengaduan.sql`
3. Konfigurasi koneksi database di `config/koneksi.php`
4. Pastikan folder `public/image/` memiliki permission write
5. Akses melalui web browser

### 3. Konfigurasi Database

Edit file `config/koneksi.php`:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sipetruk_saw';
```

## Struktur Database

### Tabel Users

- `user_id` (Primary Key)
- `username`
- `password` (hashed)
- `nama`
- `email`
- `role` (pengadu/bidang/admin)

### Tabel Pengaduan

- `id_pengaduan` (Primary Key)
- `user_id` (Foreign Key)
- `nama_pengadu`
- `alamat_pengadu`
- `alamat_diadukan`
- `alternatif` (A1-A5)
- `bukti_pengaduan` (path file)
- `tanggal_pengaduan`
- `status` (diajukan/ditolak/disetujui)
- `tingkat_urgensi`
- `potensi_dampak`
- `jenis_pengaduan`
- `tingkat_kompleksitas`
- `lama_laporan`
- `nilai_saw`
- `ranking_saw`

### Tabel Bobot Kriteria

- `id` (Primary Key)
- `kriteria`
- `kode_kriteria`
- `bobot`
- `atribut` (Benefit/Cost)

### Tabel Nilai Alternatif

- `id` (Primary Key)
- `alternatif`
- `nama_alternatif`
- `c1_value` sampai `c5_value`

## Alur Sistem

### 1. Register & Login

- User mendaftar sebagai pengadu
- Login dengan username dan password
- Redirect berdasarkan role

### 2. Pengajuan Pengaduan (Pengadu)

- Pilih jenis pengaduan (A1-A5)
- Isi form pengaduan
- Upload bukti foto
- Sistem otomatis menghitung lama laporan (1 hari untuk pengaduan baru)
- Sistem menghitung SAW dengan lama laporan dinamis
- Pengaduan tersimpan dengan ranking

### 3. Proses Pengaduan (Bidang)

- Lihat daftar pengaduan berdasarkan ranking SAW
- Update status pengaduan
- Cetak laporan pengaduan
- Detail perhitungan SAW

### 4. Manajemen User (Admin)

- Tambah, edit, hapus user
- Monitoring statistik sistem
- Akses ke semua fitur

## Perhitungan SAW

### 1. Lama Laporan Dinamis (C5)

Sistem menghitung lama laporan secara otomatis:

```
Lama Laporan = Tanggal Sekarang - Tanggal Pengajuan
```

Mapping nilai C5:

- < 1 hari → Nilai 1
- 1-2 hari → Nilai 2
- 3-4 hari → Nilai 3
- 5-6 hari → Nilai 4
- ≥ 7 hari → Nilai 5

### 2. Normalisasi

- **Benefit**: `rij = xij / max(xij)`
- **Cost**: `rij = min(xij) / xij`

### 3. Nilai Preferensi

```
Vi = Σ(wj * rij)
```

### 4. Ranking

Pengaduan diurutkan berdasarkan nilai Vi tertinggi.

## File Penting

### Core Files

- `src/helpers/saw_calculator.php` - Class perhitungan SAW
- `config/koneksi.php` - Konfigurasi database
- `database/pengaduan.sql` - Struktur database

### User Interface

- `index.php` - Halaman login utama
- `src/register.php` - Pendaftaran pengadu
- `src/pengadu/` - Fitur pengadu
- `src/bidang/` - Fitur bidang
- `src/admin/` - Fitur admin

### Templates

- `src/template/cdn_head.php` - CSS dan meta tags
- `src/template/cdn_footer.php` - JavaScript libraries

## Keamanan

### 1. Password Hashing

Menggunakan `password_hash()` dan `password_verify()`

### 2. Session Management

- Validasi session di setiap halaman
- Redirect otomatis jika tidak login
- Logout otomatis

### 3. Input Validation

- Escape string untuk mencegah SQL injection
- Validasi file upload
- Sanitasi input user

## Fitur Tambahan

### 1. UI/UX Modern

- Bootstrap 5
- FontAwesome icons
- SweetAlert2 notifications
- Responsive design

### 2. File Management

- Upload bukti pengaduan
- Validasi format dan ukuran file
- Auto-delete file saat hapus pengaduan

### 3. Reporting

- Cetak laporan pengaduan
- Detail perhitungan SAW
- Statistik sistem

## Troubleshooting

### 1. Database Connection Error

- Periksa konfigurasi di `config/koneksi.php`
- Pastikan MySQL service berjalan
- Cek nama database dan user

### 2. File Upload Error

- Periksa permission folder `public/image/`
- Pastikan ukuran file tidak melebihi 2MB
- Cek format file yang diizinkan

### 3. SAW Calculation Error

- Pastikan data bobot kriteria sudah terisi
- Cek nilai alternatif untuk setiap jenis pengaduan
- Periksa perhitungan di `saw_calculator.php`

## Kontribusi

Untuk berkontribusi pada pengembangan sistem:

1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Sistem ini dikembangkan untuk keperluan akademis dan dapat digunakan secara bebas.

## Kontak

Untuk pertanyaan atau dukungan teknis, silakan hubungi developer.

---

**SIPETRUK v1.0** - Sistem Pengaduan Terpadu dengan Metode SAW

Update Setelah Prasidang Dari Pak Heri

1. Mengubah Status Pengaduan Menjadi Diajukan, Ditolak , dan disetujui guna tidak keluar dari batasan sistem yang dibuat
2. Jika bidang sudah pernah edit status pengaduan maka tidak akan ditampilkan lagi di halaman daftar pengaduan yang telah di saw di menu update status pengaduan guna halaman tampil bersih , maka dari itu menambahkan halaman riwayat pengaduan

- Untuk bidang Mengubah Status Menjadi 2 yaitu disetujui dan ditolak saja

Update Setelah Prasidang Dari Pak Asep Ririh

1. Input Data Dummy Agar di daftar pengaduan proses SAW Kelihatan berjalan

Menambah user
hilman
hilman123

lutfi
lutfi123

iqbal
iqbal123

aulia
aulia123

teguh
teguh123
