-- Judul Materi : Latihan 3 - Query Lebih dari Satu Tabel
-- Nama         : Akbar Aditiya Sugianto
-- NIM          : 231011403729
-- Kelas        : 07 TPLM 004
-- Universitas  : Universitas Pamulang

USE pemweb_2_pertemuan_10;

START TRANSACTION;

INSERT INTO kelas (id_kelas, nama_kelas)
VALUES (1, '07 TPLM 004');

INSERT INTO mahasiswa (nim, nama, id_kelas)
VALUES ('231011403729', 'Akbar Aditiya Sugianto', 1);

SELECT mahasiswa.nim, mahasiswa.nama, kelas.nama_kelas
FROM mahasiswa
JOIN kelas ON mahasiswa.id_kelas = kelas.id_kelas;

ROLLBACK;
