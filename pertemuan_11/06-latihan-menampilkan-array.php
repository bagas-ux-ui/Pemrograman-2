<?php
/*
 * Judul Materi : Latihan 6 - Menampilkan Data dengan Fetch Array
 * Nama         : Akbar Aditiya Sugianto
 * NIM          : 231011403729
 * Kelas        : 07 TPLM 004
 * Universitas  : Universitas Pamulang
 */

mysql_connect('localhost', 'root', '');
mysql_select_db('lat_dbase');
$hasil = mysql_query('select * from tbl_mhs');
while ($data = mysql_fetch_array($hasil)) {
    echo "{$data['FirstName']} {$data['LastName']} {$data['Age']}<br>";
}
?>
