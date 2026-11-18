-- Judul Materi : Latihan 1 - Mengelola Database
-- Nama         : Akbar Aditiya Sugianto
-- NIM          : 231011403729
-- Kelas        : 07 TPLM 004
-- Universitas  : Universitas Pamulang

CREATE DATABASE IF NOT EXISTS pemweb_2_pertemuan_10;
USE pemweb_2_pertemuan_10;

CREATE TABLE IF NOT EXISTS kelas (
    id_kelas INT PRIMARY KEY,
    nama_kelas VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS mahasiswa (
    nim VARCHAR(12) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    id_kelas INT NOT NULL,
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
);

-- Contoh penghapusan dari materi. Jalankan hanya jika data latihan tidak diperlukan.
-- DROP TABLE mahasiswa;
-- DROP TABLE kelas;
-- DROP DATABASE pemweb_2_pertemuan_10;
