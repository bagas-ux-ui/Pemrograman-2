-- =====================================================
-- Database & tabel Pendaftaran Mahasiswa Baru
-- Import lewat phpMyAdmin (tab Import)
-- =====================================================
CREATE DATABASE IF NOT EXISTS db_pendaftaran
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE db_pendaftaran;

DROP TABLE IF EXISTS calon_mhs;

CREATE TABLE calon_mhs (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idn               VARCHAR(20)  NOT NULL,
  fakultas          VARCHAR(60)  NOT NULL,
  kode_pendaftaran  VARCHAR(20)  NOT NULL,
  nama              VARCHAR(100) NOT NULL,
  jenis_kelamin     ENUM('Laki-Laki','Perempuan') NOT NULL,
  tempat_lahir      VARCHAR(50)  NOT NULL,
  tanggal_lahir     DATE         NOT NULL,
  asal_sekolah      VARCHAR(100) NOT NULL,
  pekerjaan_ortu    VARCHAR(50)  NOT NULL,
  tempat_tes        VARCHAR(30)  NOT NULL,
  nilai_indonesia   DECIMAL(5,2) NOT NULL DEFAULT 0,
  nilai_matematika  DECIMAL(5,2) NOT NULL DEFAULT 0,
  nilai_inggris     DECIMAL(5,2) NOT NULL DEFAULT 0,
  nilai_umum        DECIMAL(5,2) NOT NULL DEFAULT 0,
  created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_idn (idn),
  UNIQUE KEY uk_kode (kode_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data contoh (opsional, untuk pengujian)
INSERT INTO calon_mhs
(idn, fakultas, kode_pendaftaran, nama, jenis_kelamin, tempat_lahir, tanggal_lahir,
 asal_sekolah, pekerjaan_ortu, tempat_tes,
 nilai_indonesia, nilai_matematika, nilai_inggris, nilai_umum)
VALUES
('IDN001','Teknik','A2-101-9','Budi Santoso','Laki-Laki','Jakarta','2007-03-12','SMAN 1 Jakarta','PNS','Gedung A',80,80,75,78),
('IDN002','Ekonomi','B1-102-9','Siti Aminah','Perempuan','Bandung','2007-07-21','SMAN 3 Bandung','Wiraswasta','Gedung B',70,65,60,68),
('IDN003','Hukum','V1-103-8','Andi Wijaya','Laki-Laki','Tangerang','2006-11-05','SMK Nusantara','Petani','Viktor',60,50,55,58),
('IDN004','Teknik','A1-104-9','Dewi Lestari','Perempuan','Depok','2007-01-30','SMAN 2 Depok','Guru','Gedung A',88,90,85,88);