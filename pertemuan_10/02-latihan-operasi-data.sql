-- Judul Materi : Latihan 2 - INSERT, UPDATE, SELECT, dan DELETE
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

INSERT INTO mahasiswa (nim, nama, id_kelas)
VALUES ('231011400001', 'Mahasiswa Contoh', 1);

UPDATE mahasiswa
SET nama = 'Mahasiswa Latihan'
WHERE nim = '231011400001';

SELECT nim, nama, id_kelas
FROM mahasiswa
WHERE id_kelas = 1;

DELETE FROM mahasiswa WHERE nim = '231011400001';

SELECT nim, nama, id_kelas FROM mahasiswa;

-- Data latihan dibatalkan agar contoh dapat dijalankan ulang.
ROLLBACK;
