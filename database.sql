-- Database schema untuk Sistem Informasi Pendaftaran Online Puskesmas
-- Import file ini ke MySQL / phpMyAdmin.

CREATE DATABASE IF NOT EXISTS puskesmas_online
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE puskesmas_online;

SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profil_puskesmas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_puskesmas VARCHAR(150) NOT NULL,
    deskripsi_singkat TEXT NULL,
    alamat TEXT NULL,
    telepon VARCHAR(30) NULL,
    email VARCHAR(100) NULL,
    visi TEXT NULL,
    misi TEXT NULL,
    informasi_umum TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poli (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_poli VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    dokter VARCHAR(100) NULL,
    jadwal VARCHAR(150) NULL,
    jam_operasional VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pendaftaran (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_daftar VARCHAR(20) NOT NULL UNIQUE,
    nik VARCHAR(16) NOT NULL,
    nama_pasien VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    tanggal_lahir DATE NULL,
    alamat TEXT NULL,
    no_hp VARCHAR(20) NULL,
    poli_id INT UNSIGNED NOT NULL,
    tanggal_kunjungan DATE NOT NULL,
    sesi_waktu ENUM('Pagi', 'Siang', 'Sore') NOT NULL,
    nomor_antrian INT UNSIGNED NOT NULL,
    status ENUM('menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
    catatan_admin TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pendaftaran_poli
        FOREIGN KEY (poli_id) REFERENCES poli(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO users (username, password, nama_lengkap, role)
VALUES
('admin', '$2y$10$V5DKspItk8Wt.6zl7woCue8YWbB3A1JDhgTlo.0mZC5rnzSjW0Sy2', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO profil_puskesmas (nama_puskesmas, deskripsi_singkat, alamat, telepon, email, visi, misi, informasi_umum)
VALUES
(
    'Puskesmas Sehat Bersama',
    'Pelayanan kesehatan primer yang cepat, ramah, dan profesional untuk masyarakat.',
    'Jl. Sehat Selalu No. 10, Kota Anda',
    '(021) 1234 5678',
    'info@puskesmas.local',
    'Menjadi puskesmas pilihan utama masyarakat dalam pelayanan kesehatan dasar.',
    'Memberikan pelayanan yang ramah, cepat, tepat, dan terjangkau bagi seluruh masyarakat.',
    'Puskesmas menyediakan layanan umum, KIA, gigi, imunisasi, farmasi, dan layanan penunjang lainnya.'
)
ON DUPLICATE KEY UPDATE nama_puskesmas = VALUES(nama_puskesmas);

INSERT INTO poli (nama_poli, deskripsi, dokter, jadwal, jam_operasional, is_active)
VALUES
('Poli Umum', 'Pemeriksaan kesehatan umum untuk pasien dewasa dan remaja.', 'dr. Andi Pratama', 'Senin - Jumat', '08.00 - 12.00', 1),
('Poli KIA', 'Pelayanan kesehatan ibu, anak, dan imunisasi dasar.', 'dr. Siti Nurhaliza', 'Senin - Kamis', '08.00 - 12.00', 1),
('Poli Gigi', 'Pemeriksaan dan perawatan kesehatan gigi dan mulut.', 'drg. Budi Santoso', 'Selasa - Sabtu', '09.00 - 13.00', 1),
('Poli Lansia', 'Layanan kesehatan khusus untuk pasien lanjut usia.', 'dr. Rina Melati', 'Rabu - Jumat', '08.00 - 11.00', 1)
ON DUPLICATE KEY UPDATE nama_poli = VALUES(nama_poli);
